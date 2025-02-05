<?php
namespace Jiny\Modules\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

use Nwidart\Modules\Facades\Module;
use CzProject\GitPhp\Git;

use Jiny\Site\Http\Controllers\SiteController;
class SiteModulesDetail extends SiteController
{
    public function __construct()
    {
        parent::__construct();  // setting Rule 초기화
        $this->setVisit($this); // Livewire와 양방향 의존성 주입

        $this->actions['view']['layout']
            = inSlotView("modules.detail",
                "jiny-modules::site.modules_detail.layout");

    }

    public function index(Request $request)
    {
        $code = $request->code;
        $this->actions['code'] = $code;
        $this->params['code'] = $code;

        // $plan = DB::table('license_plan')->where('code', $code)->get();
        // $this->params['plan'] = $plan;

        return parent::index($request);
    }



}
