<?php 

namespace Structure\Classes;

class Controller
{
    /**
     * Rendering a view in controller
     * 
     * @param string $path The view path
     * @param array $data The values that can be passed in view
     * 
     * @return void
     */
    protected function view(string $path, array $data = []): void
    {
        $view = new View($path, $data);

        $view->mount();
    }
}