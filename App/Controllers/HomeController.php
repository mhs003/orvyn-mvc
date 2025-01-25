<?php

namespace App\Controllers;

use Core\Request\Request;
use Core\Response\Response;
use Core\Routing\Router;

/**
 * Home Controller
 * 
 * Handles the main page and form submission functionality
 * 
 * @package App\Controllers
 */
class HomeController {
    public function index() {
        Response::setContent("Welcome to Unown! <br><br><form method='post' action='" . route('store') . "'><input name='name' placeholder='Enter your name' /> <button type='submit'>Submit</button></form><br>To <a href='" . route('test') . "'>test page</a>")->send();
    }

    public function store(Request $request) {
        Response::setContent("Hi " . $request->input('name'))->send();
    }
    
    public function test() {
        return response()->setContent("This is a test response. Redirect to <a href='" . route('redirect_to_main') . "'>Home</a>")->send();
    }

    public function redirect_to_main() {
        return redirect()->route('home');
    }
}