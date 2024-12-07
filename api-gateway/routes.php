<?php
require_once 'Router.php';
require_once __DIR__ . '/utils/db.php';

use Database\Database;
use Api\Controllers\ProductCatalogController;
use Api\Controllers\SearchEngineController;
use Api\Controllers\SellerProductController;
use Api\Controllers\SellerSearchController;
use Api\Controllers\AuthController;
use Api\Controllers\PasswordResetController;
use Api\Controllers\UserAssignController;
use Api\Controllers\UserListController;
use Api\Controllers\UserProfileController;

$db = new Database();
$router = new Router($db);

// Root URL
$router->addRoute('GET', '/', function () {
    return ['message' => 'Welcome to the API Gateway'];
});

// ===================== products =========================
$router->addRoute('GET', '/api/products', [
    ProductCatalogController::class,
    'getAllProducts',
]);
$router->addRoute('GET', '/api/products/{id}', [
    ProductCatalogController::class,
    'getProductDetails',
]);

$router->addRoute('POST', '/api/products', [
    ProductCatalogController::class,
    'createProduct',
]);
$router->addRoute('PUT', '/api/products/{id}', [
    ProductCatalogController::class,
    'updateProduct',
]);
$router->addRoute('DELETE', '/api/products/{id}', [
    ProductCatalogController::class,
    'deleteProduct',
]);

// search
$router->addRoute('GET', '/api/search', [
    SearchEngineController::class,
    'searchProductsByBrand',
]);

// seller product apis
$router->addRoute('GET', '/api/seller/products', [
    SellerProductController::class,
    'getListedProducts',
]);
$router->addRoute('GET', '/api/seller/products/{id}', [
    SellerProductController::class,
    'getProductDetails',
]);

// seller search
$router->addRoute('GET', '/api/seller/search', [
    SellerSearchController::class,
    'searchSellerProducts',
]);

// ===================== user auth =========================
$router->addRoute('POST', '/api/auth/signin', [AuthController::class, 'login']);
$router->addRoute('POST', '/api/auth/signup', [
    AuthController::class,
    'register',
]);
$router->addRoute('POST', '/api/auth/password-reset', [
    PasswordResetController::class,
    'resetPassword',
]);

// user profile apis
$router->addRoute('GET', '/api/user/profile', [
    UserProfileController::class,
    'getUserProfile',
]);
$router->addRoute('PUT', '/api/user/profile', [
    UserProfileController::class,
    'updateUserProfile',
]);

// user assign apis
$router->addRoute('POST', '/api/user/assign', [
    UserAssignController::class,
    'assignUserRole',
]);
$router->addRoute('POST', '/api/user/revoke', [
    UserAssignController::class,
    'revokeUserRole',
]);

// user list apis
$router->addRoute('GET', '/api/user/list', [
    UserListController::class,
    'getUserRoles',
]);
