<?php

namespace Jiny\Modules\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ModuleController extends \App\Http\Controllers\Controller
{
    /**
     * 설치된 모든 모듈을 목록으로만 출력합니다.
     * 상세 페이지로 리다이렉트하지 않습니다.
     */
    public function index()
    {
        // 모든 모듈을 배열로 가져옵니다.
        $modules = $this->discoverModules();

        // 무조건 목록 뷰로만 출력합니다.
        return view('jiny-modules::admin.modules.index', compact('modules'));
    }

    /**
     * 특정 모듈의 상세 정보를 표시합니다.
     */
    public function show($vendor, $package)
    {
        $module = $this->getModuleInfo($vendor, $package);

        if (!$module) {
            return redirect()->route('admin.modules.index')
                ->with('error', "모듈 {$vendor}/{$package}을 찾을 수 없습니다.");
        }

        return view('jiny-modules::admin.modules.show', compact('module'));
    }

    /**
     * 모듈을 활성화/비활성화합니다.
     */
    public function toggle(Request $request, $vendor, $package)
    {
        $module = $this->getModuleInfo($vendor, $package);

        if (!$module) {
            return response()->json(['success' => false, 'message' => '모듈을 찾을 수 없습니다.']);
        }

        // 여기에 모듈 활성화/비활성화 로직을 구현할 수 있습니다.
        // 예: 설정 파일에 상태 저장, 데이터베이스에 상태 저장 등

        return response()->json(['success' => true, 'message' => '모듈 상태가 변경되었습니다.']);
    }

    /**
     * modules 폴더에서 모든 패키지를 발견합니다.
     */
    private function discoverModules(): array
    {
        $modulesPath = base_path('modules');
        $discoveredModules = [];

        if (!File::exists($modulesPath)) {
            return $discoveredModules;
        }

        $vendorDirs = File::directories($modulesPath);

        foreach ($vendorDirs as $vendorDir) {
            $vendorName = basename($vendorDir);
            $packageDirs = File::directories($vendorDir);

            foreach ($packageDirs as $packageDir) {
                $packageName = basename($packageDir);
                $serviceProviderPath = $this->findServiceProvider($packageDir, $vendorName, $packageName);

                if ($serviceProviderPath) {
                    $info = $this->readModuleInfo($packageDir);
                    $discoveredModules[] = [
                        'vendor' => $vendorName,
                        'package' => $packageName,
                        'name' => $vendorName.'/'.$packageName,
                        'path' => $packageDir,
                        'serviceProvider' => $serviceProviderPath,
                        'namespace' => $this->generateNamespace($vendorName, $packageName),
                        'info' => $info,
                        'title' => $info['title'] ?? $vendorName.'/'.$packageName,
                        'version' => $info['version'] ?? '-',
                        'description' => $info['description'] ?? '',
                        'author' => $info['author'] ?? '',
                        'license' => $info['license'] ?? '',
                        'dependencies' => $info['dependencies'] ?? []
                    ];
                }
            }
        }

        return $discoveredModules;
    }

    /**
     * 특정 모듈 정보를 가져옵니다.
     */
    private function getModuleInfo(string $vendor, string $package): ?array
    {
        $modules = $this->discoverModules();

        foreach ($modules as $module) {
            if ($module['vendor'] === $vendor && $module['package'] === $package) {
                return $module;
            }
        }

        return null;
    }

    /**
     * 패키지 디렉토리에서 ServiceProvider를 찾습니다.
     */
    private function findServiceProvider(string $packageDir, string $vendorName, string $packageName): ?string
    {
        $patterns = [
            $vendorName . ucfirst($packageName) . 'ServiceProvider.php',
            ucfirst($packageName) . 'ServiceProvider.php',
            $vendorName . 'ServiceProvider.php',
            'ServiceProvider.php'
        ];

        foreach ($patterns as $pattern) {
            $filePath = $packageDir . '/' . $pattern;
            if (File::exists($filePath)) {
                return $filePath;
            }
        }

        $phpFiles = File::glob($packageDir . '/*.php');
        foreach ($phpFiles as $file) {
            $content = File::get($file);
            if (str_contains($content, 'extends ServiceProvider') ||
                str_contains($content, 'extends \Illuminate\Support\ServiceProvider')) {
                return $file;
            }
        }

        return null;
    }

    /**
     * module.json 파일을 읽어 반환합니다.
     */
    private function readModuleInfo(string $modulePath): array
    {
        $jsonPath = $modulePath.'/module.json';
        if (File::exists($jsonPath)) {
            $json = File::get($jsonPath);
            return json_decode($json, true) ?? [];
        }
        return [];
    }

    /**
     * 네임스페이스를 생성합니다.
     */
    private function generateNamespace(string $vendorName, string $packageName): string
    {
        return ucfirst($vendorName) . '\\' . ucfirst($packageName);
    }
}
