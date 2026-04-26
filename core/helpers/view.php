<?php

use Core\View;

if (!function_exists('view')) {
    function view(string $file, array $data = [])
    {
        $view = app(View::class);

        return $view->render(
            __DIR__ . '/../../app/Views/' . $file . '.php',
            $data
        );
    }
}