<?php
namespace Jiny\Modules\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Nwidart\Modules\Facades\Module;
use CzProject\GitPhp\Git;
use GuzzleHttp\Client;

use Jiny\Admin\Http\Controllers\AdminController;
class ModuleStore extends AdminController
{
    use \Jiny\WireTable\Http\Trait\Permit;
    //use \Jiny\Table\Http\Controllers\SetMenu;

    public function __construct()
    {
        parent::__construct();  // setting Rule 초기화
        $this->setVisit($this); // Livewire와 양방향 의존성 주입

        $this->actions['view']['main'] = "modules::store.main";

        $this->actions['title'] = "모듈 스토어";
        $this->actions['subtitle'] = "다양한 모듈을 제공합니다.";
    }


    public function index(Request $request)
    {
        return view($this->actions['view']['main'],[
            'actions' => $this->actions
        ]);
    }


}
