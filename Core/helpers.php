<?php

// Redirect

if (!function_exists('redirect')) {
    function redirect($url = null) {
        if ($url) {
            return \Core\Response\Redirect::to($url);
        } else {
            return new \Core\Response\Redirect();
        }
    }
}

// Response

if (!function_exists('response')) {
    function response() {
        return new \Core\Response\Response();
    }
}

// Misc

if (!function_exists('route')) {
    function route($name, $params = []) {
        $uri = \Core\Routing\Router::getRoute($name);

        if ($uri) {
            preg_match_all('/\{\w+\}/', $uri, $matches);
            $params = array_filter($params, fn($param) => $param !== '' && $param !== null && $param !== []);
            if (count($params) < count($matches[0])) {
                ddb("Too few argument passed for route '{$name}'. Expected: " . count($matches[0]) . ", Got: " . count($params));
            }
            foreach ($params as $key => $value) {
                $uri = str_replace("{{$key}}", $value, $uri);
            }
            return $uri;
        }
        ddb("Unknown route '{$name}'");
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null) {
        global $global_config;
        return $global_config->get($key, $default);
    }
}

// Error handling

if (!function_exists('render_error_page')) {
    function render_error_page($code, $message = null) {
        return \Core\ErrorHandlers\HttpError::render($code, $message);
    }
}

if (!function_exists('dd')) {
    function dd(...$vars) {
        \Core\ErrorHandlers\DebugHelper::process_dd(...$vars);
    }
}

if (!function_exists('ddb')) {
    function ddb(...$vars) {
        \Core\ErrorHandlers\DebugHelper::process_ddb(...$vars);
    }
}


if (!function_exists('dump')) {
    function dump(...$vars) {
        \Core\ErrorHandlers\DebugHelper::process_dump($vars);
    }
}


if (!function_exists('log')) {
    function log($var, $logFile = 'debug.log') {
        \Core\ErrorHandlers\DebugHelper::log($var, $logFile);
    }
}


if (!function_exists('final_debug_backtrace')) {
    function final_debug_backtrace($traces, $fname = 'dd') {
        $tracer = null;
        foreach ($traces as $trace) {
            if (isset($trace['function']) && $trace['function'] === $fname) {
                $tracer = $trace;
                break;
            }
        }
        return $tracer;
    }
}

// View Engine

if (!function_exists('view')) {
    function view($template, $data = []) {
        global $viewEngine;
        try {
            return $viewEngine->render($template, $data);
        } catch (\Core\Templating\Exceptions\TemplateException $e) {
            dd("Template Error: " . $e->getMessage());
        }
    }
}