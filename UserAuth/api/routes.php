<?php

require '../Auth/User.php';
require '../Auth/PasswordReset.php';
require './controllers/AuthController.php';
require './controllers/PasswordResetController.php';

use Api\Controllers\AuthController;
use Api\Controllers\PasswordResetController;

class Router
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function route($action, $data)
    {
        switch ($action) {
            case 'register':
                return $this->handleRegister($data);
            case 'login':
                return $this->handleLogin($data);
            case 'verify_code':
                return $this->handleVerifyCode($data);
            case 'logout':
                return $this->handleLogout();
            case 'reset_request':
                return $this->handleResetRequest($data);
            case 'reset_password':
                return $this->handleResetPassword($data);
            default:
                return ['error' => 'Action not found'];
        }
    }

    private function handleRegister($data)
    {
        $controller = new AuthController($this->db);
        return $controller->register($data);
    }

    private function handleLogin($data)
    {
        $controller = new AuthController($this->db);
        return $controller->login($data);
    }

    private function handleVerifyCode($data)
    {
        $controller = new AuthController($this->db);
        return $controller->verifyCode($data);
    }

    private function handleLogout()
    {
        $controller = new AuthController($this->db);
        return $controller->logout();
    }

    private function handleResetRequest($data)
    {
        $controller = new PasswordResetController($this->db);
        return $controller->requestPasswordReset($data);
    }

    private function handleResetPassword($data)
    {
        $controller = new PasswordResetController($this->db);
        return $controller->resetPassword($data);
    }
}
