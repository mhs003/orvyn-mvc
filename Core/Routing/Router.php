<?php

namespace Core\Routing;

use Core\Request\Request;

class Router {
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    // Register routes for different HTTP methods
    public function get($uri, $action, $middleware = []) {
        $this->routes['GET'][$uri] = ['action' => $action, 'middleware' => $middleware];
    }

    public function post($uri, $action, $middleware = []) {
        $this->routes['POST'][$uri] = ['action' => $action, 'middleware' => $middleware];
    }

    public function put($uri, $action, $middleware = []) {
        $this->routes['PUT'][$uri] = ['action' => $action, 'middleware' => $middleware];
    }

    public function delete($uri, $action, $middleware = []) {
        $this->routes['DELETE'][$uri] = ['action' => $action, 'middleware' => $middleware];
    }

    // Dispatch the request
    public function dispatch($uri, $method) {
        // Check for _method in POST|GET request to simulate PUT/DELETE
        if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) || ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['_method']))) {
            $method = $_SERVER['REQUEST_METHOD'] === 'POST' ? strtoupper($_POST['_method']) : strtoupper($_GET['_method']);
            if (in_array($method, ['PUT', 'DELETE'])) {
                $_SERVER['REQUEST_METHOD'] = $method;  // Change the request method to PUT or DELETE
            }
        }

        // Strip query string from the URI
        $uri = parse_url($uri, PHP_URL_PATH);

        // Check if the route exists for the given method
        if (isset($this->routes[$method][$uri])) {
            $route = $this->routes[$method][$uri];
            $action = $route['action'];
            $middleware = $route['middleware'];

            $request = new Request();

            // Build the middleware pipeline
            $this->buildMiddlewarePipeline($middleware, $request, $action);

        } else {
            // Check if the URI exists but with the wrong method
            if ($this->methodExistsForRoute($uri)) {
                http_response_code(405); // Method Not Allowed
                echo "405 Method Not Allowed";
            } else {
                http_response_code(404); // Not Found
                echo "404 Not Found";
            }
        }
    }

    // Helper method to check if the URI exists with other methods
    private function methodExistsForRoute($uri) {
        foreach ($this->routes as $method => $routes) {
            if (isset($routes[$uri])) {
                return true;
            }
        }
        return false;
    }

    // Build the middleware pipeline
    private function buildMiddlewarePipeline(array $middleware, Request $request, $action) {
        $next = function () use ($request, $action) {
            // If no middleware, call the controller action
            if (is_callable($action)) {
                call_user_func($action, $request);
            } elseif (is_array($action) && count($action) === 2) {
                // If the action is a class/method array, resolve and call
                [$controller, $method] = $action;
                if (class_exists($controller) && method_exists($controller, $method)) {
                    call_user_func([new $controller, $method], $request);
                } else {
                    echo "Controller or method not found: $controller@$method";
                }
            } elseif (is_string($action)) {
                // If the action is in the "Controller@method" format
                [$controller, $method] = explode('@', $action);
                $controller = "App\\Controllers\\$controller";
                if (class_exists($controller) && method_exists($controller, $method)) {
                    call_user_func([new $controller, $method], $request);
                } else {
                    echo "Controller or method not found: $controller@$method";
                }
            } else {
                echo "Invalid route action.";
            }
        };

        // Build the pipeline, adding each middleware to the next step
        foreach (array_reverse($middleware) as $mw) {
            $middlewareInstance = new $mw;
            $middlewareInstance->setNext($next);
            $next = function () use ($middlewareInstance, $request) {
                $middlewareInstance->handle($request);
            };
        }

        // Start the middleware pipeline
        $next();
    }
}
