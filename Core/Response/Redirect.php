<?php

namespace Core\Response;

use Core\Routing\Router;

/**
 * Redirect Class
 * 
 * Handles HTTP redirects with flash data and named routes
 * 
 * @package Core\Response
 */
class Redirect
{

    /**
     * The URL to redirect to
     * 
     * @var string
     */
    protected static $url;

    /**
     * The HTTP status code for the redirect
     * 
     * @var int
     */
    protected static $statusCode;

    /**
     * Flash data to be stored in the session
     * 
     * @var array
     */
    protected static $sessionData = [];

    
    /**
     * Redirect to a given URL with an optional status code
     * 
     * @param string $url The URL to redirect to
     * @param int $statusCode The HTTP status code for the redirect
     * @return void
     */
    public static function to($url, $statusCode = 302)
    {
        self::$url = $url;
        self::$statusCode = $statusCode;
        self::send();
    }

    /**
     * Send the redirect response
     * 
     * @return void
     */
    public static function send()
    {
        http_response_code(self::$statusCode);
        header("Location: " . self::$url);
        exit;
    }

    
    /**
     * Add flash data to the session
     * 
     * @param string $key The key to store the data under
     * @param mixed $value The data to store
     * @return void
     */
    public static function with($key, $value)
    {
        self::$sessionData[$key] = $value;
        self::to(self::$url); // Redirect to the same URL after adding flash data
    }

    
    /**
     * Redirect with an error message
     * 
     * @param string $message The error message to display
     * @return void
     */
    public static function withError($message)
    {
        self::$sessionData['error'] = $message;
        self::to(self::$url);
    }

    
    /**
     * Redirect with a success message
     * 
     * @param string $message The success message to display
     * @return void
     */
    public static function withSuccess($message)
    {
        self::$sessionData['success'] = $message;
        self::to(self::$url);
    }

    
    /**
     * Redirect with input data
     * 
     * @param array $input The input data to store
     * @return void
     */
    public static function withInput($input)
    {
        self::$sessionData['input'] = $input;
        self::to(self::$url);
    }

    
    /**
     * Redirect to a named route
     * 
     * @param string $name The name of the route
     * @param array $params The parameters to replace in the route
     * @return void
     */
    public static function route($name, $params = [])
    {
        $uri = Router::getRoute($name);

        if ($uri) {
            // Replace any parameters in the route
            foreach ($params as $key => $value) {
                $uri = str_replace("{{$key}}", $value, $uri);
            }
            self::to($uri);
        }
    }
}
