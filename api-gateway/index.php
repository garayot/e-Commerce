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
foreach ($routes as $route => $config) {
    if (
        preg_match("#^$route$#", $requestUri) &&
        $config['method'] === $requestMethod
    ) {
        $routeFound = true;

        ob_start();
        require_once $config['service'];
        $response = ob_get_contents();
        $statusCode = http_response_code();
        ob_end_clean();

        break;
    }
}

logRequestResponse($response, $statusCode);
http_response_code($statusCode);
echo $response;
