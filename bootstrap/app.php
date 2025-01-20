<?php

use Core\Routing\Router;

/**
 * Application Bootstrap
 * 
 * Initializes the application and its core components
 */
$app = new class {
    /**
     * The router instance
     * 
     * @var Router
     */
    public $router;

    /**
     * Initialize the application
     */
    public function __construct() {
        $this->router = new Router();
    }

    /**
     * Handle the incoming request
     * 
     * @param string $uri Request URI
     * @param string $method HTTP method
     * @return void
     */
    public function handle($uri, $method) {
        $this->router->dispatch($uri, $method);
    }
};

require_once __DIR__ . '/../routes/web.php';