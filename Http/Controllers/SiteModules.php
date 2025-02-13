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
class SiteModules extends SiteController
{
    public function __construct()
    {
        parent::__construct();  // setting Rule 초기화
        $this->setVisit($this); // Livewire와 양방향 의존성 주입

        $this->actions['view']['layout']
            = inSlotView("modules",
                "jiny-modules::site.modules.layout");



    }



}
