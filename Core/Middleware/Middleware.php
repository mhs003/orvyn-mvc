<?php

namespace Core\Middleware;

use Core\Request\Request;

/**
 * Abstract Middleware Class
 * 
 * Base class for all middleware implementations providing
 * pipeline functionality
 * 
 * @package Core\Middleware
 */
abstract class Middleware {
    /**
     * The next middleware or controller action in the chain
     * 
     * @var callable
     */
    protected $next;

    /**
     * Set the next step in the pipeline
     * 
     * @param callable $next The next middleware or controller action
     * @return void
     */
    public function setNext($next) {
        $this->next = $next;
    }

    /**
     * Execute the next middleware or controller action
     * 
     * @return void
     */
    public function next() {
        call_user_func($this->next);
    }

    /**
     * Handle the incoming request
     * 
     * @param Request $request The current request instance
     * @return void
     */
    abstract public function handle(Request $request);
}