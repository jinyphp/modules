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

use Jiny\Table\Http\Controllers\ResourceController;
class Modules extends ResourceController
{
    use \Jiny\WireTable\Http\Trait\Permit;
    use \Jiny\Table\Http\Controllers\SetMenu;

    public function __construct()
    {
        parent::__construct();  // setting Rule 초기화
        $this->setVisit($this); // Livewire와 양방향 의존성 주입

        $this->actions['table'] = "jiny_modules"; // 테이블 정보
        $this->actions['paging'] = 100; // 페이지 기본값

        $this->actions['view']['main'] = "modules::modules.main";
        $this->actions['view']['main_layout'] = "modules::modules.main_layout";

        $this->actions['view']['filter'] = "modules::modules.filter";
        $this->actions['view']['list'] = "modules::modules.list";

        $this->actions['view']['form'] = "modules::modules.form";

        $this->actions['title'] = "모듈목록";
        $this->actions['subtitle'] = "설치된 모듈목록 입니다.";

    }



    public function hookIndexed($wire, $rows)
    {
        // 저장소에서 tag 명령을 통하여 최종 버젼을 확인
        $path = base_path('modules').DIRECTORY_SEPARATOR;

        foreach($rows as $i => $row) {
            if($row->url) {
                $rows[$i]->ext = substr($row->url,strrpos($row->url,'.')+1);
            } else {
                $rows[$i]->ext = "";
            }

            if(is_dir($path.$row->code)) {
                $git = new Git;
                $repo = $git->open($path.$row->code);
                $tags = $repo->getTags();
                if(is_array($tags)) {
                    $version = array_reverse($tags);
                    $rows[$i]->version = $version[0]; //최종버젼
                } else {
                    $rows[$i]->version = null;
                }
            } else {
                $rows[$i]->version = null;
            }


        }


        return $rows;
    }



    public function hookStored($wire, $form)
    {
    }

}
