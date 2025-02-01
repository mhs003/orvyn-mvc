<?php

namespace App\Controllers;

use Core\Request\Request;
use Core\Response\Response;
use Core\Routing\Router;

class HomeController {
    public function index() {
        return response()->setContent("Welcome to Orvyn! <br><br><form method='post' action='" . route('store') . "'><input name='name' placeholder='Enter your name' /> <button type='submit'>Submit</button></form><br>To <a href='" . route('test', ['param' => 'test']) . "'>test page</a>");
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