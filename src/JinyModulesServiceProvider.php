<?php

namespace Jiny\Modules;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;

class JinyModulesServiceProvider extends ServiceProvider
{
    private $package = "jiny-modules";
    public function boot()
    {
        // 모듈: 라우트 설정
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', $this->package);

        // 데이터베이스
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('modules.php'),
        ]);

        //actions
        // php artisan vendor:publish --tag=actions
        $this->publishes([
            __DIR__.'/../resources/actions' => resource_path('actions'),
        ], 'actions');


        // artisan 명령등록
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Jiny\Modules\Console\Commands\ModuleInit::class,
                \Jiny\Modules\Console\Commands\ModuleGetUrl::class,
                \Jiny\Modules\Console\Commands\ModuleRemove::class
            ]);
        }
    }

    public function register()
    {

        /* 라이브와이어 컴포넌트 등록 */
        $this->app->afterResolving(BladeCompiler::class, function () {
            Livewire::component('ModuleInstall', \Jiny\Modules\Http\Livewire\ModuleInstall::class);
            Livewire::component('ModuleList', \Jiny\Modules\Http\Livewire\ModuleList::class);
            Livewire::component('ModuleStore', \Jiny\Modules\Http\Livewire\ModuleStore::class);
            Livewire::component('ModuleStoreInstall', \Jiny\Modules\Http\Livewire\ModuleStoreInstall::class);


            Livewire::component('jiny-license-store-detail',
                \Jiny\Modules\Http\Livewire\LicenseStoreDetail::class);
        });



    }

}
