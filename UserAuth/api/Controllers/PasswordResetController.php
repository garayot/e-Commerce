<?php

namespace Api\Controllers;

use Auth\PasswordReset;

class PasswordResetController
{
    private $passwordReset;

    public function __construct($db)
    {
        $this->passwordReset = new PasswordReset($db);
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
                return $this->passwordReset->resetPassword($data);
            } else {
                return ['error' => 'Passwords do not match'];
            }
        } else {
            return ['error' => 'Password and confirm password are required'];
        }
    }
}
