<?php 

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request\Request;

class TestMiddleware extends Middleware {
    public function handle(Request $request) {
        echo "This content is printed from test middleware";
        echo "<hr>";
        $this->next();
    }
}