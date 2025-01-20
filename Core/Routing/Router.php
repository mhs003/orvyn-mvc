<?php

namespace Core\Routing;

use Core\Request\Request;
use Core\Response\Response;

/**
 * Router Class
 * 
 * Handles HTTP routing with middleware support and named routes.
 * Provides a fluent interface for route definition and management.
 * 
 * @package Core\Routing
 */
class Router {
    /**
     * Collection of registered routes grouped by HTTP methods
     * 
     * @var array<string, array>
     */
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * Currently processed route for method chaining
     * 
     * @var array|null
     */
    protected $currentRoute = null;

    /**
     * Middleware stack for the next route registration
     * 
     * @var array
     */
    protected $currentMiddleware = [];

    /**
     * Collection of routes with their assigned names
     * 
     * @var array<string, array>
     */
    protected $namedRoutes = [];

    /**
     * Register a new GET route
     * 
     * @param string $uri The URI pattern to match
     * @param mixed $action The route handler (callable|array|string)
     * @return $this
     */
    public function get($uri, $action) {
        $this->currentRoute = ['method' => 'GET', 'uri' => $uri];
        $this->routes['GET'][$uri] = ['action' => $action, 'middleware' => $this->currentMiddleware];
        $this->currentMiddleware = [];
        return $this;
    }

    /**
     * Register a new POST route
     * 
     * @param string $uri The URI pattern to match
     * @param mixed $action The route handler (callable|array|string)
     * @return $this
     */
    public function post($uri, $action) {
        $this->currentRoute = ['method' => 'POST', 'uri' => $uri];
        $this->routes['POST'][$uri] = ['action' => $action, 'middleware' => $this->currentMiddleware];
        $this->currentMiddleware = [];
        return $this;
    }

    /**
     * Register a new PUT route
     * 
     * @param string $uri The URI pattern to match
     * @param mixed $action The route handler (callable|array|string)
     * @return $this
     */
    public function put($uri, $action) {
        $this->currentRoute = ['method' => 'PUT', 'uri' => $uri];
        $this->routes['PUT'][$uri] = ['action' => $action, 'middleware' => $this->currentMiddleware];
        $this->currentMiddleware = [];
        return $this;
    }

    /**
     * Register a new DELETE route
     * 
     * @param string $uri The URI pattern to match
     * @param mixed $action The route handler (callable|array|string)
     * @return $this
     */
    public function delete($uri, $action) {
        $this->currentRoute = ['method' => 'DELETE', 'uri' => $uri];
        $this->routes['DELETE'][$uri] = ['action' => $action, 'middleware' => $this->currentMiddleware];
        $this->currentMiddleware = [];
        return $this;
    }

    /**
     * Apply middleware to the current route or store for next route registration
     * 
     * @param array|string $middleware Single middleware class or array of middleware classes
     * @return $this
     */
    public function middleware($middleware = []) {
        if ($this->currentRoute) {
            $method = $this->currentRoute['method'];
            $uri = $this->currentRoute['uri'];
            $this->routes[$method][$uri]['middleware'] = array_merge(
                $this->routes[$method][$uri]['middleware'],
                is_array($middleware) ? $middleware : [$middleware]
            );
        } else {
            $this->currentMiddleware = is_array($middleware) ? $middleware : [$middleware];
        }
        return $this;
    }

    /**
     * Assign a name to the current route
     * 
     * @param string $name The name to assign to the route
     * @return $this
     */
    public function name($name) {
        if ($this->currentRoute) {
            $this->namedRoutes[$name] = $this->currentRoute;
        }
        return $this;
    }

    /**
     * Dispatch the request to the appropriate route handler
     * 
     * @param string $uri The request URI
     * @param string $method The HTTP method
     * @return void
     */
    public function dispatch($uri, $method) {
        $uri = parse_url($uri, PHP_URL_PATH);

        if (isset($this->routes[$method][$uri])) {
            $route = $this->routes[$method][$uri];
            $action = $route['action'];
            $middleware = $route['middleware'];

            $request = new Request();
            $this->buildMiddlewarePipeline($middleware, $request, $action);
        } else {
            if ($this->methodExistsForRoute($uri)) {
                http_response_code(405);
                echo "405 Method Not Allowed";
            } else {
                http_response_code(404);
                echo "404 Not Found";
            }
        }
    }

    /**
     * Check if the URI exists for any HTTP method
     * 
     * @param string $uri The URI to check
     * @return bool
     */
    private function methodExistsForRoute($uri) {
        foreach ($this->routes as $method => $routes) {
            if (isset($routes[$uri])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build and execute the middleware pipeline
     * 
     * @param array $middleware Array of middleware classes
     * @param Request $request The current request instance
     * @param mixed $action The final route handler
     * @return void
     */
    private function buildMiddlewarePipeline(array $middleware, Request $request, $action) {
        $next = function () use ($request, $action) {
            $response = $this->callAction($action, $request);

            if ($response instanceof Response) {
                $response->send();
            } else {
                echo $response;
            }
        };

        foreach (array_reverse($middleware) as $mw) {
            $middlewareInstance = new $mw;
            $middlewareInstance->setNext($next);
            $next = function () use ($middlewareInstance, $request) {
                $middlewareInstance->handle($request);
            };
        }

        $next();
    }

    /**
     * Execute the route action
     * 
     * @param mixed $action The route handler (callable|array|string)
     * @param Request $request The current request instance
     * @return mixed
     */
    private function callAction($action, $request) {
        if (is_callable($action)) {
            return call_user_func($action, $request);
        } elseif (is_array($action) && count($action) === 2) {
            [$controller, $method] = $action;
            if (class_exists($controller) && method_exists($controller, $method)) {
                return call_user_func([new $controller, $method], $request);
            } else {
                return "Controller or method not found: $controller@$method";
            }
        } elseif (is_string($action)) {
            [$controller, $method] = explode('@', $action);
            $controller = "App\\Controllers\\$controller";
            if (class_exists($controller) && method_exists($controller, $method)) {
                return call_user_func([new $controller, $method], $request);
            } else {
                return "Controller or method not found: $controller@$method";
            }
        } else {
            return "Invalid route action.";
        }
    }
}