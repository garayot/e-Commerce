<?php

require '../database/db.php';
require './routes.php'; // Include the routes file
require '../utils/response.php';

use database\Database;

class API
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($_GET['action'])) {
                $action = $_GET['action'];
                $router = new Router($this->db);
                jsonResponse($router->route($action, $data));
            } else {
                jsonResponse(['error' => 'Action not specified']);
            }
        } else {
            jsonResponse(['error' => 'Invalid request method']);
        }
    }
}

// Instantiate the API and handle the request
$api = new API();
$api->handleRequest();






// require '../database/db.php';
// require '../Auth/User.php'; 
// require '../Auth/PasswordReset.php'; 

// use database\Database;  
// use Auth\UserAuthentication; 
// use Auth\PasswordReset;  // Import your PasswordReset class

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {

//     $db = new Database();
//     $auth = new UserAuthentication($db);
//     $passwordReset = new PasswordReset($db); 

//     $data = json_decode(file_get_contents('php://input'), true);

//     if (isset($_GET['action'])) {
//         $action = $_GET['action'];

//         if ($action === 'register') {
//             // Ensure required fields are provided
//             if (!empty($data['first_name']) && !empty($data['last_name']) && !empty($data['email']) && !empty($data['phone']) && !empty($data['address']) && !empty($data['password'])) {
//                 $response = $auth->register($data['first_name'], $data['last_name'], $data['email'], $data['phone'], $data['address'], $data['password']);
//                 echo json_encode($response);
//             } else {
//                 echo json_encode(['error' => 'Invalid input data']);
//             }
//         } elseif ($action === 'login') {
//             if (!empty($data['email']) && !empty($data['password'])) {
//                 $response = $auth->login($data['email'], $data['password']);
//                 echo json_encode($response);
//             } else {
//                 echo json_encode(['error' => 'Invalid input data']);
//             }
//         } elseif ($action === 'verify_code') {
//             if (!empty($data['user_uuid']) && !empty($data['verification_code'])) {
//                 $response = $auth->verifyCode($data['user_uuid'], $data['verification_code']);
//                 echo json_encode($response);
//             } else {
//                 echo json_encode(['error' => 'Invalid input data']);
//             }
//         } elseif ($action === 'logout') {
//             $response = $auth->logout(); // Call the logout method without passing the token
//             echo json_encode($response);
//         } elseif ($action === 'reset_request') {
//             // Handle password reset request
//             if (!empty($data['email'])) {
//                 $response = $passwordReset->requestPasswordReset($data['email']);
//                 echo json_encode($response);
//             } else {
//                 echo json_encode(['error' => 'Email is required']);
//             }
//         } elseif ($action === 'reset_password') {
//             // Handle password reset
//             if (!empty($data['password']) && !empty($data['confirm_password'])) {
//                 if ($data['password'] === $data['confirm_password']) {
//                     // Call the resetPassword method with the entire $data array
//                     $response = $passwordReset->resetPassword($data);
//                     echo json_encode($response);
//                 } else {
//                     echo json_encode(['error' => 'Passwords do not match']);
//                 }
//             } else {
//                 echo json_encode(['error' => 'Password and confirm password are required']);
//             }
//         }
//     } else {
//         echo json_encode(['error' => 'Action not specified']);
//     }
// } else {
//     echo json_encode(['error' => 'Invalid request method']);
// }
