<?php 

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request\Request;

/**
 * Test Middleware
 * 
 * Example middleware implementation for demonstration purposes
 * 
 * @package App\Middleware
 */
class TestMiddleware extends Middleware {
    /**
     * Handle the incoming request
     * 
     * @param Request $request The current request instance
     * @return void
     */
    public function handle(Request $request) {
        echo "This content is printed from test middleware";
        echo "<hr>";
        $this->next();
    }
}
