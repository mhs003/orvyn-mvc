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
    protected static $routes = [
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
    protected static $currentRoute = null;

    /**
     * Middleware stack for the next route registration
     * 
     * @var array
     */
    protected static $currentMiddleware = [];

    /**
     * Collection of routes with their assigned names
     * 
     * @var array<string, array>
     */
    protected static $namedRoutes = [];

    /**
     * Register a new GET route
     * 
     * @param string $uri The URI pattern to match
     * @param mixed $action The route handler (callable|array|string)
     * @return $this
     */
    public function get($uri, $action) {
        self::$currentRoute = ['method' => 'GET', 'uri' => $uri];
        self::$routes['GET'][$uri] = ['action' => $action, 'middleware' => self::$currentMiddleware];
        self::$currentMiddleware = [];
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
        self::$currentRoute = ['method' => 'POST', 'uri' => $uri];
        self::$routes['POST'][$uri] = ['action' => $action, 'middleware' => self::$currentMiddleware];
        self::$currentMiddleware = [];
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
        self::$currentRoute = ['method' => 'PUT', 'uri' => $uri];
        self::$routes['PUT'][$uri] = ['action' => $action, 'middleware' => self::$currentMiddleware];
        self::$currentMiddleware = [];
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
        self::$currentRoute = ['method' => 'DELETE', 'uri' => $uri];
        self::$routes['DELETE'][$uri] = ['action' => $action, 'middleware' => self::$currentMiddleware];
        self::$currentMiddleware = [];
        return $this;
    }

    /**
     * Apply middleware to the current route or store for next route registration
     * 
     * @param array|string $middleware Single middleware class or array of middleware classes
     * @return $this
     */
    public function middleware($middleware = []) {
        if (self::$currentRoute) {
            $method = self::$currentRoute['method'];
            $uri = self::$currentRoute['uri'];
            self::$routes[$method][$uri]['middleware'] = array_merge(
                self::$routes[$method][$uri]['middleware'],
                is_array($middleware) ? $middleware : [$middleware]
            );
        } else {
            self::$currentMiddleware = is_array($middleware) ? $middleware : [$middleware];
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
        if (self::$currentRoute) {
            self::$namedRoutes[$name] = self::$currentRoute;
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
    
        foreach (self::$routes[$method] as $routeUri => $route) {
            $pattern = preg_replace('/\{(\w+)\?\}/', '(?<$1>[^/]+)?', $routeUri); // Optional parameters
            $pattern = preg_replace('/\{(\w+)\}/', '(?<$1>[^/]+)', $pattern); // Required parameters
            $pattern = "#^{$pattern}$#";
    
            if (preg_match($pattern, $uri, $matches)) {
                $action = $route['action'];
                $middleware = $route['middleware'];
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Extract named params
    
                $request = new Request();
                $this->buildMiddlewarePipeline($middleware, $request, function () use ($action, $request, $params) {
                    $response = $this->callAction($action, $request, $params);
                    if ($response instanceof Response) {
                        $response->send();
                    } else {
                        echo $response;
                    }
                });
                return;
            }
        }
    
        if ($this->methodExistsForRoute($uri)) {
            render_error_page(405);
        } else {
            render_error_page(404);
        }
    }
    

    /**
     * Get all registered routes
     * 
     * @return array
     */
    public static function getRoutes() {
        return self::$routes;
    }


    /**
     * Get the URI for a named route
     * 
     * @param string $name The name of the route
     * @return string|null
     */
    public static function getRoute($name) {
        if (isset(self::$namedRoutes[$name])) {
            return self::$namedRoutes[$name]['uri'];
        }
    }

    /**
     * Check if the URI exists for any HTTP method
     * 
     * @param string $uri The URI to check
     * @return bool
     */
    private function methodExistsForRoute($uri) {
        foreach (self::$routes as $method => $routes) {
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
     * @param array $params Named parameters extracted from the URI
     * @return mixed
     */
    private function callAction($action, $request, $params = []) {
        if (is_callable($action)) {
            return call_user_func($action, $request, ...array_values($params));
        } elseif (is_array($action) && count($action) === 2) {
            [$controller, $method] = $action;
            if (class_exists($controller) && method_exists($controller, $method)) {
                return call_user_func([new $controller, $method], $request, ...array_values($params));
            }
        } elseif (is_string($action)) {
            [$controller, $method] = explode('@', $action);
            $controller = "App\\Controllers\\$controller";
            if (class_exists($controller) && method_exists($controller, $method)) {
                return call_user_func([new $controller, $method], $request, ...array_values($params));
            }
        }
        render_error_page(500);        
    }
    
}