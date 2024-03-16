<?php

namespace Jiny\Modules\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use ZipArchive;

use Nwidart\Modules\Facades\Module;
use CzProject\GitPhp\Git;

class ModuleInstall extends Component
{
    public $actions;
    public $ext;

    public function mount()
    {

    }

    public function render()
    {
        // 모듈 설치 팝업창
        return view("modules::livewire.popup.install");
    }


    /** ----- ----- ----- ----- -----
     *  팝업창 관리
     */
    protected $listeners = [
        'popupInstallOpen','popupInstallClose',
        'install',
        'uninstall',
        'enable',
        'disable'
    ];

    public $popup = false;

    public function popupInstallOpen()
    {
        $this->popup = true;
    }

    public function popupInstallClose()
    {
        $this->popup = false;
    }


    /** ----- ----- ----- ----- -----
     *  설치 프로세스
     */
    public $code;
    public $mode;
    public $item;
    public function install($code)
    {
        $this->code = $code;
        $this->mode = "install";

        // 정보 데이터 읽기
        $row = $this->fetch($code);
        if ($row) {
            foreach($row as $key => $value) {
                $this->item[$key] = $value;
            }
        }

        $this->urlType($this->item['url']);

        $this->popupInstallOpen();
    }

    private function createJsonModule()
    {

        $path = base_path('Modules').DIRECTORY_SEPARATOR;
        $filename = $path.'modules.json';
        $module_info = DB::table("jiny_modules")->get();
        $json = json_encode($module_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        file_put_contents($filename, $json);

    }

    private function urlType($url)
    {
        $pos = strrpos($url,'.');
        $ext = substr($url,$pos+1);

        $this->ext = $ext;
        return $ext;
    }

    /**
     *  파일 다운로드
     */
    public function download()
    {

        if($this->item['url']) {
            // 다운로드 url 체크
            $ext = $this->urlType($this->item['url']);

            $path = base_path('Modules').DIRECTORY_SEPARATOR;
            if(!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            if($ext == "zip") {
                // 1. 다운로드
                $filename = $path.str_replace("/","-",$this->item['code']).".zip";
                $source = $this->item['url'];
                $response = (new Client)->get($source);
                file_put_contents($filename, $response->getBody());

                $vendor = explode("/",$this->item['code']);

                // 2. 압축풀기
                if (file_exists($filename)) {
                    $archive = new ZipArchive;
                    $archive->open($filename);
                    $archive->extractTo($path.$vendor[0]); // 압축풀기
                    $archive->close();

                    // 다운로드 파일 삭제
                    unlink($filename);
                }
            } else
            if($ext == "git") {
                $vendor = explode("/",$this->item['code']);

                //dd($path."/".$vendor[0]);
            }

            // 4. DB 정보 갱신
            $row = DB::table("jiny_modules")->where('code',$this->item['code'])->first();
            if($row) {
                // 기존 설치되어 있는 경우, 설치일자만 재설정
                DB::table("jiny_modules")->where('code',$this->item['code'])->update([
                    'installed'=> date("Y-m-d H:i:s")
                ]);
            }

        } else {
            // 다운로드 url이 없습니다.
        }

        $this->item=[]; // 초기화
        $this->mode = null;
        $this->popupInstallClose();


        // 모듈 정보파일 새로 생성
        //$this->createJsonModule();

        // Livewire Table을 갱신을 호출합니다.
        $this->emit('refeshTable');

    }

    public function clone($code)
    {
        $this->code = $code;
        $this->mode = "clone";

        // 정보 데이터 읽기
        $row = $this->fetch($code);
        if ($row) {
            foreach($row as $key => $value) {
                $this->item[$key] = $value;
            }
        }

        $this->popupInstallOpen();
    }


    public function repoClone()
    {
        $moduleName = $this->moduleName($this->item['code']);

        // 경로 생성
        //$vendor = explode("/",$this->item['code']);
        $path = base_path('Modules').DIRECTORY_SEPARATOR.$moduleName;
        if(!is_dir($path)) {
            mkdir($path, 0777, true);
//chmod($path, 777);
        }

//dd($path);

        // 깃 저장소 복제
        $git = new Git;
        $repo = $git->cloneRepository($this->item['url'], $path);

        // 4. DB 정보 갱신
        $row = DB::table("jiny_modules")->where('code',$this->item['code'])->first();
        if($row) {
            // 기존 설치되어 있는 경우, 설치일자만 재설정
            DB::table("jiny_modules")->where('code',$this->item['code'])->update([
                'enable'=>1,
                'installed'=> date("Y-m-d H:i:s")
            ]);
        }

        // 모듈 활성화
        if($module = Module::find($moduleName)) {
            $module->enable();
        }


        $this->item=[]; // 초기화
        $this->mode = null;
        $this->popupInstallClose();

        // 모듈 정보파일 새로 생성
        //$this->createJsonModule();

        // Livewire Table을 갱신을 호출합니다.
        $this->emit('refeshTable');
    }

    // CamelCase 형태로 모듈 이름 반환
    private function moduleName($code)
    {
        return moduleName($code);
        /*
        $temp = explode('-',$code);
        $moduleName = "";
        foreach($temp as $name) {
            $moduleName .= ucfirst($name);
        }
        return $moduleName;
        */
    }


    public function repoPull()
    {
        // 경로 생성
        $vendor = explode("/",$this->code);
        $path = base_path('Modules').DIRECTORY_SEPARATOR.$vendor[0];
        if(!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // 깃 저장소 복제
        $git = new Git;
        $repo = $git->open($path);
        $repo->pull('origin');

        $this->item=[]; // 초기화
        $this->mode = null;
        $this->popupInstallClose();

        // Livewire Table을 갱신을 호출합니다.
        // 생략 // $this->emit('refeshTable');
    }

    public function upgrade()
    {

        $this->item=[]; // 초기화
        $this->mode = null;
        $this->popupInstallClose();

        // Livewire Table을 갱신을 호출합니다.
        $this->emit('refeshTable');

    }


    /** ----- ----- ----- ----- -----
     *  제거 프로세스
     */
    public function uninstall($code)
    {
        $this->code = $code;
        $this->mode = "uninstall";

        // 정보 데이터 읽기
        $row = $this->fetch($code);
        if ($row) {
            foreach($row as $key => $value) {
                $this->item[$key] = $value;
            }
        }

        $this->popupInstallOpen();
    }

    public function remove()
    {
        if ($this->item['code']) {

            $moduleName = $this->moduleName($this->item['code']);

            // 모든 파일을 삭제
            $path = base_path('Modules').DIRECTORY_SEPARATOR;
            $filename = $path.$moduleName;
            if(file_exists($filename) && is_dir($filename)) {
                $this->unlinkAll($filename);
            }

            // 테이블 갱신
            DB::table("jiny_modules")->where('code',$this->item['code'])->update([
                'enable'=>0,
                'installed'=> ""
            ]);
        }

        $this->item=[]; // 초기화
        $this->mode = null;
        $this->popupInstallClose();

        // 모듈 정보파일 새로 생성
        //$this->createJsonModule();

        // Livewire Table을 갱신을 호출합니다.
        $this->emit('refeshTable');
    }

    // delete all files and sub-folders from a folder
    public function unlinkAll($dir) {
        foreach( scandir($dir) as $file) {
            if($file == "." || $file == "..") continue;
            if(is_dir($dir.DIRECTORY_SEPARATOR.$file)) {
                $this->unlinkAll($dir.DIRECTORY_SEPARATOR.$file);
            } else {
                chmod($dir.DIRECTORY_SEPARATOR.$file, 0777); // permission 방지
                unlink($dir.DIRECTORY_SEPARATOR.$file);
            }
        }
        rmdir($dir);
    }


    protected function fetch($code)
    {
        $row = DB::table("jiny_modules")->where('code',$code)->first();
        return $row;
    }


    public function enable($code)
    {
        $module = Module::find($code);
        $module->enable();

        DB::table("jiny_modules")->where('code',$code)->update([
            'enable'=>1
        ]);

        // Livewire Table을 갱신을 호출합니다.
        $this->emit('refeshTable');
    }

    public function disable($code)
    {
        $module = Module::find($code);
        $module->disable();

        DB::table("jiny_modules")->where('code',$code)->update([
            'enable'=>0
        ]);

        // Livewire Table을 갱신을 호출합니다.
        $this->emit('refeshTable');
    }


    /** ----- ----- ----- ----- -----
     *  json 데이터 읽기
     */
    /*
    public $dataType = "table";
    protected function dataFetch($actions)
    {
        // table 필드를 source로 활용
        if(isset($actions['table']) && $actions['table']) {
            $source = $actions['table'];
        }

        if(isset($actions['source']) && $actions['source']) {
            $source = $actions['source'];
        }

        //$source = "https://jinytheme.github.io/store/themelist.json";
        if ($source) {
            if($pos = strpos($source,"://")) {
                $this->dataType = "uri";

                // url 리소스
                $response = HTTP::get($source);
                $body = $response->body();
                $json = json_decode($body);
                return $json->data;
            } else {
                $this->dataType = "file";

                // 파일 리소스
                $path = resource_path().$source;
                if (file_exists($path)) {
                    $json = file_get_contents($path);
                    $rows = json_decode($json)->data;
                    return $rows;
                }
            }
        }

        return [];
    }
    */
}
