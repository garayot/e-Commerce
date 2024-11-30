#!/usr/bin/env php
<?php
require_once 'routes.php';
require_once 'middlewares/cache.php';
require_once 'middlewares/auth.php';
require_once 'middlewares/rate_limit.php';
require_once 'middlewares/sanitize.php';
require_once 'middlewares/cors.php';

// Include the file where logRequestResponse is defined
require_once __DIR__ . '/utils/logger.php';

// Include all controllers
require_once __DIR__ .
    '/../Product/api/Controllers/ProductCatalogController.php';
require_once __DIR__ . '/../Product/api/Controllers/SearchEngineController.php';
require_once __DIR__ .
    '/../Product/api/Controllers/SellerProductController.php';
require_once __DIR__ . '/../Product/api/Controllers/SellerSearchController.php';
require_once __DIR__ . '/../UserAuth/api/Controllers/AuthController.php';
require_once __DIR__ .
    '/../UserAuth/api/Controllers/PasswordResetController.php';
require_once __DIR__ . '/../UserAuth/api/Controllers/UserAssignController.php';
require_once __DIR__ . '/../UserAuth/api/Controllers/UserListController.php';
require_once __DIR__ . '/../UserAuth/api/Controllers/UserProfileController.php';

use Product\Api\Controllers\ProductCatalogController;
use Product\Api\Controllers\SearchEngineController;
use Product\Api\Controllers\SellerProductController;
use Product\Api\Controllers\SellerSearchController;
use UserAuth\Api\Controllers\AuthController;
use UserAuth\Api\Controllers\PasswordResetController;
use UserAuth\Api\Controllers\UserAssignController;
use UserAuth\Api\Controllers\UserListController;
use UserAuth\Api\Controllers\UserProfileController;

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestBody = file_get_contents('php://input');

// Ensure the cache directory exists
$cacheDir = __DIR__ . '/../cache';
if (!is_dir($cacheDir)) {
    if (!mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
        error_log("Failed to create cache directory: $cacheDir");
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error']);
        exit();
    }
}

// caching
$cacheFile = "$cacheDir/$requestUri.json";
if (file_exists($cacheFile)) {
    $response = json_decode(file_get_contents($cacheFile), true);
    http_response_code(200);
    echo json_encode($response);
    exit();
}

// middlewares
checkRateLimit();
sanitizeInputs();

// conditionally apply the authenticate middleware
$publicRoutes = ['/api/auth/signup', '/api/auth/signin'];

if (!in_array($requestUri, $publicRoutes)) {
    authenticate();
}

$response = ['error' => 'Endpoint not found']; // default response
$statusCode = 404; // default status code
$routeFound = false;

foreach ($router->getRoutes() as $route) {
    if (
        preg_match("#^{$route['path']}$#", $requestUri) &&
        $route['method'] === $requestMethod
    ) {
        $routeFound = true;
        list($controller, $method) = $route['action'];
        $controllerInstance = new $controller();
        $response = $controllerInstance->$method(
            json_decode($requestBody, true)
        );
        $statusCode = http_response_code();
        break;
    }
}

if (!$routeFound) {
    $response = ['error' => 'Endpoint not found'];
    $statusCode = 404;
}

logRequestResponse($response, $statusCode);
http_response_code($statusCode);
echo json_encode($response);

