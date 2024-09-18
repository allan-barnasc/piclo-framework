<?php

use Structure\Classes\Router;

$router = new Router();

$router->get('/', 'HomeController@index');

$router->load();