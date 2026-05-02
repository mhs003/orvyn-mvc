<?php

use Core\App;

if (!function_exists('app')) {
    function app(?string $key = null)
    {
        $container = App::$instance;

        return $key ? $container->make($key) : $container;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null)
    {
        global $global_config;
        return $global_config->get($key, $default);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/') : '');
    }
}