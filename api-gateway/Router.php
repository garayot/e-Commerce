<?php

class Router
{
    private $routes = [];

    public function __construct($db)
    {
        $this->db = $db;
    }

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

    public function dispatch($requestUri, $requestMethod, $requestBody)
    {
        foreach ($this->getRoutes() as $route) {
            if (
                preg_match("#^{$route['path']}$#", $requestUri) &&
                $route['method'] === $requestMethod
            ) {
                list($controller, $method) = $route['action'];
                $controllerInstance = new $controller($this->db);
                return $controllerInstance->$method(
                    json_decode($requestBody, true)
                );
            }
        }
        return ['error' => 'Endpoint not found'];
    }
}
