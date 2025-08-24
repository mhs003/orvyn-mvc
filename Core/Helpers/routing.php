<?php

// Redirect

if (!function_exists('redirect')) {
    function redirect($url = null)
    {
        if ($url) {
            return \Core\Response\Redirect::to($url);
        } else {
            return new \Core\Response\Redirect();
        }
    }
}

// Response

if (!function_exists('response')) {
    function response()
    {
        return new \Core\Response\Response();
    }
}

// Misc

if (!function_exists('route')) {
    function route($name, $params = [])
    {
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
