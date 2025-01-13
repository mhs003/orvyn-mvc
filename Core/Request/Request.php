<?php

namespace Core\Request;

class Request
{

    // Get the full request URI (path + query string)
    public static function fullUrl()
    {
        return "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    // Get the current request URI (path only)
    public static function uri()
    {
        return parse_url(self::fullUrl(), PHP_URL_PATH);
    }

    // Get query string parameters
    public static function query($key = null, $default = null)
    {
        if ($key) {
            return $_GET[$key] ?? $default;
        }
        return $_GET;
    }

    // Get POST data
    public static function post($key = null, $default = null)
    {
        if ($key) {
            return $_POST[$key] ?? $default;
        }
        return $_POST;
    }

    // Get all input data (either from GET or POST)
    public static function input($key = null, $default = null)
    {
        if ($key) {
            return $_REQUEST[$key] ?? $default;
        }
        return $_REQUEST;
    }

    // Retrieve JSON input from request body
    public static function json($key = null, $default = null)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($key) {
            return $input[$key] ?? $default;
        }
        return $input;
    }

    // Check if request has a certain key in GET, POST, or REQUEST
    public static function has($key)
    {
        return isset($_GET[$key]) || isset($_POST[$key]) || isset($_REQUEST[$key]);
    }

    // Retrieve all headers
    public static function header($key = null)
    {
        $headers = getallheaders();
        if ($key) {
            return $headers[$key] ?? null;
        }
        return $headers;
    }

    // Retrieve a specific header
    public static function getHeader($key)
    {
        return self::header($key);
    }

    // Retrieve the request method (GET, POST, etc.)
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    // Check if request is of a specific method (GET, POST, etc.)
    public static function isMethod($method)
    {
        return strtoupper($method) === strtoupper(self::method());
    }

    // Retrieve file input (uploads)
    public static function file($key = null)
    {
        if ($key) {
            return $_FILES[$key] ?? null;
        }
        return $_FILES;
    }

    // Retrieve the host from the URL
    public static function host()
    {
        return $_SERVER['HTTP_HOST'];
    }

    // Retrieve the full URL with the query string
    public static function url()
    {
        return self::fullUrl();
    }

    // Retrieve the user agent string
    public static function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    // Check if request is secure (HTTPS)
    public static function isSecure()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }

    // Get the client IP address
    public static function ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    // Get the client IP address, considering proxies
    public static function clientIp()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    }
}
