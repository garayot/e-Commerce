<?php

class Router
{
    private $routes = [];

    public function addRoute($method, $path, $action)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action,
        ];
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
