<?php

use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;


if(!function_exists("is_module")) {
    function is_module($code) {
        // 대소문자 구분
        return isModule($code);
    }
}

if(!function_exists("isModule")) {
    function isModule($code) {
        if(Module::has($code)) {
            return true;
        }
        return false;
    }
}

if(!function_exists("moduleName")) {
    function moduleName($code)
    {
        $temp = explode('-',$code);
        $moduleName = "";

        foreach($temp as $name) {
            if(strlen($name) <=2) {
                $moduleName .= strtoupper($name);
            } else {
                $moduleName .= ucfirst($name);
            }

        }
        return $moduleName;
    }
}

