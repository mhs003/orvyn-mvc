<?php

use App\Controllers\HomeController;

$app->router->get('/', [HomeController::class, 'index']);
$app->router->get('/about', [HomeController::class, 'about']);
