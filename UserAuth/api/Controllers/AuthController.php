<?php

namespace Api\Controllers;

use Auth\UserAuthentication;

class AuthController
{
    private $auth;

    public function __construct($db)
    {
        $this->auth = new UserAuthentication($db);
    }

    public function register($data)
    {
        // Ensure required fields are provided
        if (!empty($data['first_name']) && !empty($data['last_name']) && !empty($data['email']) && !empty($data['phone']) && !empty($data['address']) && !empty($data['password'])) {
            return $this->auth->register($data['first_name'], $data['last_name'], $data['email'], $data['phone'], $data['address'], $data['password']);
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

    public function verifyCode($data)
    {
        if (!empty($data['user_uuid']) && !empty($data['verification_code'])) {
            return $this->auth->verifyCode($data['user_uuid'], $data['verification_code']);
        } else {
            return ['error' => 'Invalid input data'];
        }
    }

    public function logout()
    {
        return $this->auth->logout(); // Call the logout method without passing the token
    }
}
