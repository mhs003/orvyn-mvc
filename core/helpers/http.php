<?php

use Core\Http\Request;
use Core\Http\Response;

if (!function_exists('request')) {
    function request(): Request
    {
        return app(Request::class);
    }
}

if (!function_exists('response')) {
    function response($content = '', $status = 200): Response
    {
        return new Response($content, $status);
    }
}