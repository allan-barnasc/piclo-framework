<?php

use Structure\Classes\Router;

$router = new Router();

$router->get('/', function () {
    echo phpinfo();
});

$router->dispatch();