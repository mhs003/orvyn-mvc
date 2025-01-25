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
            return $uri;
        }
    }
}