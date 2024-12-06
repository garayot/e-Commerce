<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Include Composer's autoload file
require_once 'routes.php';
require_once 'middlewares/cache.php';
require_once 'middlewares/auth.php';
require_once 'middlewares/rate_limit.php';
require_once 'middlewares/sanitize.php';
require_once 'middlewares/cors.php';

// Include the file where the Database class is defined
require_once __DIR__ . '/utils/db.php';

use Database\Database;

// Include the file where logRequestResponse is defined
require_once __DIR__ . '/utils/logger.php';

// Include all controllers
//require_once __DIR__ .
'../../api-gateway/Product/api/Controllers/ProductCatalogController.php';
require_once __DIR__ .
    '../../api-gateway/Product/api/Controllers/SearchEngineController.php';
require_once __DIR__ .
    '../../api-gateway/Product/api/Controllers/SellerProductController.php';
require_once __DIR__ .
    '../../api-gateway/Product/api/Controllers/SellerSearchController.php';

// auth controllers
require_once __DIR__ .
    '../../api-gateway/UserAuth/api/Controllers/AuthController.php';
require_once __DIR__ .
    '../../api-gateway/UserAuth/api/Controllers/PasswordResetController.php';
require_once __DIR__ .
    '../../api-gateway/UserAuth/api/Controllers/UserAssignController.php';
require_once __DIR__ .
    '../../api-gateway/UserAuth/api/Controllers/UserListController.php';
require_once __DIR__ .
    '../../api-gateway/UserAuth/api/Controllers/UserProfileController.php';

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

$publicRoutes = [
    '/api/auth/signup',
    '/api/auth/signin',
    '/api/auth/password-reset',
];

if (!in_array($requestUri, $publicRoutes)) {
    authenticate();
}

$response = ['error' => 'Endpoint not found']; // default response
$statusCode = 404; // default status code
$routeFound = false;

$db = new Database();

$startTime = microtime(true); // Start time for logging

foreach ($router->getRoutes() as $route) {
    if (
        preg_match("#^{$route['path']}$#", $requestUri) &&
        $route['method'] === $requestMethod
    ) {
        $routeFound = true;
        list($controller, $method) = $route['action'];
        $controllerInstance = new $controller($db);
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

$endTime = microtime(true); // End time for logging
$executionTime = $endTime - $startTime; // Calculate execution time
error_log("Request to $requestUri took $executionTime seconds."); // Log execution time

logRequestResponse($response, $statusCode);
http_response_code($statusCode);
echo json_encode($response);
