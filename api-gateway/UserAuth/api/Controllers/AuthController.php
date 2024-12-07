<?php

namespace Api\Controllers;

use Database\Database;
use Auth\UserAuthentication;

class AuthController
{
    private $auth;

    public function __construct(Database $db)
    {
        $this->auth = new UserAuthentication($db);
    }

    public function register($data)
    {
        // Ensure required fields are provided
        if (
            !empty($data['first_name']) &&
            !empty($data['last_name']) &&
            !empty($data['email']) &&
            !empty($data['phone']) &&
            !empty($data['address']) &&
            !empty($data['password'])
        ) {
            if (
                !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ||
                !preg_match('/@gmail\.com$/', $data['email'])
            ) {
                return [
                    'error' =>
                        'Invalid email address. Only Gmail accounts are allowed.',
                ];
            }
            if (!preg_match('/^09[0-9]{9}$/', $data['phone'])) {
                return [
                    'error' =>
                        'Invalid phone number. It must start with 09 and be 11 digits long.',
                ];
            }
            $password_error = $this->validatePassword($data['password']);
            if ($password_error) {
                return ['error' => $password_error];
            }
            return $this->auth->register(
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $data['address'],
                $data['password']
            );
        } else {
            return ['error' => 'Invalid input data'];
        }
    }

    public function login($data)
    {
        if (!empty($data['email']) && !empty($data['password'])) {
            return $this->auth->login($data['email'], $data['password']);
        } else {
            return ['error' => 'Invalid input data'];
        }
    }

    // Function to validate password
    public function validatePassword($password)
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number';
        }
        return null;
    }

    public function logout($token)
    {
        return $this->auth->logout($token);
    }
}
