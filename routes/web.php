<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// 모듈에서 설정되 접속 prefix값을 읽어 옵니다.
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
