# Helper 자동 로딩 시스템

## 개요

Jiny Modules는 `/modules` 디렉토리에 있는 모든 모듈의 Helper 파일들을 자동으로 로드하는 시스템을 제공합니다. 이 시스템은 성능 최적화를 위해 캐싱과 지연 로딩을 적용합니다.

## 지원하는 구조

### 1. Helpers 디렉토리 구조
```
modules/
├── vendor1/
│   ├── package1/
│   │   ├── Helpers/
│   │   │   ├── Helper1.php
│   │   │   ├── Helper2.php
│   │   │   └── ...
│   │   └── ...
│   └── package2/
│       ├── Helpers/
│       │   └── Helper.php
│       └── ...
└── vendor2/
    └── package3/
        ├── Helpers/
        │   └── Helper.php
        └── ...
```

### 2. 루트 Helper 파일 구조
```
modules/
├── vendor1/
│   ├── package1/
│   │   ├── Helper.php  # 루트 Helper 파일
│   │   └── ...
│   └── package2/
│       ├── Helper.php  # 루트 Helper 파일
│       └── ...
```

## 자동 로딩 방식

### 1. Service Provider에서 자동 로딩
```php
// JinyModulesServiceProvider.php에서 자동으로 모든 Helper 파일들을 로드
ModuleHelper::loadAllHelpers();
```

### 2. 수동으로 특정 모듈의 Helper 로드
```php
use Jiny\Modules\Helpers\ModuleHelper;

// 특정 모듈의 Helper 파일들만 로드
ModuleHelper::loadModuleHelpers('vendor', 'package');
```

### 3. 캐시 관리
```php
// Helper 캐시 클리어
ModuleHelper::clearHelperCache();

// Helper 파일들을 다시 스캔하고 로드
ModuleHelper::refreshHelpers();
```

## Helper 파일 작성 예제

### 1. 기본 Helper 파일
```php
<?php
// modules/vendor/package/Helpers/Helper.php

if (!function_exists('my_helper_function')) {
    function my_helper_function($param) {
        return "Hello, {$param}!";
    }
}

if (!function_exists('format_price')) {
    function format_price($price) {
        return number_format($price) . '원';
    }
}
```

### 2. 클래스 기반 Helper
```php
<?php
// modules/vendor/package/Helpers/UtilityHelper.php

if (!function_exists('utility_format_date')) {
    function utility_format_date($date, $format = 'Y-m-d') {
        return date($format, strtotime($date));
    }
}

if (!function_exists('utility_generate_slug')) {
    function utility_generate_slug($text) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    }
}
```

### 3. 루트 Helper 파일
```php
<?php
// modules/vendor/package/Helper.php

if (!function_exists('package_specific_function')) {
    function package_specific_function() {
        return 'This is a package-specific helper function';
    }
}
```

## 명령어 사용법

### 1. 모든 Helper 파일 목록 보기
```bash
php artisan modules:helpers
```

### 2. 특정 모듈의 Helper 파일 목록 보기
```bash
php artisan modules:helpers --module=vendor/package
```

### 3. Helper 캐시 새로고침
```bash
php artisan modules:helpers --refresh
```

### 4. 특정 모듈의 Helper 캐시 새로고침
```bash
php artisan modules:helpers --module=vendor/package --refresh
```

## 성능 최적화

### 1. 캐싱 시스템
- Helper 파일 목록은 1시간 동안 캐시됨
- 파일 시스템 스캔을 최소화하여 성능 향상
- `require_once`를 사용하여 중복 로딩 방지

### 2. 지연 로딩
- 애플리케이션 시작 시 모든 Helper 파일을 로드
- 필요할 때만 특정 모듈의 Helper 로드 가능

### 3. 메모리 효율성
- Helper 파일은 한 번만 로드됨
- 캐시를 통한 파일 시스템 접근 최소화

## 사용 예제

### 1. 컨트롤러에서 Helper 사용
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function index(Request $request)
    {
        // 자동으로 로드된 Helper 함수 사용
        $formattedPrice = format_price(10000);
        $greeting = my_helper_function('World');
        $slug = utility_generate_slug('Hello World');
        
        return view('example', compact('formattedPrice', 'greeting', 'slug'));
    }
}
```

### 2. 뷰에서 Helper 사용
```php
<!-- resources/views/example.blade.php -->
<div>
    <p>가격: {{ format_price(15000) }}</p>
    <p>인사: {{ my_helper_function('Laravel') }}</p>
    <p>슬러그: {{ utility_generate_slug('My Blog Post') }}</p>
</div>
```

### 3. 서비스 클래스에서 Helper 사용
```php
<?php

namespace App\Services;

class ProductService
{
    public function formatProduct($product)
    {
        return [
            'name' => $product->name,
            'price' => format_price($product->price),
            'slug' => utility_generate_slug($product->name),
            'created_at' => utility_format_date($product->created_at)
        ];
    }
}
```

## 모듈 개발 시 주의사항

### 1. 함수명 충돌 방지
```php
// 좋은 예: 접두사 사용
if (!function_exists('my_module_format_price')) {
    function my_module_format_price($price) {
        return number_format($price) . '원';
    }
}

// 나쁜 예: 일반적인 함수명
if (!function_exists('format_price')) {
    function format_price($price) {
        return number_format($price) . '원';
    }
}
```

### 2. 네임스페이스 활용
```php
<?php

namespace Vendor\Package\Helpers;

if (!function_exists('vendor_package_helper')) {
    function vendor_package_helper() {
        return 'Helper function with namespace';
    }
}
```

### 3. 조건부 로딩
```php
<?php

// 특정 조건에서만 Helper 함수 정의
if (config('app.debug')) {
    if (!function_exists('debug_helper')) {
        function debug_helper($data) {
            dd($data);
        }
    }
}
```

## 문제 해결

### 1. Helper 함수가 인식되지 않는 경우
```bash
# 캐시 클리어
php artisan modules:helpers --refresh

# 또는 수동으로 캐시 클리어
php artisan cache:clear
```

### 2. 함수명 충돌 발생 시
```php
// 기존 함수 확인
if (function_exists('conflicting_function')) {
    // 다른 이름 사용 또는 기존 함수 재정의
}
```

### 3. 성능 문제 발생 시
```php
// 특정 모듈의 Helper만 로드
ModuleHelper::loadModuleHelpers('vendor', 'package');

// 캐시 TTL 조정 (ModuleHelper 클래스에서)
Cache::remember($cacheKey, 1800, function () { // 30분으로 단축
    return self::discoverHelperFiles();
});
```

## 모범 사례

1. **함수명에 접두사 사용**: 모듈별로 고유한 접두사 사용
2. **조건부 함수 정의**: `function_exists()` 체크 필수
3. **문서화**: Helper 함수에 대한 주석 작성
4. **테스트**: Helper 함수에 대한 단위 테스트 작성
5. **버전 관리**: Helper 함수의 버전 호환성 고려 
