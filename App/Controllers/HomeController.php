<?php

namespace App\Controllers;

use Core\Request\Request;

class HomeController {
    public function index() {
        echo "Welcome to Unown!";
        echo "<br><br>";
        echo "<form method='post' action='/store'><input name='name' placeholder='Enter your name' /> <button type='submit'>Submit</button></form>";
    }

    public function store(Request $request) {
        echo "Hi " . $request->input('name');
    }
}
