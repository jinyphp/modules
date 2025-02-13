<?php

namespace Jiny\Modules\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use ZipArchive;

class ModuleRemove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:remove {name?}';

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
        $name = $this->argument('name');
        if($name) {
            $path = base_path('modules').DIRECTORY_SEPARATOR.$name;
            if(file_exists($path)) {
                exec("rm -rf ".$path);
                $this->info("removed");
            } else {
                $this->info("설치된 모듈이 존재하지 않습니다.");
            }
        } else {
            $this->info("제거할 모듈명을 입력해 주세요.");
        }

        return 0;
    }


}
