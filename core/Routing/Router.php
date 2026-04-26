<?php

namespace Core\Routing;

use Core\Container;
use Core\Http\Request;
use Core\Http\Response;
use ReflectionClass;
use ReflectionMethod;
use Attributes\Route;
use Attributes\DefaultRoute;
use Attributes\Method;

class Router
{
    private array $routes = [];
    protected array $allowedMethods = ['POST', 'GET', 'PUT', 'DELETE'];

    public function __construct(private Container $container) {}

    public function loadControllers()
    {
        $files = glob(__DIR__ . '/../../app/Controllers/*.php');

        foreach ($files as $file) {
            $class = 'App\\Controllers\\' . basename($file, '.php');

            if (class_exists($class)) {
                $this->register($class);
            }
        }
    }

    private function register(string $class)
    {
        $ref = new ReflectionClass($class);

        $base = '/';

        foreach ($ref->getAttributes(Route::class) as $attr) {
            $base = $attr->newInstance()->path;
        }

        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

            $http = 'GET';

            $methodAttr = $method->getAttributes(Method::class)[0] ?? null;
            $methodStr = $methodAttr?->newInstance()?->method ?? null;
            if($methodAttr && $methodStr && \in_array(strtoupper($methodStr), $this->allowedMethods)) $http = strtoupper($methodStr);

            $route = $method->getAttributes(Route::class)[0] ?? null;
            $default = $method->getAttributes(DefaultRoute::class)[0] ?? null;

            $path = null;

            if ($route) $path = $route->newInstance()->path;
            if ($default) $path = '';

            if ($path !== null) {
                $this->routes[] = [
                    'method' => $http,
                    'path' => $this->norm($base . $path),
                    'class' => $class,
                    'action' => $method->getName()
                ];
            }
        }
    }

    private function norm($p)
    {
        return '/' . trim($p, '/');
    }

    public function dispatch(Request $request): Response
    {
        $uri = $this->norm(parse_url($request->uri(), PHP_URL_PATH));

        foreach ($this->routes as $r) {

            if ($r['method'] !== $request->method()) continue;

            $pattern = preg_replace('#:([\w]+)#', '([\w-]+)', $r['path']);
            $pattern = "#^$pattern$#";

            if (preg_match($pattern, $uri, $m)) {
                array_shift($m);

                $controller = $this->container->make($r['class']);
                $method = $r['action'];

                $result = $controller->$method($request, ...$m);

                return $result instanceof Response
                    ? $result
                    : Response::make($result);
            }
        }

        return Response::make("404 Not Found", 404);
    }
}