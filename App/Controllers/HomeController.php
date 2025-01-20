<?php

namespace App\Controllers;

use Core\Request\Request;
use Core\Response\Response;

/**
 * Home Controller
 * 
 * Handles the main page and form submission functionality
 * 
 * @package App\Controllers
 */
class HomeController {
    /**
     * Display the welcome page with a form
     * 
     * @return void
     */
    public function index() {
        Response::setContent("Welcome to Unown! <br><br><form method='post' action='/store'><input name='name' placeholder='Enter your name' /> <button type='submit'>Submit</button></form>")->send();
    }

    /**
     * Handle the form submission
     * 
     * @param Request $request The current request instance
     * @return void
     */
    public function store(Request $request) {
        Response::setContent("Hi " . $request->input('name'))->send();
    }
}