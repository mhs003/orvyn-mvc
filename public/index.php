<?php

// Load autloader
require_once __DIR__ . '/../dumper/autoload.php';

// Load the app bootstrap file
require_once __DIR__ . '/../bootstrap/app.php';

// Defines
define('DEBUG', true);


// Dispatch the request
$app->handle(
    $_SERVER['REQUEST_URI'], 
    $_SERVER['REQUEST_METHOD']
);
