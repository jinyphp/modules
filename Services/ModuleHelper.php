<?php

namespace Jiny\Modules\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

class ModuleHelper
{
    /**
     * 모든 모듈의 Helper 파일들을 로드합니다.
     */
    public static function loadAllHelpers(): void
    {
        // 캐시가 사용 가능한지 확인
        if (!self::isCacheAvailable()) {
            $helperFiles = self::discoverHelperFiles();
            foreach ($helperFiles as $helperFile) {
                if (File::exists($helperFile)) {
                    require_once $helperFile;
                }
            }
            return;
        }

        try {
            $cacheKey = 'jiny_modules_helpers';

            $helperFiles = Cache::remember($cacheKey, 3600, function () {
                return self::discoverHelperFiles();
            });

            foreach ($helperFiles as $helperFile) {
                if (File::exists($helperFile)) {
                    require_once $helperFile;
                }
            }
        } catch (\Exception $e) {
            // 캐시 시스템이 사용 불가능한 경우 직접 파일을 스캔
            $helperFiles = self::discoverHelperFiles();
            foreach ($helperFiles as $helperFile) {
                if (File::exists($helperFile)) {
                    require_once $helperFile;
                }
            }
        }
    }

    /**
     * 특정 모듈의 Helper 파일들을 로드합니다.
     */
    public static function loadModuleHelpers(string $vendorName, string $packageName): void
    {
        $modulesPath = base_path('modules');
        $packageDir = $modulesPath . '/' . $vendorName . '/' . $packageName;

        if (!File::exists($packageDir)) {
            return;
        }

        // Helpers 디렉토리 확인
        $helpersDir = $packageDir . '/Helpers';
        if (File::exists($helpersDir) && File::isDirectory($helpersDir)) {
            $helperPhpFiles = glob($helpersDir . '/*.php');
            foreach ($helperPhpFiles as $helperFile) {
                if (File::exists($helperFile)) {
                    require_once $helperFile;
                }
            }
        }

        // 루트 디렉토리의 Helper.php 파일도 확인
        $rootHelperFile = $packageDir . '/Helper.php';
        if (File::exists($rootHelperFile)) {
            require_once $rootHelperFile;
        }
    }

    /**
     * 모듈 디렉토리에서 Helper 파일들을 발견합니다.
     */
    public static function discoverHelperFiles(): array
    {
        $helperFiles = [];
        $modulesPath = base_path('modules');

        if (!File::exists($modulesPath)) {
            return $helperFiles;
        }

        // 벤더 디렉토리들을 순회
        $vendorDirs = glob($modulesPath . '/*', GLOB_ONLYDIR);

        foreach ($vendorDirs as $vendorDir) {
            $vendorName = basename($vendorDir);
            $packageDirs = glob($vendorDir . '/*', GLOB_ONLYDIR);

            foreach ($packageDirs as $packageDir) {
                $packageName = basename($packageDir);

                // Helpers 디렉토리 확인
                $helpersDir = $packageDir . '/Helpers';
                if (File::exists($helpersDir) && File::isDirectory($helpersDir)) {
                    // Helper.php 파일들 찾기
                    $helperPhpFiles = glob($helpersDir . '/*.php');
                    $helperFiles = array_merge($helperFiles, $helperPhpFiles);
                }

                // 루트 디렉토리의 Helper.php 파일도 확인
                $rootHelperFile = $packageDir . '/Helper.php';
                if (File::exists($rootHelperFile)) {
                    $helperFiles[] = $rootHelperFile;
                }
            }
        }

        return $helperFiles;
    }

    /**
     * Helper 파일 캐시를 클리어합니다.
     */
    public static function clearHelperCache(): void
    {
        if (!self::isCacheAvailable()) {
            return;
        }

        try {
            Cache::forget('jiny_modules_helpers');
        } catch (\Exception $e) {
            // 캐시 시스템이 사용 불가능한 경우 무시
        }
    }

    /**
     * Helper 파일들을 다시 스캔하고 로드합니다.
     */
    public static function refreshHelpers(): void
    {
        self::clearHelperCache();
        self::loadAllHelpers();
    }

    /**
     * 등록된 Helper 파일 목록을 반환합니다.
     */
    public static function getRegisteredHelpers(): array
    {
        if (!self::isCacheAvailable()) {
            return self::discoverHelperFiles();
        }

        try {
            $cacheKey = 'jiny_modules_helpers';

            return Cache::remember($cacheKey, 3600, function () {
                return self::discoverHelperFiles();
            });
        } catch (\Exception $e) {
            // 캐시 시스템이 사용 불가능한 경우 직접 파일을 스캔
            return self::discoverHelperFiles();
        }
    }

    /**
     * 특정 모듈이 Helper 파일을 가지고 있는지 확인합니다.
     */
    public static function hasHelpers(string $vendorName, string $packageName): bool
    {
        $modulesPath = base_path('modules');
        $packageDir = $modulesPath . '/' . $vendorName . '/' . $packageName;

        if (!File::exists($packageDir)) {
            return false;
        }

        // Helpers 디렉토리 확인
        $helpersDir = $packageDir . '/Helpers';
        if (File::exists($helpersDir) && File::isDirectory($helpersDir)) {
            $helperPhpFiles = glob($helpersDir . '/*.php');
            if (!empty($helperPhpFiles)) {
                return true;
            }
        }

        // 루트 디렉토리의 Helper.php 파일도 확인
        $rootHelperFile = $packageDir . '/Helper.php';
        return File::exists($rootHelperFile);
    }

    /**
     * 특정 모듈의 Helper 파일 목록을 반환합니다.
     */
    public static function getModuleHelpers(string $vendorName, string $packageName): array
    {
        $helperFiles = [];
        $modulesPath = base_path('modules');
        $packageDir = $modulesPath . '/' . $vendorName . '/' . $packageName;

        if (!File::exists($packageDir)) {
            return $helperFiles;
        }

        // Helpers 디렉토리 확인
        $helpersDir = $packageDir . '/Helpers';
        if (File::exists($helpersDir) && File::isDirectory($helpersDir)) {
            $helperPhpFiles = glob($helpersDir . '/*.php');
            $helperFiles = array_merge($helperFiles, $helperPhpFiles);
        }

        // 루트 디렉토리의 Helper.php 파일도 확인
        $rootHelperFile = $packageDir . '/Helper.php';
        if (File::exists($rootHelperFile)) {
            $helperFiles[] = $rootHelperFile;
        }

        return $helperFiles;
    }

    /**
     * 캐시가 사용 가능한지 확인합니다.
     */
    private static function isCacheAvailable(): bool
    {
        try {
            // Laravel 애플리케이션이 부트스트랩되었는지 확인
            if (!App::isBooted()) {
                return false;
            }

            // 캐시 설정이 제대로 되어 있는지 확인
            $defaultStore = config('cache.default');
            if (!$defaultStore) {
                return false;
            }

            // 간단한 캐시 테스트
            Cache::put('test_cache', 'test', 1);
            $testValue = Cache::get('test_cache');
            Cache::forget('test_cache');

            return $testValue === 'test';
        } catch (\Exception $e) {
            return false;
        }
    }
}
