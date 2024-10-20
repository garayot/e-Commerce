<?php

namespace Auth;

require '../vendor/autoload.php';

use database\Database;

class UserAssign
{
    private $conn;

    public function __construct(Database $db)
    {
        $this->conn = $db->getConnection();
    }

    // Function to validate token and get user_uuid
    public function validateToken($token)
    {
        // Prepare SQL to validate the token and get user_uuid
        $stmt = $this->conn->prepare("SELECT user_uuid FROM session_token WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc()['user_uuid'];
        } else {
            return null;
        }
    }

    // Function to assign role to a user
    public function assignRole($admin_token, $user_uuid, $new_role)
    {
        // Validate the admin token
        $admin_uuid = $this->validateToken($admin_token);

        if ($admin_uuid === null) {
            return ['error' => 'Invalid token or token has expired'];
        }

        // Check if the admin user has admin role
        $stmt = $this->conn->prepare("SELECT role FROM users WHERE user_uuid = ? AND role = 'admin'");
        $stmt->bind_param('s', $admin_uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['error' => 'You do not have permission to assign roles'];
        }

        // Update the user's role
        $stmt = $this->conn->prepare("UPDATE users SET role = ? WHERE user_uuid = ?");
        $stmt->bind_param('ss', $new_role, $user_uuid);

        if ($stmt->execute()) {
            return ['message' => 'Role assigned successfully'];
        } else {
            return ['error' => 'Failed to assign role'];
        }
    }

    public function revokeRole($admin_token, $user_uuid)
    {
        // Validate the admin token
        $admin_uuid = $this->validateToken($admin_token);

        if ($admin_uuid === null) {
            return ['error' => 'Invalid token or token has expired'];
        }

        // Check if the admin user has admin role
        $stmt = $this->conn->prepare("SELECT role FROM users WHERE user_uuid = ? AND role = 'admin'");
        $stmt->bind_param('s', $admin_uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['error' => 'You do not have permission to revoke roles'];
        }

        // Check if the user is already a default role of 'user'
        $stmt = $this->conn->prepare("SELECT role FROM users WHERE user_uuid = ?");
        $stmt->bind_param('s', $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['error' => 'User not found'];
        }

        $user = $result->fetch_assoc();

        if ($user['role'] === 'user') {
            return ['error' => 'User is already in default role'];
        }

        // Revoke the user's role back to 'user'
        $default_role = 'user';
        $stmt = $this->conn->prepare("UPDATE users SET role = ? WHERE user_uuid = ?");
        $stmt->bind_param('ss', $default_role, $user_uuid);

        if ($stmt->execute()) {
            return ['message' => 'Role revoked successfully'];
        } else {
            return ['error' => 'Failed to revoke role'];
        }
    }
}
