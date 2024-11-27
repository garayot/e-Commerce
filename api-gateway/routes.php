// api-gateway/routes.php

<?php
// product apis (not yet final since we don't have the list of apis in one file to check)
require_once '../Product/api/Controllers/ProductCatalogController.php';
require_once '../Product/api/Controllers/SearchEngineController.php';
require_once '../Product/api/Controllers/SellerProductController.php';
require_once '../Product/api/Controllers/SellerSearchController.php';

// auth apis
require_once '../UserAuth/api/Controllers/PasswordReset.php';
require_once '../UserAuth/api/Controllers/User.php';
require_once '../UserAuth/api/Controllers/UserAssign.php';
require_once '../UserAuth/api/Controllers/UserList.php';
require_once '../UserAuth/api/Controllers/UserProfile.php';

// ===================== products =========================
$router->addRoute(
    'GET',
    '/api/products',
    'ProductCatalogController@listProducts'
);
$router->addRoute(
    'GET',
    '/api/products/{id}',
    'ProductCatalogController@getProduct'
);
$router->addRoute(
    'POST',
    '/api/products',
    'ProductCatalogController@createProduct'
);
$router->addRoute(
    'PUT',
    '/api/products/{id}',
    'ProductCatalogController@updateProduct'
);
$router->addRoute(
    'DELETE',
    '/api/products/{id}',
    'ProductCatalogController@deleteProduct'
);

// search
$router->addRoute('GET', '/api/search', 'SearchEngineController@search');

// seller product apis
$router->addRoute(
    'GET',
    '/api/seller/products',
    'SellerProductController@listSellerProducts'
);
$router->addRoute(
    'GET',
    '/api/seller/products/{id}',
    'SellerProductController@getSellerProduct'
);

// seller search
$router->addRoute(
    'GET',
    '/api/seller/search',
    'SellerSearchController@searchSellerProducts'
);

// user auth apis
$router->addRoute('POST', '/api/auth/signin', 'User@signin');
$router->addRoute('POST', '/api/auth/signup', 'User@signup');
$router->addRoute('POST', '/api/auth/password-reset', 'PasswordReset@reset');

// user profile apis
$router->addRoute('GET', '/api/user/profile', 'UserProfile@viewProfile');
$router->addRoute('PUT', '/api/user/profile', 'UserProfile@updateProfile');

// user assign apis
$router->addRoute('GET', '/api/user/assign', 'UserAssign@assignRole');
$router->addRoute('GET', '/api/user/list', 'UserList@listUsers');


?>
