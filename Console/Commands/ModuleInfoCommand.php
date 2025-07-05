<?php

namespace Jiny\Modules\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Modules\JinyModulesServiceProvider;
use Illuminate\Support\Facades\File;

class ModuleInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:info {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show detailed information about a specific module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $moduleArg = $this->argument('module');

        // vendor/package 형식으로 분리
        if (!str_contains($moduleArg, '/')) {
            $this->error("모듈명은 'vendor/package' 형식으로 입력해주세요. (예: jiny/auth)");
            return 1;
        }

        list($vendor, $package) = explode('/', $moduleArg, 2);

        // 모듈 스캔 로직을 직접 구현
        $modules = $this->discoverModules();

        $targetModule = null;
        foreach ($modules as $module) {
            if ($module['vendor'] === $vendor && $module['package'] === $package) {
                $targetModule = $module;
                break;
            }
        }

        if (!$targetModule) {
            $this->error("Module {$moduleArg} not found!");
            $this->info("Available modules:");
            foreach ($modules as $module) {
                $this->line("• {$module['vendor']}/{$module['package']}");
            }
            return 1;
        }

        $this->displayModuleInfo($targetModule);
    }

    /**
     * 모듈 정보를 표시합니다.
     */
    private function displayModuleInfo(array $module): void
    {
        $info = $this->readModuleInfo($module['path']);

        if ($info) {
            $this->info("Module Information");
            $this->info("================");
            $this->line("");
            $this->line("Name: ".($info['name'] ?? $module['vendor'].'/'.$module['package']));
            $this->line("Title: ".($info['title'] ?? '-'));
            $this->line("Version: ".($info['version'] ?? '-'));
            $this->line("Description: ".($info['description'] ?? '-'));
            if (isset($info['author'])) {
                $this->line("Author: ".$info['author']);
            }
            if (isset($info['license'])) {
                $this->line("License: ".$info['license']);
            }
            if (isset($info['dependencies']) && !empty($info['dependencies'])) {
                $this->line("Dependencies: ".implode(', ', $info['dependencies']));
            }
            $this->line("");
            $this->line("Path: {$module['path']}");
        } else {
            $this->error("module.json 파일이 없습니다.");
            $this->line("");
            $this->line("Module: {$module['vendor']}/{$module['package']}");
            $this->line("Path: {$module['path']}");
        }
    }

    /**
     * 모듈의 파일 구조를 표시합니다.
     */
    private function displayFileStructure(string $modulePath): void
    {
        $this->info("File Structure:");
        $this->info("---------------");

        $this->displayDirectory($modulePath, 0);
    }

    /**
     * 디렉토리 구조를 재귀적으로 표시합니다.
     */
    private function displayDirectory(string $path, int $level): void
    {
        $indent = str_repeat('  ', $level);

        if ($level === 0) {
            $this->line($indent . basename($path) . '/');
        }

        $items = File::allFiles($path);
        $directories = File::directories($path);

        // 디렉토리 먼저 표시
        foreach ($directories as $dir) {
            $dirName = basename($dir);
            $this->line($indent . '  ' . $dirName . '/');
            $this->displayDirectory($dir, $level + 1);
        }

        // 파일 표시
        foreach ($items as $file) {
            $fileName = basename($file);
            $this->line($indent . '  ' . $fileName);
        }
    }

    /**
     * ServiceProvider 내용을 표시합니다.
     */
    private function displayServiceProviderContent(string $serviceProviderPath): void
    {
        $this->info("ServiceProvider Content:");
        $this->info("------------------------");

        if (File::exists($serviceProviderPath)) {
            $content = File::get($serviceProviderPath);
            $lines = explode("\n", $content);

            // 처음 20줄만 표시
            $previewLines = array_slice($lines, 0, 20);
            foreach ($previewLines as $line) {
                $this->line($line);
            }

            if (count($lines) > 20) {
                $this->line("... (truncated)");
            }
        } else {
            $this->error("ServiceProvider file not found!");
        }
    }

    /**
     * modules 폴더에서 모든 패키지를 발견합니다.
     */
    private function discoverModules(): array
    {
        $modulesPath = base_path('modules');
        $discoveredModules = [];

        if (!\Illuminate\Support\Facades\File::exists($modulesPath)) {
            return $discoveredModules;
        }

        $vendorDirs = \Illuminate\Support\Facades\File::directories($modulesPath);

        foreach ($vendorDirs as $vendorDir) {
            $vendorName = basename($vendorDir);
            $packageDirs = \Illuminate\Support\Facades\File::directories($vendorDir);

            foreach ($packageDirs as $packageDir) {
                $packageName = basename($packageDir);
                $serviceProviderPath = $this->findServiceProvider($packageDir, $vendorName, $packageName);

                if ($serviceProviderPath) {
                    $discoveredModules[] = [
                        'vendor' => $vendorName,
                        'package' => $packageName,
                        'path' => $packageDir,
                        'serviceProvider' => $serviceProviderPath,
                        'namespace' => $this->generateNamespace($vendorName, $packageName)
                    ];
                }
            }
        }

        return $discoveredModules;
    }

    /**
     * 패키지 디렉토리에서 ServiceProvider를 찾습니다.
     */
    private function findServiceProvider(string $packageDir, string $vendorName, string $packageName): ?string
    {
        // 일반적인 ServiceProvider 파일명 패턴들
        $patterns = [
            $vendorName . ucfirst($packageName) . 'ServiceProvider.php',
            ucfirst($packageName) . 'ServiceProvider.php',
            $vendorName . 'ServiceProvider.php',
            'ServiceProvider.php'
        ];

        foreach ($patterns as $pattern) {
            $filePath = $packageDir . '/' . $pattern;
            if (\Illuminate\Support\Facades\File::exists($filePath)) {
                return $filePath;
            }
        }

        // 디렉토리 내 모든 PHP 파일을 검사
        $phpFiles = \Illuminate\Support\Facades\File::glob($packageDir . '/*.php');
        foreach ($phpFiles as $file) {
            $content = \Illuminate\Support\Facades\File::get($file);
            if (str_contains($content, 'extends ServiceProvider') ||
                str_contains($content, 'extends \Illuminate\Support\ServiceProvider')) {
                return $file;
            }
        }

        return null;
    }

    /**
     * 네임스페이스를 생성합니다.
     */
    private function generateNamespace(string $vendorName, string $packageName): string
    {
        return ucfirst($vendorName) . '\\' . ucfirst($packageName);
    }

    private function readModuleInfo(string $modulePath): array
    {
        $jsonPath = $modulePath.'/module.json';
        if (\Illuminate\Support\Facades\File::exists($jsonPath)) {
            $json = \Illuminate\Support\Facades\File::get($jsonPath);
            return json_decode($json, true) ?? [];
        }
        return [];
    }
}
