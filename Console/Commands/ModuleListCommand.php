<?php

namespace Jiny\Modules\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Modules\JinyModulesServiceProvider;

class ModuleListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:list {--detailed : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all discovered modules in the modules directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 모듈 스캔 로직을 직접 구현
        $modules = $this->discoverModules();

        if (empty($modules)) {
            $this->info('No modules discovered in the modules directory.');
            return;
        }

        $this->info('Discovered Modules:');
        $this->info('==================');

        foreach ($modules as $module) {
            $info = $this->readModuleInfo($module['path']);
            $moduleName = $module['vendor'].'/'.$module['package'];
            $title = $info['title'] ?? $moduleName;
            $version = $info['version'] ?? '-';

            $this->line("• {$moduleName} : {$title} (v{$version})");

            if ($this->option('detailed')) {
                $desc = $info['description'] ?? '';
                if ($desc) {
                    $this->line("  {$desc}");
                }
                $this->line("  Path: {$module['path']}");
                $this->line("  ServiceProvider: {$module['serviceProvider']}");
                $this->line("  Namespace: {$module['namespace']}");
                if ($info) {
                    $this->line("  module.json: " . json_encode($info, JSON_UNESCAPED_UNICODE));
                }
                $this->line('');
            }
        }

        $this->info("\nTotal: " . count($modules) . " module(s)");
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

    /**
     * modules.json 파일을 읽어 반환합니다.
     */
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
