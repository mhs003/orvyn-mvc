<?php

namespace Core;

class Router {
    protected $routes = [];

    // Register a GET route
    public function get($uri, $action) {
        $this->routes['GET'][$uri] = $action;
    }

    // 

    // Dispatch the request
    public function dispatch($uri, $method) {
        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];

            if (is_callable($action)) {
                // If the action is callable, call it directly
                call_user_func($action);
            } elseif (is_array($action) && count($action) === 2) {
                // If the action is a class/method array, resolve and call
                list($controller, $method) = $action;
                if (class_exists($controller) && method_exists($controller, $method)) {
                    call_user_func([new $controller, $method]);
                } else {
                    echo "Controller or method not found: $controller@$method";
                }
            } elseif (is_string($action)) {
                // If the action is in the "Controller@method" format
                list($controller, $method) = explode('@', $action);
                $controller = "App\\Controllers\\$controller";
                if (class_exists($controller) && method_exists($controller, $method)) {
                    call_user_func([new $controller, $method]);
                } else {
                    echo "Controller or method not found: $controller@$method";
                }
            } else {
                echo "Invalid route action.";
            }
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
        echo "<br><pre>";
        die([var_dump($uri), var_dump($this->routes)]);
    }
}
