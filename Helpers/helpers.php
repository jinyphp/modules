<?php

/**
 * Jiny Modules Helper Functions
 *
 * 이 파일은 Jiny Modules에서 사용하는 헬퍼 함수들을 정의합니다.
 * 모든 함수는 function_exists() 체크를 통해 중복 정의를 방지합니다.
 */

use Jiny\Modules\Services\ModuleHelper;

if (!function_exists('jiny_modules_load_helpers')) {
    /**
     * 모든 모듈의 Helper 파일들을 로드합니다.
     */
    function jiny_modules_load_helpers(): void
    {
        ModuleHelper::loadAllHelpers();
    }
}

if (!function_exists('jiny_modules_load_module_helpers')) {
    /**
     * 특정 모듈의 Helper 파일들을 로드합니다.
     *
     * @param string $vendorName 벤더 이름
     * @param string $packageName 패키지 이름
     */
    function jiny_modules_load_module_helpers(string $vendorName, string $packageName): void
    {
        ModuleHelper::loadModuleHelpers($vendorName, $packageName);
    }
}

if (!function_exists('jiny_modules_refresh_helpers')) {
    /**
     * Helper 파일들을 다시 스캔하고 로드합니다.
     */
    function jiny_modules_refresh_helpers(): void
    {
        ModuleHelper::refreshHelpers();
    }
}

if (!function_exists('jiny_modules_clear_cache')) {
    /**
     * Helper 파일 캐시를 클리어합니다.
     */
    function jiny_modules_clear_cache(): void
    {
        ModuleHelper::clearHelperCache();
    }
}

if (!function_exists('jiny_modules_get_helpers')) {
    /**
     * 등록된 Helper 파일 목록을 반환합니다.
     *
     * @return array Helper 파일 목록
     */
    function jiny_modules_get_helpers(): array
    {
        return ModuleHelper::getRegisteredHelpers();
    }
}

if (!function_exists('jiny_modules_has_helpers')) {
    /**
     * 특정 모듈이 Helper 파일을 가지고 있는지 확인합니다.
     *
     * @param string $vendorName 벤더 이름
     * @param string $packageName 패키지 이름
     * @return bool Helper 파일 존재 여부
     */
    function jiny_modules_has_helpers(string $vendorName, string $packageName): bool
    {
        return ModuleHelper::hasHelpers($vendorName, $packageName);
    }
}

if (!function_exists('jiny_modules_get_module_helpers')) {
    /**
     * 특정 모듈의 Helper 파일 목록을 반환합니다.
     *
     * @param string $vendorName 벤더 이름
     * @param string $packageName 패키지 이름
     * @return array Helper 파일 목록
     */
    function jiny_modules_get_module_helpers(string $vendorName, string $packageName): array
    {
        return ModuleHelper::getModuleHelpers($vendorName, $packageName);
    }
}

if (!function_exists('jiny_modules_discover_helpers')) {
    /**
     * 모듈 디렉토리에서 Helper 파일들을 발견합니다.
     *
     * @return array 발견된 Helper 파일 목록
     */
    function jiny_modules_discover_helpers(): array
    {
        return ModuleHelper::discoverHelperFiles();
    }
}

if (!function_exists('jiny_modules_path')) {
    /**
     * 모듈 경로를 반환합니다.
     *
     * @param string $vendorName 벤더 이름
     * @param string $packageName 패키지 이름
     * @param string $path 추가 경로
     * @return string 모듈 경로
     */
    function jiny_modules_path(string $vendorName, string $packageName, string $path = ''): string
    {
        $basePath = base_path("modules/{$vendorName}/{$packageName}");
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('jiny_modules_exists')) {
    /**
     * 모듈이 존재하는지 확인합니다.
     *
     * @param string $vendorName 벤더 이름
     * @param string $packageName 패키지 이름
     * @return bool 모듈 존재 여부
     */
    function jiny_modules_exists(string $vendorName, string $packageName): bool
    {
        return \Illuminate\Support\Facades\File::exists(jiny_modules_path($vendorName, $packageName));
    }
}

if (!function_exists('jiny_modules_list')) {
    /**
     * 모든 모듈 목록을 반환합니다.
     *
     * @return array 모듈 목록
     */
    function jiny_modules_list(): array
    {
        $modules = [];
        $modulesPath = base_path('modules');

        if (!\Illuminate\Support\Facades\File::exists($modulesPath)) {
            return $modules;
        }

        $vendorDirs = glob($modulesPath . '/*', GLOB_ONLYDIR);

        foreach ($vendorDirs as $vendorDir) {
            $vendorName = basename($vendorDir);
            $packageDirs = glob($vendorDir . '/*', GLOB_ONLYDIR);

            foreach ($packageDirs as $packageDir) {
                $packageName = basename($packageDir);
                $modules[] = [
                    'vendor' => $vendorName,
                    'package' => $packageName,
                    'path' => $packageDir,
                    'has_helpers' => jiny_modules_has_helpers($vendorName, $packageName)
                ];
            }
        }

        return $modules;
    }
}
