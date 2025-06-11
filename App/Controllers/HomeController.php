<?php

namespace App\Controllers;

use App\Models\Book;
use Core\Request\Request;
use Core\Response\Response;
use Core\Routing\Router;

class HomeController {
    public function index() {
        dd(Book::all());
        return view('welcome');
    }

    public function store(Request $request) {
        return response()->setContent("Hi " . $request->input('name'));
    }
    
    public function test(Request $request, $param) {
        return response()->setContent("This is a test response, <b>{$param}</b>. Redirect to <a href='" . route('redirect_to_main') . "'>Home</a>");
    }

    public function redirect_to_main() {
        redirect()->route('home');
    }
}