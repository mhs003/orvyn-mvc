<?php

if (!function_exists('render_error_page')) {
    function render_error_page($code, $message = null)
    {
        return \Core\ErrorHandlers\HttpError::render($code, $message);
    }
}

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        \Core\ErrorHandlers\DebugHelper::process_dd(...$vars);
    }
}

if (!function_exists('ddb')) {
    function ddb(...$vars)
    {
        \Core\ErrorHandlers\DebugHelper::process_ddb(...$vars);
    }
}


if (!function_exists('dump')) {
    function dump(...$vars)
    {
        \Core\ErrorHandlers\DebugHelper::process_dump($vars);
    }
}


if (!function_exists('orvyn_log')) {
    function orvyn_log($var, $logFile = 'orvyn_debug.log')
    {
        \Core\ErrorHandlers\DebugHelper::log($var, $logFile);
    }
}


if (!function_exists('final_debug_backtrace')) {
    function final_debug_backtrace($traces, $fname = 'dd')
    {
        $tracer = null;
        foreach ($traces as $trace) {
            if (isset($trace['function']) && $trace['function'] === $fname) {
                $tracer = $trace;
                break;
            }
        }
        return $tracer;
    }
}
