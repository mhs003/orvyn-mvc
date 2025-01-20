<?php

namespace Core\Request;

/**
 * Request Class
 * 
 * Handles HTTP request data and provides a clean interface
 * for accessing request information
 * 
 * @package Core\Request
 */
class Request
{

    /**
     * Get the full request URL including query string
     * 
     * @return string
     */
    public static function fullUrl()
    {
        return "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    /**
     * Get the current request URI (path only)
     * 
     * @return string|null
     */
    public static function uri()
    {
        return parse_url(self::fullUrl(), PHP_URL_PATH);
    }

    /**
     * Get query string parameters
     * 
     * @param string|null $key Specific parameter key
     * @param mixed|null $default Default value if key not found
     * @return mixed
     */
    public static function query($key = null, $default = null)
    {
        if ($key) {
            return $_GET[$key] ?? $default;
        }
        return $_GET;
    }

    /**
     * Get post data parameters
     * 
     * @param string|null $key Specific parameter key
     * @param mixed|null $default Default value if key not found
     * @return mixed
     */
    public static function post($key = null, $default = null)
    {
        if ($key) {
            return $_POST[$key] ?? $default;
        }
        return $_POST;
    }

    /**
     * Get all input data parameters (GET, POST, REQUEST etc.)
     * 
     * @param string|null $key Specific parameter key
     * @param mixed|null $default Default value if key not found
     * @return mixed
     */
    public static function input($key = null, $default = null)
    {
        if ($key) {
            return $_REQUEST[$key] ?? $default;
        }
        return $_REQUEST;
    }

    /**
     * Retrieve JSON input data from the request body
     * 
     * @param string|null $key Specific parameter key
     * @param mixed|null $default Default value if key not found
     * @return mixed
     */
    public static function json($key = null, $default = null)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($key) {
            return $input[$key] ?? $default;
        }
        return $input;
    }

    /**
     * Check if request has a certain key in GET, POST, or REQUEST
     * 
     * @param string|null $key Specific parameter key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_GET[$key]) || isset($_POST[$key]) || isset($_REQUEST[$key]);
    }

    /**
     * Retrieve all headers or a specific header
     * 
     * @param string|null $key Specific header key
     * @return mixed|null
     */
    public static function header($key = null)
    {
        $headers = getallheaders();
        if ($key) {
            return $headers[$key] ?? null;
        }
        return $headers;
    }

    /**
     * Retrieve specific header
     * 
     * @param string|null $key Specific header key
     * @return mixed
     */
    public static function getHeader($key)
    {
        return self::header($key);
    }

    /**
     * Retrieve the request method (GET, POST, etc.)
     * 
     * @return string
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request method is a certain method
     * 
     * @param string $method Method to check
     * @return bool
     */
    public static function isMethod($method)
    {
        return strtoupper($method) === strtoupper(self::method());
    }

    /**
     * Retrieve uploaded files from the request
     * 
     * @param string|null $key The file input name
     * @return array|null Returns specific file data if key provided, all files otherwise
     */
    public static function file($key = null)
    {
        if ($key) {
            return $_FILES[$key] ?? null;
        }
        return $_FILES;
    }

    /**
     * Get the HTTP host from the current request
     * 
     * @return string The hostname
     */
    public static function host()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Get the complete URL of the current request
     * 
     * @return string The full URL including query string
     */
    public static function url()
    {
        return self::fullUrl();
    }

    /**
     * Get the user agent string from the request
     * 
     * @return string|null The user agent string or null if not set
     */
    public static function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * Determine if the request was made over HTTPS
     * 
     * @return bool True if the request is secure, false otherwise
     */
    public static function isSecure()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }

    /**
     * Get the client's direct IP address
     * 
     * @return string The IP address
     */
    public static function ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get the client's IP address, checking for proxy headers
     * 
     * @return string The real client IP address considering X-Forwarded-For header
     */
    public static function clientIp()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    }
}
