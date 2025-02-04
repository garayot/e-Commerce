<?php

namespace Auth;

use database\Database;

class UserProfile
{
    private $conn;

    public function __construct(Database $db)
    {
        $this->conn = $db->getConnection();
    }

    // Function to validate token and get user_uuid
    public function validateToken($token)
    {

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

    public function getUserProfile($user_uuid)
    {

        $stmt = $this->conn->prepare("SELECT user_uuid, first_name, last_name, email, address, phone_number
                                      FROM users
                                      WHERE user_uuid = ?");
        $stmt->bind_param('s', $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        } else {
            return ['error' => 'User not found'];
        }
    }

    public function updateUserProfile($user_uuid, $data)
    {
        // Array to hold query parts and corresponding bind params
        $fieldsToUpdate = [];
        $bindTypes = '';
        $bindValues = [];

        // Check which fields are provided and add them to the query
        if (isset($data['first_name'])) {
            $fieldsToUpdate[] = "first_name = ?";
            $bindTypes .= 's';
            $bindValues[] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $fieldsToUpdate[] = "last_name = ?";
            $bindTypes .= 's';
            $bindValues[] = $data['last_name'];
        }
        if (isset($data['address'])) {
            $fieldsToUpdate[] = "address = ?";
            $bindTypes .= 's';
            $bindValues[] = $data['address'];
        }
        if (isset($data['phone_number'])) {
            $fieldsToUpdate[] = "phone_number = ?";
            $bindTypes .= 's';
            $bindValues[] = $data['phone_number'];
        }

        // Ensure there's something to update
        if (empty($fieldsToUpdate)) {
            return ['error' => 'No data provided to update'];
        }

        // Add updated_at field and user_uuid for WHERE condition
        $fieldsToUpdate[] = "updated_at = CURRENT_TIMESTAMP";
        $bindTypes .= 's';
        $bindValues[] = $user_uuid;

        // Build the dynamic SQL query
        $sql = "UPDATE users SET " . implode(', ', $fieldsToUpdate) . " WHERE user_uuid = ?";

        // Prepare statement
        $stmt = $this->conn->prepare($sql);

        // Dynamically bind the parameters
        $stmt->bind_param($bindTypes, ...$bindValues);

        // Execute the query and return the result
        if ($stmt->execute()) {
            return ['message' => 'User profile updated successfully'];
        } else {
            return ['error' => 'Failed to update user profile'];
        }
    }


    public function updateUserEmail($user_uuid, $new_email)
    {

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $new_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {

            $stmt = $this->conn->prepare("UPDATE users SET email = ?, updated_at = CURRENT_TIMESTAMP WHERE user_uuid = ?");
            $stmt->bind_param('ss', $new_email, $user_uuid);

            if ($stmt->execute()) {
                return ['message' => 'Email updated successfully'];
            } else {
                return ['error' => 'Failed to update email'];
            }
        } else {
            return ['error' => 'Email already in use'];
        }
    }

    public function changePassword($user_uuid, $data)
    {

        $stmt = $this->conn->prepare("SELECT password FROM users WHERE user_uuid = ?");
        $stmt->bind_param('s', $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return ['error' => 'User not found'];
        }

        $user = $result->fetch_assoc();

        if (!password_verify($data['current_password'], $user['password'])) {
            return ['error' => 'Current password is incorrect'];
        }

        $hashed_password = password_hash($data['new_password'], PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE user_uuid = ?");
        $stmt->bind_param('ss', $hashed_password, $user_uuid);

        if ($stmt->execute()) {
            return ['message' => 'Password changed successfully'];
        } else {
            return ['error' => 'Failed to change password'];
        }
    }
}
