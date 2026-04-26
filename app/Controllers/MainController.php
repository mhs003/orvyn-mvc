<?php

namespace App\Controllers;

use Attributes\Method;
use Core\Http\Request;
use Attributes\Route;
use Attributes\DefaultRoute;

#[Route('/')]
class MainController
{
    #[DefaultRoute]
    public function index(Request $request)
    {
        return view("home", ['name' => '<u>World</u>']);
    }

    #[Method('POST')]
    #[Route('/store')]
    public function store() {
        return response()->json(['result' => 'ok']);
    }
}