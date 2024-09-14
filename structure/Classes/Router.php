<?php

namespace Structure\Classes;

use BadMethodCallException;
use Exception;
use InvalidArgumentException;

class Router
{
    /** Defined HTTP Verbs */
    private array $methods = ['get', 'post', 'put', 'patch', 'delete'];

    protected array $routes = [];

    /**
     * Magic method to call HTTP Verbs dynamically
     * 
     * @param string $method The HTTP Verb name
     * @param mixed $args The arguments to pass by parameters required
     * 
     * @return void
     */
    public function __call(string $method, mixed $args): void
    {
        if (!in_array($method, array_values($this->methods)))
			throw new BadMethodCallException();

        $this->add($args[0], $args[1], strtoupper($method));
    }

    /**
     * Add a route in $routes variable
     * 
     * @param string $route The route path that need
     * @param string $controller The controller link that route is connect
     * @param string $action The action method to access in controller
     * @param string $method The HTTP method GET|POST|PUT|PATCH|DELETE
     * 
     * @return void
     */
    private function add(string $route, mixed $params, string $method): void
    {
        if (!is_callable($params)) 
        {
            $controller = '';
            $action = '';

            if (is_string($params)) 
            {
                $explode = explode('@', $params);

                $controller = $explode[0];
                $action = $explode[1];

            } 
            else if (is_array($params)) 
            {
                $controller = $params[0];
                $action = $params[1];

            } 
            else 
            {
                throw new InvalidArgumentException('The param passed is invalid');
                
            }

            $this->routes[$method][$route] = ['controller' => $controller, 'action' => $action];

        } 
        else 
        {
            $this->routes[$method][$route] = ['callback' => $params];
        }
    }

    /**
     * The method that execute the defined $routes array
     * variables actions
     * 
     * @return void
     */
    public function dispatch(): void
    {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $method =  $_SERVER['REQUEST_METHOD'];

        if (array_key_exists($uri, $this->routes[$method])) 
        {
            if (!isset($this->routes[$method][$uri]['callback']))
            {
                $controller = "Structure\\Controllers\\" . $this->routes[$method][$uri]['controller'];
                $action = $this->routes[$method][$uri]['action'];

                $controller = new $controller();
                $controller->$action();

            }
            else
            {
                call_user_func($this->routes[$method][$uri]['callback']);
            }

        } 
        else 
        {
            throw new Exception("No route found for URI: $uri");

        }
    }
}