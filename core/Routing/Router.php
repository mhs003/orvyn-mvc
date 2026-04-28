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
        $basePath = realpath(__DIR__ . '/../../app/Controllers');
        $baseNamespace = 'App\\Controllers';

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());

            $class = $baseNamespace . '\\' . str_replace(
                [DIRECTORY_SEPARATOR, '.php'],
                ['\\', ''],
                $relativePath
            );

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

            if ($route) $path = $this->norm($base . $route->newInstance()->path);
            if ($default) $path = $base;

            if ($path !== null) {
                $this->routes[] = [
                    'method' => $http,
                    'path' => $path,
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