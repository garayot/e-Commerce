// api-gateway/routes.php
<?php
require_once 'Router.php';

$router = new Router();

// product apis
require_once __DIR__ .
    '/../Product/api/Controllers/ProductCatalogController.php';
require_once __DIR__ . '/../Product/api/Controllers/SearchEngineController.php';
require_once __DIR__ .
    '/../Product/api/Controllers/SellerProductController.php';
require_once __DIR__ . '/../Product/api/Controllers/SellerSearchController.php';

// auth apis
require_once __DIR__ . '/../UserAuth/api/Controllers/AuthController.php';
require_once __DIR__ .
    '/../UserAuth/api/Controllers/PasswordResetController.php';
require_once __DIR__ . '/../UserAuth/api/Controllers/UserAssignController.php';
require_once __DIR__ . '/../UserAuth/api/Controllers/UserListController.php';
require_once __DIR__ . '/../UserAuth/api/Controllers/UserProfileController.php';

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

// ===================== user auth =========================
$router->addRoute('POST', '/api/auth/signin', 'AuthController@login');
$router->addRoute('POST', '/api/auth/signup', 'AuthController@register');
$router->addRoute(
    'POST',
    '/api/auth/password-reset',
    'PasswordResetController@resetPassword'
);

// user profile apis
$router->addRoute(
    'GET',
    '/api/user/profile',
    'UserProfileController@getUserProfile'
);
$router->addRoute(
    'PUT',
    '/api/user/profile',
    'UserProfileController@updateUserProfile'
);

// user assign apis
$router->addRoute(
    'POST',
    '/api/user/assign',
    'UserAssignController@assignUserRole'
);
$router->addRoute(
    'POST',
    '/api/user/revoke',
    'UserAssignController@revokeUserRole'
);

// user list apis
$router->addRoute('GET', '/api/user/list', 'UserListController@getUserRoles');


?>
