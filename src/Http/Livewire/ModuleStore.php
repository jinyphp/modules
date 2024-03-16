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

class ModuleStore extends Component
{
    public $modules=[];

    public function mount()
    {
        $rows = $this->getStoreUrl();
        $this->modules = $this->parsing($rows);
    }

    public function render()
    {
        return view("modules::store.livewire.list");
    }

    // 서버에서 모듈 목록 읽기
    private function getStoreUrl()
    {
        $url = "https://jinyerp-src.github.io/module-server/modules.json";
        $client = new Client([
            'verify' => false, // Disable SSL verification (not recommended)
        ]);
        $body = $client->get($url)->getBody();
        return json_decode($body);
    }

    // 모듈 목록과 설치 정보 비교
    private function parsing($rows)
    {
        // 설치된 모듈 정보
        $module_info = DB::table("jiny_modules")->get();

        $data = [];

        foreach($rows as $i => $row) {
            $item = [];
            foreach($row as $key => $value) {
                $item[$key] = $value;
            }

            if($row->url) {
                $item['ext'] = substr($row->url,strrpos($row->url,'.')+1);
            } else {
                $item['ext'] = null;
            }

            $item['installed'] = null; //초기화
            foreach($module_info as $module) {
                if($row->code == $module->code) {
                    // 설치가 되어 있는 경우
                    $item['installed'] = $module->installed;
                    break;
                }
            }

            $code = $row->code;
            $data[$code] = $item;
        }

        return $data;
    }

    // 목록 배열에서 아이템 코드 정보 읽기
    private function getItem($code)
    {
        foreach( $this->modules as $module) {
            if($module['code'] == $code) {
                return $module;
            }
        }
    }

    // 모듈 설치
    public function install($code)
    {
        $module = $this->getItem($code);
        if($module) {
            if($module['ext'] == "zip") {
                dd($code.": download zip file");
            } else
            if($module['ext'] == "git") {
                // 모듈 git clone 복제
                $this->repoClone($module);
            }
        }
    }

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

    // 모듈 깃복제하기
    private function repoClone($item)
    {
        //dump($item);

        // 모듈이름 설정
        $moduleName = $this->moduleName($item['code']);
        //dd($moduleName);

        // 모듈 설치 경로 생성
        $path = base_path('Modules').DIRECTORY_SEPARATOR.$moduleName;
        if(!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        //dd($path);

        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        // 깃 저장소 복제
        $git = new Git;

        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            $url = str_replace("https://github.com/","git@github.com:",$item['url']); //ssh 접속으로 전환
        } else {
            // localhost
            $url = $item['url'];
        }
        $repo = $git->cloneRepository($url, $path);

        // 4. 모듈정보 DB 삽입
        $module = DB::table("jiny_modules")->where('code', $item['code'])->first();
        if($module) {
            // 모듈 정보 갱신
            DB::table("jiny_modules")->where('code', $item['code'])->update([
                'code' => $item['code'],
                'enable'=>1,
                'installed'=> date("Y-m-d H:i:s")
            ]);
        } else {
            // 신규 추가
            DB::table("jiny_modules")->insert([
                'code' => $item['code'],
                'enable'=>1,
                'installed'=> date("Y-m-d H:i:s"),

                'title' => $item['title'],
                'url' => $item['url'],
                'image' => $item['image'],
                'version' => $item['version'],
                'description' => $item['description'],

                'created_at'=> date("Y-m-d H:i:s"),
                'updated_at'=> date("Y-m-d H:i:s"),
                'user_id'=> Auth::user()->id
            ]);
        }


        // json 정보 갱신
        $code = $item['code'];
        $this->modules[$code]['enable'] = 1;
        $this->modules[$code]['installed'] = date("Y-m-d H:i:s");

        // 모듈 활성화
        $module = Module::find($moduleName);
        if($module) {
            $module->enable();
        }


        //$this->item=[]; // 초기화
        //$this->mode = null;
        //$this->popupInstallClose();

        // 모듈 정보파일 새로 생성
        $this->createJsonModule();


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

    private function createJsonModule()
    {
        $path = base_path('Modules').DIRECTORY_SEPARATOR;
        $filename = $path.'modules.json';
        $module_info = DB::table("jiny_modules")->get();
        $json = json_encode($module_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        file_put_contents($filename, $json);
    }


}
