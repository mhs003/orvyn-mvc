<?php

use App\Controllers\HomeController;
use App\Middleware\TestMiddleware;

/**
 * Web Routes
 * 
 * Define all web routes for the application
 */

$app->router->middleware(TestMiddleware::class)->get('/', [HomeController::class, 'index']);
$app->router->get('/test', [HomeController::class, 'indexs']);
$app->router->post('/store', [HomeController::class, 'store']);
$app->router->put('/put', [HomeController::class, 'store']);
$app->router->delete('/delete', [HomeController::class, 'store']);