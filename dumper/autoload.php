<?php
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    include_once __DIR__ . '/../' . $class . '.php';
});

require_once __DIR__ . '/../Core/helpers.php';