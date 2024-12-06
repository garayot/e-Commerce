<?php

namespace Api\Controllers;

use Auth\PasswordReset;
use Api\Controllers\AuthController;

class PasswordResetController
{
    private $passwordReset;
    private $validate;

    public function __construct($db)
    {
        $this->passwordReset = new PasswordReset($db);
        $this->validate = new AuthController($db);
    }

    public function requestPasswordReset($data)
    {
        if (!empty($data['email'])) {
            return $this->passwordReset->requestPasswordReset($data['email']);
        } else {
            return ['error' => 'Email is required'];
        }
    }

    public function resetPassword($data)
    {
        if (!empty($data['password']) && !empty($data['confirm_password'])) {
            if ($data['password'] === $data['confirm_password']) {
                $password_error = $this->validate->validatePassword($data['password']);
                if ($password_error) {
                    return ['error' => $password_error];
                }
                return $this->passwordReset->resetPassword($data['password']);
            } else {
                return ['error' => 'Passwords do not match'];
            }
        } else {
            return ['error' => 'Password and confirm password are required'];
        }
    }
}
