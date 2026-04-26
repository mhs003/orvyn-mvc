<?php

use Core\App;

if (!function_exists('app')) {
    function app(?string $key = null)
    {
        $container = App::$instance;

        return $key ? $container->make($key) : $container;
    }
}