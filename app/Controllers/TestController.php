<?php

namespace App\Controllers;

use Attributes\DefaultRoute;
use Core\Http\Request;
use Core\Http\Response;
use Attributes\Route;

#[Route('/Test')]
class TestController
{
    #[DefaultRoute]
    public function index(Request $request)
    {
        return Response::make("Hello From TestController");
    }
}