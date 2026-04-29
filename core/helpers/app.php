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