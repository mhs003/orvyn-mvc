<?php

if (!function_exists('orvyn_dir')) {
    function orvyn_dir($dirpath = '/')
    {
        return realpath(__DIR__ . '/../..') . leading_slash($dirpath);
    }
}

if (!function_exists('log_dir')) {
    function log_dir()
    {
        $log_dir = orvyn_dir('logs');
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755);
        }
        return orvyn_dir('logs');
    }
}


if (!function_exists('is_core_path')) {
    function is_core_path($path)
    {
        static $coreDir = null;

        if ($coreDir === null) {
            $coreDir = realpath(__DIR__ . '/..');
        }

        $realPath = realpath($path);
        if ($realPath === false) {
            return false;
        }

        return str_starts_with($realPath, $coreDir . DIRECTORY_SEPARATOR);
    }
}
