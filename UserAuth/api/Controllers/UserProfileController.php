<?php

namespace Api\Controllers;

use Auth\UserProfile;

class UserProfileController
{
    public $userProfile;

    public function __construct($db)
    {
        $this->userProfile = new UserProfile($db);
    }


    private function getBearerToken()
    {
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {

            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        return null;
    }


    public function getUserProfile()
    {

        $token = $this->getBearerToken();

        if ($token) {

            $user_uuid = $this->userProfile->validateToken($token);

            if ($user_uuid) {

                return $this->userProfile->getUserProfile($user_uuid);
            } else {
                return ['error' => 'Invalid or expired token'];
            }
        } else {
            return ['error' => 'Authorization token not found'];
        }
    }


    public function updateUserProfile($data)
    {

        $token = $this->getBearerToken();

        if ($token) {
            $user_uuid = $this->userProfile->validateToken($token);

            if ($user_uuid) {

                return $this->userProfile->updateUserProfile($user_uuid, $data);
            } else {
                return ['error' => 'Invalid or expired token'];
            }
        } else {
            return ['error' => 'Authorization token not found'];
        }
    }


    public function updateUserEmail($new_email)
    {

        $token = $this->getBearerToken();

        if ($token) {

            $user_uuid = $this->userProfile->validateToken($token);

            if ($user_uuid) {

                return $this->userProfile->updateUserEmail($user_uuid, $new_email);
            } else {
                return ['error' => 'Invalid or expired token'];
            }
        } else {
            return ['error' => 'Authorization token not found'];
        }
    }
}
