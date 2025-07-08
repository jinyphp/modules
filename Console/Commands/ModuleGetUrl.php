<?php

namespace Jiny\Modules\Console\Commands;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

use ZipArchive;

class ModuleGetUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:geturl {url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'module download from url';

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
        $url = $this->argument('url');

        if (!$url) {
            $this->error("URL 경로가 입력되지 않았습니다.");
            $this->info("사용법: php artisan module:geturl <url>");
            return 1;
        }

        $this->info("URL 확인 중: {$url}");

        $downloadUrl = $this->checkUrl($url);
        if (!$downloadUrl) {
            $this->error("유효하지 않은 URL입니다. GitHub 저장소 URL을 입력해주세요.");
            return 1;
        }

        $this->info("다운로드 중...");

        try {
            $zipFile = $this->download($downloadUrl);
            if (!$zipFile) {
                $this->error("다운로드에 실패했습니다.");
                return 1;
            }

            $this->info("압축 해제 중...");
            $extractPath = $this->unzip($zipFile);

            if (!$extractPath) {
                $this->error("압축 해제에 실패했습니다.");
                return 1;
            }

            $this->info("모듈 정보 확인 중...");
            $moduleInfo = $this->getModuleInfo($extractPath);

            if (!$moduleInfo) {
                $this->error("module.json 파일을 찾을 수 없습니다.");
                return 1;
            }

            $this->info("모듈 설치 중...");
            $success = $this->installModule($extractPath, $moduleInfo);

            if ($success) {
                $this->info("모듈이 성공적으로 설치되었습니다: {$moduleInfo['name']}");
                $this->info("설치 경로: " . base_path("modules/{$moduleInfo['vendor']}/{$moduleInfo['package']}"));
            } else {
                $this->error("모듈 설치에 실패했습니다.");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("오류가 발생했습니다: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * URL을 확인하고 다운로드 URL을 반환합니다.
     */
    private function checkUrl($url)
    {
        // GitHub 저장소 URL 처리
        if (preg_match('/^https?:\/\/github\.com\/([^\/]+\/[^\/]+)(?:\.git)?$/', $url, $matches)) {
            $repo = $matches[1];
            return "https://github.com/{$repo}/archive/refs/heads/master.zip";
        }

        // GitHub 저장소 URL (이미 .git 포함)
        if (preg_match('/^https?:\/\/github\.com\/([^\/]+\/[^\/]+)\.git$/', $url, $matches)) {
            $repo = $matches[1];
            return "https://github.com/{$repo}/archive/refs/heads/master.zip";
        }

        // 직접 ZIP 파일 URL
        if (preg_match('/\.zip$/', $url)) {
            return $url;
        }

        // GitHub 저장소의 특정 브랜치
        if (preg_match('/^https?:\/\/github\.com\/([^\/]+\/[^\/]+)\/tree\/(.+)$/', $url, $matches)) {
            $repo = $matches[1];
            $branch = $matches[2];
            return "https://github.com/{$repo}/archive/refs/heads/{$branch}.zip";
        }

        return false;
    }

    /**
     * 파일 확장자를 가져옵니다.
     */
    private function getExtension($file)
    {
        $pos = strrpos($file, '.');
        if ($pos > 0) {
            return substr($file, $pos + 1);
        }
        return '';
    }



    /**
     * 파일을 다운로드합니다.
     */
    public function download($url)
    {
        if (!$url) {
            return false;
        }

        $this->info("다운로드 URL: {$url}");

        try {
            $client = new Client([
                'timeout' => 300, // 5분 타임아웃
                'verify' => false // SSL 인증서 검증 비활성화 (개발 환경용)
            ]);

            $response = $client->get($url);

            if ($response->getStatusCode() !== 200) {
                $this->error("다운로드 실패: HTTP " . $response->getStatusCode());
                return false;
            }

            $path = base_path('modules') . DIRECTORY_SEPARATOR;
            $zipFile = $path . "temp_module_" . uniqid() . ".zip";

            file_put_contents($zipFile, $response->getBody());

            return $zipFile;

        } catch (ClientException $e) {
            $this->error("다운로드 오류: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->error("예상치 못한 오류: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 압축 파일을 해제합니다.
     */
    public function unzip($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $archive = new ZipArchive;
        if ($archive->open($file) !== true) {
            return false;
        }

        $extractPath = str_replace(".zip", "", $file);
        $success = $archive->extractTo($extractPath);
        $archive->close();

        // 다운로드 파일 삭제
        unlink($file);

        return $success ? $extractPath : false;
    }

    /**
     * 모듈 정보를 가져옵니다.
     */
    private function getModuleInfo($extractPath)
    {
        $items = scandir($extractPath);

        // 첫 번째 디렉토리 찾기 (보통 저장소명-브랜치명 형태)
        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..' && is_dir($extractPath . '/' . $item)) {
                $moduleJsonPath = $extractPath . '/' . $item . '/module.json';
                if (file_exists($moduleJsonPath)) {
                    $jsonContent = file_get_contents($moduleJsonPath);
                    $moduleData = json_decode($jsonContent, true);

                    if ($moduleData && isset($moduleData['vendor']) && isset($moduleData['package'])) {
                        return [
                            'name' => $moduleData['name'] ?? "{$moduleData['vendor']}/{$moduleData['package']}",
                            'vendor' => $moduleData['vendor'],
                            'package' => $moduleData['package'],
                            'version' => $moduleData['version'] ?? '1.0.0',
                            'description' => $moduleData['description'] ?? '',
                            'path' => $extractPath . '/' . $item
                        ];
                    }
                }
            }
        }

        return false;
    }

    /**
     * 모듈을 설치합니다.
     */
    private function installModule($extractPath, $moduleInfo)
    {
        $targetPath = base_path("modules/{$moduleInfo['vendor']}/{$moduleInfo['package']}");

        // 기존 모듈이 있으면 백업
        if (is_dir($targetPath)) {
            $backupPath = $targetPath . '_backup_' . date('Y-m-d_H-i-s');
            if (!rename($targetPath, $backupPath)) {
                return false;
            }
        }

        // 모듈 디렉토리 생성
        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0755, true);
        }

        // 모듈 이동
        if (!rename($moduleInfo['path'], $targetPath)) {
            return false;
        }

        // 임시 디렉토리 정리
        if (is_dir($extractPath)) {
            $this->removeDirectory($extractPath);
        }

        return true;
    }

    /**
     * 디렉토리를 재귀적으로 삭제합니다.
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

}
