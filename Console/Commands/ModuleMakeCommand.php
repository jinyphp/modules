<?php

namespace Jiny\Modules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class ModuleMakeCommand extends Command
{
    protected $signature = 'module:make {name}';
    protected $description = '새로운 모듈을 /modules/{vendor}/{package} 구조로 생성합니다.';

    public function handle()
    {
        $name = $this->argument('name'); // 예: jiny/layouts
        if (!Str::contains($name, '/')) {
            $this->error('이름은 vendor/package 형식이어야 합니다. 예: jiny/layouts');
            return 1;
        }
        [$vendor, $package] = explode('/', $name, 2);
        $basePath = base_path("modules/{$vendor}/{$package}");
        $fs = new Filesystem();

        if ($fs->exists($basePath)) {
            $this->error('이미 해당 모듈 폴더가 존재합니다: '.$basePath);
            return 1;
        }

        // 디렉토리 구조 생성
        $fs->makeDirectory($basePath.'/Http/Controllers', 0755, true);
        $fs->makeDirectory($basePath.'/resources/views', 0755, true);
        $fs->makeDirectory($basePath.'/routes', 0755, true);
        $fs->makeDirectory($basePath.'/database/migrations', 0755, true);
        $fs->makeDirectory($basePath.'/config', 0755, true);
        $fs->makeDirectory($basePath.'/resources/lang', 0755, true);

        // 치환 변수
        $namespace = Str::studly($vendor).'\\'.Str::studly($package);
        $class = Str::studly($package).'ServiceProvider';
        $viewNamespace = $vendor.'-'.$package;
        $moduleTitle = Str::title($package);

        $replacements = [
            '{{vendor}}' => $vendor,
            '{{package}}' => $package,
            '{{namespace}}' => $namespace,
            '{{class}}' => $class,
            '{{view_namespace}}' => $viewNamespace,
            '{{module_title}}' => $moduleTitle,
            '{{table}}' => Str::plural($package),
        ];

        // stub 복사 및 치환
        $this->copyStub('module.json.stub', "$basePath/module.json", $replacements);
        $this->copyStub('ServiceProvider.stub', "$basePath/{$class}.php", $replacements);
        $this->copyStub('DefaultController.stub', "$basePath/Http/Controllers/DefaultController.php", $replacements);
        $this->copyStub('index.blade.stub', "$basePath/resources/views/index.blade.php", $replacements);
        $this->copyStub('README.md.stub', "$basePath/README.md", $replacements);
                $this->copyStub('web.php.stub', "$basePath/routes/web.php", $replacements);
        $this->copyStub('api.php.stub', "$basePath/routes/api.php", $replacements);
        $this->copyStub('admin.php.stub', "$basePath/routes/admin.php", $replacements);
        $this->copyStub('config.php.stub', "$basePath/config.php", $replacements);

        $this->info("모듈이 생성되었습니다: modules/{$vendor}/{$package}");
        return 0;
    }

    protected function copyStub($stub, $target, $replacements)
    {
        $stubPath = base_path('vendor/jiny/modules/stubs/'.$stub);
        if (!file_exists($stubPath)) {
            $this->error('stub 파일이 없습니다: '.$stubPath);
            return;
        }
        $content = file_get_contents($stubPath);
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        file_put_contents($target, $content);
    }
}
