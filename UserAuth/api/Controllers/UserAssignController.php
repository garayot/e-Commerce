<?php

namespace Api\Controllers;

use Auth\UserAssign;

class UserAssignController
{
    private $userAssign;

    public function __construct($db)
    {
        $this->userAssign = new UserAssign($db);
    }

    public function assignUserRole($data)
    {
        $headers = getallheaders();

        if (empty($headers['Authorization']) || !preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return ['error' => 'Authorization token not provided or invalid'];
        }

        $admin_token = $matches[1];

        if (empty($data['user_uuid']) || empty($data['role'])) {
            return ['error' => 'Required fields are missing'];
        }

        return $this->userAssign->assignRole($admin_token, $data['user_uuid'], $data['role']);
    }
    public function revokeUserRole($data)
    {
        $headers = getallheaders();

        if (empty($headers['Authorization']) || !preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return ['error' => 'Authorization token not provided or invalid'];
        }

        $admin_token = $matches[1];

        if (empty($data['user_uuid'])) {
            return ['error' => 'User UUID is required'];
        }

        $response = $this->userAssign->revokeRole($admin_token, $data['user_uuid']);

        if (isset($response['current_role'])) {
            $response['message'] .= " Current role was: " . $response['current_role'];
        }

        return $response;
    }
}
