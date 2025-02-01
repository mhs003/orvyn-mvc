<?php

use App\Controllers\HomeController;
use App\Middleware\TestMiddleware;

/**
 * Web Routes
 * 
 * Define all web routes for the application
 */

$app->router/* ->middleware(TestMiddleware::class) */->get('/', [HomeController::class, 'index'])->name('home');
$app->router->get('/test/{param}', [HomeController::class, 'test'])->name('test');
$app->router->post('/store', [HomeController::class, 'store'])->name('store');
$app->router->get('/redirect_to_main', [HomeController::class, 'redirect_to_main'])->name('redirect_to_main');