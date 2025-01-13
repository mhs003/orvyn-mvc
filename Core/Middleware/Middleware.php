<?php

namespace Core\Middleware;

use Core\Request\Request;

abstract class Middleware {
    // The next middleware or controller action in the chain
    protected $next;

    // Set the next step in the pipeline
    public function setNext($next) {
        $this->next = $next;
    }

    public function next() {
        call_user_func($this->next);
    }

    // Abstract handle function that must be implemented by subclasses
    abstract public function handle(Request $request);
}