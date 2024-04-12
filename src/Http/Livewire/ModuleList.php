<?php
namespace Jiny\Modules\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use ZipArchive;

use Nwidart\Modules\Facades\Module;
use CzProject\GitPhp\Git;

class ModuleList extends Component
{
    public $modules=[];

    public function mount()
    {
        //$rows = $this->getStoreUrl();
        //$this->modules = $this->parsing($rows);
    }

    public function render()
    {


        $modulePath = base_path('/modules');
        $filename = "modules_statuses.json";
        $path = base_path().DIRECTORY_SEPARATOR.$filename;
        $content = file_get_contents($path);
        $rows = json_decode($content);

        $this->modules=[]; //초기화
        foreach($rows as $key => $value) {
            $this->modules[$key] = new \stdClass();

            if($module = $this->getModuleInfo($key)) {
                $this->modules[$key]->description = $module->description;
            } else {
                $this->modules[$key]->description = "";
            }

            $this->modules[$key]->enable = $value;

            $this->modules[$key]->title = $key;

            $this->modules[$key]->image = null;

            $this->modules[$key]->version = null;

            // 실제 설치가 되어 있는 확인
            if(is_dir($modulePath.DIRECTORY_SEPARATOR.$key)) {
                $this->modules[$key]->installed = true;
                if(is_dir($modulePath.DIRECTORY_SEPARATOR.$key.DIRECTORY_SEPARATOR.".git")) {
                    $this->modules[$key]->git = true;

                    $git = new Git;
                    $repo = $git->open($modulePath.DIRECTORY_SEPARATOR.$key);
                    $tag = $repo->getTags();
                    if($tag) {
                        //dd($tag);
                        $this->modules[$key]->version = end($tag);
                    }


                } else {
                    $this->modules[$key]->git = false;
                }
            } else {
                $this->modules[$key]->installed = false;
                $this->modules[$key]->git = false;
            }


            $this->modules[$key]->ext = null;
            $this->modules[$key]->code = null;
        }



        return view("modules::modules.list");
    }

    public function getModuleInfo($code)
    {
        $modulePath = base_path('/modules');
        if(is_dir($modulePath.DIRECTORY_SEPARATOR.$code)) {
            $str = file_get_contents($modulePath.DIRECTORY_SEPARATOR.$code.DIRECTORY_SEPARATOR."module.json");
            return json_decode($str);
        }

        return false;
    }


}
