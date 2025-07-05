<?php

namespace Jiny\Modules;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

class JinyModulesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 모듈 자동 로드 등록
        $this->registerModuleAutoLoader();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 뷰 네임스페이스 등록
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'jiny-modules');

        // 라우트 등록
        $this->loadRoutes();

        // 명령어 등록
        $this->registerCommands();
    }

    /**
     * 모듈 자동 로더를 등록합니다.
     */
    private function registerModuleAutoLoader(): void
    {
        $modulesPath = base_path('modules');

        if (!file_exists($modulesPath)) {
            return;
        }

        $vendorDirs = glob($modulesPath . '/*', GLOB_ONLYDIR);

        foreach ($vendorDirs as $vendorDir) {
            $vendorName = basename($vendorDir);
            $packageDirs = glob($vendorDir . '/*', GLOB_ONLYDIR);

            foreach ($packageDirs as $packageDir) {
                $packageName = basename($packageDir);
                $serviceProviderPath = $this->findServiceProvider($packageDir, $vendorName, $packageName);

                if ($serviceProviderPath) {
                    $this->registerModuleServiceProvider($serviceProviderPath, $vendorName, $packageName);
                }
            }
        }
    }

    /**
     * 패키지 디렉토리에서 ServiceProvider를 찾습니다.
     */
    private function findServiceProvider(string $packageDir, string $vendorName, string $packageName): ?string
    {
        $patterns = [
            $vendorName . ucfirst($packageName) . 'ServiceProvider.php',
            ucfirst($packageName) . 'ServiceProvider.php',
            $vendorName . 'ServiceProvider.php',
            'ServiceProvider.php'
        ];

        foreach ($patterns as $pattern) {
            $filePath = $packageDir . '/' . $pattern;
            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        $phpFiles = glob($packageDir . '/*.php');
        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            if (str_contains($content, 'extends ServiceProvider') ||
                str_contains($content, 'extends \Illuminate\Support\ServiceProvider')) {
                return $file;
            }
        }

        return null;
    }

    /**
     * 모듈 ServiceProvider를 등록합니다.
     */
    private function registerModuleServiceProvider(string $serviceProviderPath, string $vendorName, string $packageName): void
    {
        $className = pathinfo($serviceProviderPath, PATHINFO_FILENAME);
        $namespace = $this->generateNamespace($vendorName, $packageName);
        $fullClassName = $namespace . '\\' . $className;

        if (class_exists($fullClassName)) {
            $this->app->register($fullClassName);
        }
    }

    /**
     * 네임스페이스를 생성합니다.
     */
    private function generateNamespace(string $vendorName, string $packageName): string
    {
        return ucfirst($vendorName) . '\\' . ucfirst($packageName);
    }

    /**
     * 라우트를 로드합니다.
     */
    private function loadRoutes(): void
    {
        Route::middleware(['web', 'auth', 'admin'])
            ->prefix('admin/modules')
            ->name('admin.modules.')
            ->group(function () {
                Route::get('/', [\Jiny\Modules\Http\Controllers\ModuleController::class, 'index'])->name('index');
                Route::get('/{vendor}/{package}', [\Jiny\Modules\Http\Controllers\ModuleController::class, 'show'])->name('show');
                Route::post('/{vendor}/{package}/toggle', [\Jiny\Modules\Http\Controllers\ModuleController::class, 'toggle'])->name('toggle');
            });
    }

    /**
     * 명령어를 등록합니다.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Jiny\Modules\Console\Commands\ModuleListCommand::class,
                \Jiny\Modules\Console\Commands\ModuleCreateCommand::class,
                \Jiny\Modules\Console\Commands\ModuleInfoCommand::class,
                \Jiny\Modules\Console\Commands\ModuleMakeCommand::class,
                \Jiny\Modules\Console\Commands\ModuleRemove::class,
            ]);
        }
    }
}
