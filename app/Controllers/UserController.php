<?php

namespace App\Controllers;

use Core\Http\Request;
use Core\Http\Response;
use Attributes\Route;

#[Route('/user')]
class UserController
{
    #[Route('/:id')]
    public function show(Request $request, $id)
    {
        return Response::make("User ID: " . $id);
    }
}