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
        $filename = "modules_statuses.json";
        $path = base_path().DIRECTORY_SEPARATOR.$filename;
        $content = file_get_contents($path);
        $rows = json_decode($content);

        $this->modules=[]; //초기화
        foreach($rows as $key => $value) {
            $this->modules[$key] = new \stdClass();
            $this->modules[$key]->enable = $value;
            $this->modules[$key]->title = $key;
            $this->modules[$key]->subtitle = "모듈설명입니다.";
            $this->modules[$key]->image = null;
            $this->modules[$key]->installed = null;
            $this->modules[$key]->description = null;
            $this->modules[$key]->version = null;
            $this->modules[$key]->ext = null;
            $this->modules[$key]->code = null;
        }

        return view("modules::modules.list");
    }


}
