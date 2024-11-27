<?php
require_once 'routes.php';
require_once 'middlewares/cache.php';
require_once 'middlewares/auth.php';
require_once 'middlewares/rate_limit.php';
require_once 'middlewares/sanitize.php';

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestBody = file_get_contents('php://input');

// caching
checkCache();

// middlewares
checkRateLimit();
sanitizeInputs();
authenticate();

$response = ['error' => 'Endpoint not found']; // default response
$statusCode = 404; // default status code
$routeFound = false;

foreach ($router->getRoutes() as $route) {
    if (
        preg_match("#^{$route['path']}$#", $requestUri) &&
        $route['method'] === $requestMethod
    ) {
        $routeFound = true;
        list($controller, $method) = explode('@', $route['action']);
        $controllerInstance = new $controller();
        $response = $controllerInstance->$method(
            json_decode($requestBody, true)
        );
        $statusCode = http_response_code();
        break;
    }
}

logRequestResponse($response, $statusCode);
http_response_code($statusCode);
echo json_encode($response);
