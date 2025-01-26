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
            // Replace any parameters in the route
            foreach ($params as $key => $value) {
                $uri = str_replace("{{$key}}", $value, $uri);
            }
            if(preg_match('/\{(\w+)\}/', $uri, $matches)) {
                dd("Missing parameter for route '{$name}': {{$matches[1]}}");
            }
            return $uri;
        }
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
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        \Core\ErrorHandlers\DebugHelper::dd($trace, ...$vars);
    }
}


if (!function_exists('dump')) {
    function dump(...$vars) {
        \Core\ErrorHandlers\DebugHelper::dump($vars);
    }
}


if (!function_exists('log')) {
    function log($var, $logFile = 'debug.log') {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        \Core\ErrorHandlers\DebugHelper::log($trace, $var, $logFile);
    }
}