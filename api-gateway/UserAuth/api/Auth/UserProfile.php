<?php

namespace Auth;

use Database\Database;
use PDO;

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
        $stmt = $this->conn->prepare(
            'SELECT user_uuid FROM sessiontokens WHERE session_token = :token AND session_expires_at > NOW()'
        );
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['user_uuid'];
        } else {
            return null;
        }
    }

    public function getUserProfile($user_uuid)
    {
        $stmt = $this->conn
            ->prepare("SELECT user_uuid, first_name, last_name, email, address, phone_number
                                      FROM users
                                      WHERE user_uuid = :user_uuid");
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result;
        } else {
            return ['error' => 'User not found'];
        }
    }

    public function updateUserProfile($user_uuid, $data)
    {
        // Array to hold query parts and corresponding bind params
        $fieldsToUpdate = [];
        $bindValues = [];

        // Check which fields are provided and add them to the query
        if (isset($data['first_name'])) {
            $fieldsToUpdate[] = 'first_name = :first_name';
            $bindValues[':first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $fieldsToUpdate[] = 'last_name = :last_name';
            $bindValues[':last_name'] = $data['last_name'];
        }
        if (isset($data['address'])) {
            $fieldsToUpdate[] = 'address = :address';
            $bindValues[':address'] = $data['address'];
        }
        if (isset($data['phone_number'])) {
            $fieldsToUpdate[] = 'phone_number = :phone_number';
            $bindValues[':phone_number'] = $data['phone_number'];
        }

        // Ensure there's something to update
        if (empty($fieldsToUpdate)) {
            return ['error' => 'No data provided to update'];
        }

        // Add updated_at field and user_uuid for WHERE condition
        $fieldsToUpdate[] = 'updated_at = CURRENT_TIMESTAMP';
        $bindValues[':user_uuid'] = $user_uuid;

        // Build the dynamic SQL query
        $sql =
            'UPDATE users SET ' .
            implode(', ', $fieldsToUpdate) .
            ' WHERE user_uuid = :user_uuid';

        // Prepare statement
        $stmt = $this->conn->prepare($sql);

        // Dynamically bind the parameters
        foreach ($bindValues as $param => $value) {
            $stmt->bindValue($param, $value, PDO::PARAM_STR);
        }

        // Execute the query and return the result
        if ($stmt->execute()) {
            return ['message' => 'User profile updated successfully'];
        } else {
            return ['error' => 'Failed to update user profile'];
        }
    }

    public function updateUserEmail($user_uuid, $new_email)
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM users WHERE email = :email'
        );
        $stmt->bindValue(':email', $new_email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            $stmt = $this->conn->prepare(
                'UPDATE users SET email = :email, updated_at = CURRENT_TIMESTAMP WHERE user_uuid = :user_uuid'
            );
            $stmt->bindValue(':email', $new_email, PDO::PARAM_STR);
            $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);

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
        $stmt = $this->conn->prepare(
            'SELECT password FROM users WHERE user_uuid = :user_uuid'
        );
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return ['error' => 'User not found'];
        }

        if (!password_verify($data['current_password'], $result['password'])) {
            return ['error' => 'Current password is incorrect'];
        }

        $hashed_password = password_hash(
            $data['new_password'],
            PASSWORD_BCRYPT
        );
        $stmt = $this->conn->prepare(
            'UPDATE users SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE user_uuid = :user_uuid'
        );
        $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return ['message' => 'Password changed successfully'];
        } else {
            return ['error' => 'Failed to change password'];
        }
    }
}
