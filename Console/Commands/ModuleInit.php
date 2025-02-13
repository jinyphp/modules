<?php

namespace Jiny\Modules\Console\Commands;

use Illuminate\Console\Command;

class ModuleInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'module remove';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 디렉터리 생성
        $path = base_path('modules');
        if(!is_dir($path)) {
            mkdir($path);
            $this->info("create modules folder");
        }

        // 컴포저 정보 변경
        $composer_path = base_path().DIRECTORY_SEPARATOR."composer.json";
        $composer = file_get_contents($composer_path);
        $composer = json_decode($composer);

        $composer->autoload->{"psr-4"}->{"Modules\\"} = "modules/";
        $body = json_encode($composer,JSON_PRETTY_PRINT);
        $body = str_replace('\/','/',$body);
        file_put_contents(base_path().DIRECTORY_SEPARATOR."composer.json", $body);
        $this->info("update composer.json");

        // composer 갱신
        passthru("composer dump-autoload");
        return 0;
    }


}
