<?php

use Core\Routing\Router;
use Core\Templating\Compiler;
use Core\Templating\Engine;

/**
 * Application Bootstrap
 * 
 * Initializes the application and its core components
 */

//  Register custom directives
require_once __DIR__ . '/../App/Views/Directives.php';

// Initialize the view compiler
$compiler = new Compiler(__DIR__ . '/../resource/views', __DIR__ . '/../resource/compiled-views');
// Register the view engine
$viewEngine = new Engine($compiler);


// Initialize the application
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

// Load the application routes
require_once __DIR__ . '/../routes/web.php';