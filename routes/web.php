<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// 모듈 관리 라우트
Route::middleware(['web','auth:sanctum', 'verified'])
    ->name('admin.')
    ->prefix('admin')->group(function () {
        Route::get('modules', [\Jiny\Modules\Http\Controllers\ModuleController::class, 'index'])
            ->name('modules.index');
        Route::get('modules/{vendor}/{package}', [\Jiny\Modules\Http\Controllers\ModuleController::class, 'show'])
            ->name('modules.show');
        Route::post('modules/{vendor}/{package}/toggle', [\Jiny\Modules\Http\Controllers\ModuleController::class, 'toggle'])
            ->name('modules.toggle');
    });


// 모듈에서 설정되 접속 prefix값을 읽어 옵니다.
if(function_exists("admin_prefix")) {
    $prefix = admin_prefix();

    Route::middleware(['web','auth:sanctum', 'verified'])
    ->name('admin.')
    ->prefix($prefix)->group(function () {

        // 설치된 모듈목록
        Route::get('modules',[\Jiny\Modules\Http\Controllers\Modules::class,"index"]);

        // 모듈 스토어
        Route::get('module/store',[\Jiny\Modules\Http\Controllers\ModuleStore::class,"index"]);

        // 모듈 설정
        Route::resource('module/setting', \Jiny\Modules\Http\Controllers\SettingController::class);

        Route::get('modules/home',function(){
            return view("modules::layouts.app");
        });



    });
}


/**
 * 모듈 라이센스 구매
 */

 Route::middleware(['web','auth:sanctum', 'verified'])
 ->name('site.')
 ->prefix('/')->group(function () {
    Route::get('modules',[
        \Jiny\Modules\Http\Controllers\SiteModules::class,
        "index"
    ]);

    Route::get('modules/detail/{code}',[
        \Jiny\Modules\Http\Controllers\SiteModulesDetail::class,
        "index"
    ]);

 });
