<?php

require_once __DIR__ . "/Helpers/str.php";
require_once __DIR__ . "/Helpers/debug.php";
require_once __DIR__ . "/Helpers/routing.php";
require_once __DIR__ . "/Helpers/utils.php";

if (!function_exists('config')) {
    function config(string $key, mixed $default = null)
    {
        global $global_config;
        return $global_config->get($key, $default);
    }
}

// View Engine

if (!function_exists('view')) {
    function view($template, $data = [])
    {
        global $viewEngine;
        try {
            return $viewEngine->render($template, $data);
        } catch (\Core\Templating\Exceptions\TemplateException $e) {
            dd("Template Error: " . $e->getMessage());
        }
    }
}
