<?php

use Core\Router;


// Init the application
$app = new class {
    public $router;

    public function __construct() {
        $this->router = new Router();
    }

    public function handle($uri, $method) {
        $this->router->dispatch($uri, $method);
    }
};

require_once __DIR__ . '/../routes/web.php';