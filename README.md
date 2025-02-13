# Jiny Modules
`지니모듈`은 `nWidart/laravel-modules`의 확장 패키지로서 라라벨 프레임워크에서 패키지 형태의 
모듈을 생성하고 관리하는 도구 모음 입니다.

## 설치
컴포저 명령을 통하여 다음과 같이 콘솔 창에서 실행합니다.
```
composer require jiny/modules
```

설정파일 배포
```
php artisan vendor:publish --provider="Jiny\Modules\JinyModulesServiceProvider"
```

`modules` 디렉터리를 생성하고, `composer.json` 파일을 수정하여 네임스페이스를 설정합니다.
`module:init` 명령은 이러한 과정을 자동으로 처리해 줍니다.
```
php artisan modules:init
```

변경된 `composer.json` 의 주요 내용은 다음과 같습니다.
```
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\": "Modules/"
    }
  }
}
```

또한 자동으로 `composer dump-autoload` 명령을 수행시켜 줍니다.


## atrisan 확장 명령
지니모듈은 `nWidart/laravel-modules`보다 몇개의 확장 명령등을 제공합니다.

### init
처음 modules 관리자를 설치후 해주어야 하는 폴더 생성과 composer 수정을 자동으로 처리해 줍니다.
```
php artisan module:init
```

### url경로를 통하여 다운로드 설치
외부 주소를 입력하여 모듈을 자동으로 다운로드, 설치를 진행할 수 있습니다.

```
php artisan module:geturl 주소명
```

* 깃주소: 외부 접근이 가능한 공개된 저장소만 가능
* zip파일: 직접 다운로드가 가능한 경로만 가능

### 모듈 삭제
설치된 모듈의 파일을 삭제합니다.

```
php artisan module:remove 모듈명
```

## artisan 기본 명령어
모듈관리자의 기본 명령은 [nWidart/laravel-modules](https://nwidart.com/laravel-modules/v6/introduction) 에서 확인이 가능합니다.

### 설치된 모듈리스트 출력
설치된 모듈의 목록을 출력합니다.
```
php artisan module:list
```

