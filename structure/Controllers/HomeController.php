<?php

namespace Structure\Controllers;

use Structure\Classes\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('index');
    }
}