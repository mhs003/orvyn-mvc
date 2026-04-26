<?php

namespace Core;

use Core\Routing\Router;
use Core\Http\Request;

class App
{
    public static Container $instance;
    public Container $container;
    public Router $router;

    public function __construct()
    {
        $this->container = new Container();
        self::$instance = $this->container;
        $this->router = new Router($this->container);
    }

    public function run()
    {
        $this->router->loadControllers();

        $request = Request::capture();

        $this->container->instance(Request::class, $request);

        $response = $this->router->dispatch($request);

        $response->send();
    }
}