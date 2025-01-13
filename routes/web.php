<?php

use App\Controllers\HomeController;
use App\Middleware\TestMiddleware;

$app->router->get('/', [HomeController::class, 'index'], [TestMiddleware::class]);
$app->router->post('/store', [HomeController::class, 'store']);
$app->router->put('/put', [HomeController::class, 'store']);
$app->router->delete('/delete', [HomeController::class, 'store']);