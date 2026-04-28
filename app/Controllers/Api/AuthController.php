<?php

namespace App\Controllers\Api;

use Attributes\Route;
use Attributes\DefaultRoute;
use Core\Http\Request;
use Core\Http\Response;

#[Route('/Api/Auth')]
class AuthController
{
    #[DefaultRoute]
    public function index(Request $request)
    {
        return response()->json(['auth' => false]);
    }
}