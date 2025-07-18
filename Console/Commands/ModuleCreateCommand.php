<?php

namespace Jiny\Modules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:create {vendor} {package} {--force : Overwrite existing module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module in the modules directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vendor = $this->argument('vendor');
        $package = $this->argument('package');
        $force = $this->option('force');

        $modulePath = base_path("modules/{$vendor}/{$package}");

        if (File::exists($modulePath) && !$force) {
            $this->error("Module {$vendor}/{$package} already exists!");
            $this->error("Use --force to overwrite existing module.");
            return 1;
        }

        if ($force && File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
            $this->info("Removed existing module: {$vendor}/{$package}");
        }

        $this->createModuleStructure($vendor, $package, $modulePath);
        $this->createServiceProvider($vendor, $package, $modulePath);
        $this->createHelpers($vendor, $package, $modulePath);
        $this->createModuleJson($vendor, $package, $modulePath);
        $this->createReadme($vendor, $package, $modulePath);

        $this->info("Module {$vendor}/{$package} created successfully!");
        $this->info("Path: {$modulePath}");
    }

    /**
     * 모듈 기본 구조를 생성합니다.
     */
    private function createModuleStructure(string $vendor, string $package, string $modulePath): void
    {
        $directories = [
            'database/migrations',
            'database/seeders',
            'resources/views',
            'resources/lang',
            'routes',
            'config',
            'docs'
        ];

        foreach ($directories as $dir) {
            $path = $modulePath . '/' . $dir;
            File::makeDirectory($path, 0755, true);
            $this->line("Created directory: {$dir}");
        }
    }

    /**
     * ServiceProvider를 생성합니다.
     */
    private function createServiceProvider(string $vendor, string $package, string $modulePath): void
    {
        $vendorClass = ucfirst($vendor);
        $packageClass = ucfirst($package);
        $className = "{$vendorClass}{$packageClass}ServiceProvider";
        $namespace = "{$vendorClass}\\{$packageClass}";

        $content = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class {$className} extends ServiceProvider
{
    /**
     * 패키지 이름
     */
    private \$package = "{$vendor}-{$package}";

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 라우트 로드
        \$this->loadRoutesFrom(__DIR__.'/routes/web.php');
        \$this->loadRoutesFrom(__DIR__.'/routes/api.php');

        // 뷰 로드
        \$this->loadViewsFrom(__DIR__.'/resources/views', \$this->package);

        // 마이그레이션 로드
        \$this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // 설정 파일 발행
        \$this->publishes([
            __DIR__.'/config' => config_path('{$vendor}/{$package}'),
        ], '{$vendor}-{$package}-config');

        // 언어 파일 발행
        \$this->publishes([
            __DIR__.'/resources/lang' => resource_path('lang/vendor/{$package}'),
        ], '{$vendor}-{$package}-lang');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 설정 파일 병합
        \$this->mergeConfigFrom(
            __DIR__.'/config/{$package}.php', '{$vendor}.{$package}'
        );
    }
}
PHP;

        File::put($modulePath . '/' . $className . '.php', $content);
        $this->line("Created ServiceProvider: {$className}.php");
    }

    /**
     * Helper 파일들을 생성합니다.
     */
    private function createHelpers(string $vendor, string $package, string $modulePath): void
    {
        // Helpers 디렉토리 생성
        $helpersPath = $modulePath . '/Helpers';
        File::makeDirectory($helpersPath, 0755, true);
        $this->line("Created directory: Helpers");

        // 기본 Helper.php 파일 생성
        $this->createHelperFile($vendor, $package, $helpersPath);

        // 루트 Helper.php 파일도 생성
        $this->createRootHelperFile($vendor, $package, $modulePath);
    }

    /**
     * Helpers 디렉토리의 Helper.php 파일을 생성합니다.
     */
    private function createHelperFile(string $vendor, string $package, string $helpersPath): void
    {
        $content = "<?php\n";

        File::put($helpersPath . '/Helper.php', $content);
        $this->line("Created Helper file: Helpers/Helper.php");
    }

    /**
     * 루트 디렉토리의 Helper.php 파일을 생성합니다.
     */
    private function createRootHelperFile(string $vendor, string $package, string $modulePath): void
    {
        $content = "<?php\n";

        File::put($modulePath . '/Helper.php', $content);
        $this->line("Created Root Helper file: Helper.php");
    }

    /**
     * module.json 파일을 생성합니다.
     */
    private function createModuleJson(string $vendor, string $package, string $modulePath): void
    {
        $moduleName = "{$vendor}/{$package}";
        $vendorClass = ucfirst($vendor);
        $packageClass = ucfirst($package);
        $className = "{$vendorClass}{$packageClass}ServiceProvider";

        $moduleData = [
            'name' => $moduleName,
            'vendor' => $vendor,
            'package' => $package,
            'version' => '1.0.0',
            'description' => "{$vendorClass} {$packageClass} Module",
            'author' => 'Your Name',
            'email' => 'your.email@example.com',
            'license' => 'MIT',
            'homepage' => '',
            'repository' => '',
            'keywords' => ['laravel', 'module', $vendor, $package],
            'require' => [
                'php' => '>=8.0',
                'laravel/framework' => '^10.0'
            ],
            'autoload' => [
                'psr-4' => [
                    "{$vendorClass}\\{$packageClass}\\" => ''
                ]
            ],
            'extra' => [
                'laravel' => [
                    'providers' => [
                        "{$vendorClass}\\{$packageClass}\\{$className}"
                    ]
                ]
            ],
            'config' => [
                'enabled' => true,
                'auto_load' => true,
                'namespace' => "{$vendorClass}\\{$packageClass}"
            ]
        ];

        $jsonContent = json_encode($moduleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        File::put($modulePath . '/module.json', $jsonContent);
        $this->line("Created module.json");
    }

    /**
     * README 파일을 생성합니다.
     */
    private function createReadme(string $vendor, string $package, string $modulePath): void
    {
        $content = <<<MARKDOWN
# {$vendor}/{$package}

이 모듈은 {$vendor} 패키지의 {$package} 기능을 제공합니다.

## 설치

이 모듈은 자동으로 로드됩니다.

## 설정

설정 파일을 발행하려면:

```bash
php artisan vendor:publish --tag={$vendor}-{$package}-config
```



## 사용법

이 모듈의 사용법은 docs 폴더를 참조하세요.

## 라이센스

MIT License
MARKDOWN;

        File::put($modulePath . '/readme.md', $content);
        $this->line("Created README: readme.md");
    }
}
