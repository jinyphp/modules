<?php

namespace Jiny\Modules\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Modules\Services\ModuleHelper;

class HelperListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:helpers
                            {--refresh : Refresh helper cache}
                            {--module= : Show helpers for specific module (format: vendor/package)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered helper files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('refresh')) {
            $this->info('Refreshing helper cache...');
            ModuleHelper::refreshHelpers();
            $this->info('Helper cache refreshed successfully!');
        }

        if ($module = $this->option('module')) {
            $this->showModuleHelpers($module);
        } else {
            $this->showAllHelpers();
        }
    }

    /**
     * 모든 Helper 파일들을 표시합니다.
     */
    private function showAllHelpers(): void
    {
        $helperFiles = ModuleHelper::getRegisteredHelpers();

        if (empty($helperFiles)) {
            $this->warn('No helper files found.');
            return;
        }

        $this->info('Registered Helper Files:');
        $this->line('');

        $headers = ['File', 'Module', 'Size'];
        $rows = [];

        foreach ($helperFiles as $helperFile) {
            $relativePath = str_replace(base_path('modules/'), '', $helperFile);
            $modulePath = dirname($relativePath);
            $fileName = basename($helperFile);
            $size = file_exists($helperFile) ? filesize($helperFile) : 0;

            $rows[] = [
                $fileName,
                $modulePath,
                $this->formatBytes($size)
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * 특정 모듈의 Helper 파일들을 표시합니다.
     */
    private function showModuleHelpers(string $module): void
    {
        $parts = explode('/', $module);
        if (count($parts) !== 2) {
            $this->error('Invalid module format. Use: vendor/package');
            return;
        }

        [$vendorName, $packageName] = $parts;

        if (!ModuleHelper::hasHelpers($vendorName, $packageName)) {
            $this->warn("No helper files found for module: {$vendorName}/{$packageName}");
            return;
        }

        $helperFiles = ModuleHelper::getModuleHelpers($vendorName, $packageName);

        $this->info("Helper Files for {$vendorName}/{$packageName}:");
        $this->line('');

        $headers = ['File', 'Size', 'Path'];
        $rows = [];

        foreach ($helperFiles as $helperFile) {
            $fileName = basename($helperFile);
            $size = file_exists($helperFile) ? filesize($helperFile) : 0;
            $relativePath = str_replace(base_path('modules/'), '', $helperFile);

            $rows[] = [
                $fileName,
                $this->formatBytes($size),
                $relativePath
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * 바이트를 읽기 쉬운 형태로 변환합니다.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $factor), 2) . ' ' . $units[$factor];
    }
}
