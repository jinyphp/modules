<?php

namespace Jiny\Modules\Tests;

use Tests\TestCase;
use Jiny\Modules\Helpers\ModuleHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ModuleHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 테스트 전에 캐시 클리어
        ModuleHelper::clearHelperCache();
    }

    protected function tearDown(): void
    {
        // 테스트 후에 캐시 클리어
        ModuleHelper::clearHelperCache();

        parent::tearDown();
    }

    public function test_discover_helper_files()
    {
        // 테스트용 모듈 디렉토리 생성
        $this->createTestModuleStructure();

        $helperFiles = ModuleHelper::discoverHelperFiles();

        $this->assertIsArray($helperFiles);
        $this->assertNotEmpty($helperFiles);

        // 테스트용 모듈 디렉토리 정리
        $this->cleanupTestModuleStructure();
    }

    public function test_load_all_helpers()
    {
        // 테스트용 모듈 디렉토리 생성
        $this->createTestModuleStructure();

        // Helper 함수가 정의되지 않은 상태 확인
        $this->assertFalse(function_exists('test_helper_function'));

        // 모든 Helper 로드
        ModuleHelper::loadAllHelpers();

        // Helper 함수가 정의된 상태 확인
        $this->assertTrue(function_exists('test_helper_function'));

        // 테스트용 모듈 디렉토리 정리
        $this->cleanupTestModuleStructure();
    }

    public function test_load_module_helpers()
    {
        // 테스트용 모듈 디렉토리 생성
        $this->createTestModuleStructure();

        // Helper 함수가 정의되지 않은 상태 확인
        $this->assertFalse(function_exists('test_helper_function'));

        // 특정 모듈의 Helper만 로드
        ModuleHelper::loadModuleHelpers('testvendor', 'testpackage');

        // Helper 함수가 정의된 상태 확인
        $this->assertTrue(function_exists('test_helper_function'));

        // 테스트용 모듈 디렉토리 정리
        $this->cleanupTestModuleStructure();
    }

    public function test_has_helpers()
    {
        // 테스트용 모듈 디렉토리 생성
        $this->createTestModuleStructure();

        // Helper가 있는 모듈 확인
        $this->assertTrue(ModuleHelper::hasHelpers('testvendor', 'testpackage'));

        // Helper가 없는 모듈 확인
        $this->assertFalse(ModuleHelper::hasHelpers('testvendor', 'nonexistent'));

        // 테스트용 모듈 디렉토리 정리
        $this->cleanupTestModuleStructure();
    }

    public function test_get_module_helpers()
    {
        // 테스트용 모듈 디렉토리 생성
        $this->createTestModuleStructure();

        $helperFiles = ModuleHelper::getModuleHelpers('testvendor', 'testpackage');

        $this->assertIsArray($helperFiles);
        $this->assertNotEmpty($helperFiles);

        // 테스트용 모듈 디렉토리 정리
        $this->cleanupTestModuleStructure();
    }

    public function test_get_registered_helpers()
    {
        // 테스트용 모듈 디렉토리 생성
        $this->createTestModuleStructure();

        $helperFiles = ModuleHelper::getRegisteredHelpers();

        $this->assertIsArray($helperFiles);
        $this->assertNotEmpty($helperFiles);

        // 테스트용 모듈 디렉토리 정리
        $this->cleanupTestModuleStructure();
    }

    public function test_clear_helper_cache()
    {
        // 테스트용 모듈 디렉토리 생성
        $this->createTestModuleStructure();

        // 캐시에 데이터 저장
        $helperFiles = ModuleHelper::getRegisteredHelpers();
        $this->assertNotEmpty($helperFiles);

        // 캐시 클리어
        ModuleHelper::clearHelperCache();

        // 캐시가 클리어되었는지 확인
        $this->assertNull(Cache::get('jiny_modules_helpers'));

        // 테스트용 모듈 디렉토리 정리
        $this->cleanupTestModuleStructure();
    }

    public function test_refresh_helpers()
    {
        // 테스트용 모듈 디렉토리 생성
        $this->createTestModuleStructure();

        // Helper 함수가 정의되지 않은 상태 확인
        $this->assertFalse(function_exists('test_helper_function'));

        // Helper 새로고침
        ModuleHelper::refreshHelpers();

        // Helper 함수가 정의된 상태 확인
        $this->assertTrue(function_exists('test_helper_function'));

        // 테스트용 모듈 디렉토리 정리
        $this->cleanupTestModuleStructure();
    }

    public function test_helper_function_works_after_loading()
    {
        // 테스트용 모듈 디렉토리 생성
        $this->createTestModuleStructure();

        // Helper 로드
        ModuleHelper::loadAllHelpers();

        // Helper 함수 실행 테스트
        $result = test_helper_function('World');
        $this->assertEquals('Hello, World!', $result);

        // 테스트용 모듈 디렉토리 정리
        $this->cleanupTestModuleStructure();
    }

    /**
     * 테스트용 모듈 구조를 생성합니다.
     */
    private function createTestModuleStructure(): void
    {
        $modulesPath = base_path('modules');
        $testModulePath = $modulesPath . '/testvendor/testpackage';
        $helpersPath = $testModulePath . '/Helpers';

        // 디렉토리 생성
        if (!File::exists($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
        }
        if (!File::exists($testModulePath)) {
            File::makeDirectory($testModulePath, 0755, true);
        }
        if (!File::exists($helpersPath)) {
            File::makeDirectory($helpersPath, 0755, true);
        }

        // 테스트용 Helper 파일 생성
        $helperContent = '<?php
if (!function_exists("test_helper_function")) {
    function test_helper_function($param) {
        return "Hello, {$param}!";
    }
}';

        File::put($helpersPath . '/TestHelper.php', $helperContent);

        // 루트 Helper 파일도 생성
        $rootHelperContent = '<?php
if (!function_exists("root_test_helper")) {
    function root_test_helper() {
        return "Root helper function";
    }
}';

        File::put($testModulePath . '/Helper.php', $rootHelperContent);
    }

    /**
     * 테스트용 모듈 구조를 정리합니다.
     */
    private function cleanupTestModuleStructure(): void
    {
        $modulesPath = base_path('modules');
        $testModulePath = $modulesPath . '/testvendor/testpackage';

        if (File::exists($testModulePath)) {
            File::deleteDirectory($testModulePath);
        }

        // testvendor 디렉토리가 비어있으면 삭제
        $testVendorPath = $modulesPath . '/testvendor';
        if (File::exists($testVendorPath) && count(File::directories($testVendorPath)) === 0) {
            File::deleteDirectory($testVendorPath);
        }

        // modules 디렉토리가 비어있으면 삭제
        if (File::exists($modulesPath) && count(File::directories($modulesPath)) === 0) {
            File::deleteDirectory($modulesPath);
        }
    }
}
