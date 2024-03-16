<?php

namespace Jiny\Modules\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Config\Http\Controllers\ConfigController;
class SettingController extends ConfigController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['filename'] = "jiny/modules/setting"; // 설정파일명(경로)
        $this->actions['view']['form'] = "modules::setting.form";

        $this->actions['view']['main'] = "modules::setting.layout";
    }
}
