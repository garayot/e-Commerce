<?php

namespace Auth;

use Auth\UserAssign;

class UserList
{
    private $conn;
    private $validateToken;

    public function __construct($db)
    {
        $this->conn = $db->getConnection();
        $this->validateToken = new UserAssign($db);
    }

    // Function to list users with pagination
    public function listAllUsersWithRoles($admin_token, $page = 1, $limit = 5)
    {
        $admin_uuid = $this->validateToken->validateToken($admin_token);

        if ($admin_uuid === null) {
            return ['error' => 'Invalid token or token has expired'];
        }

        // Check if the admin user has an admin role
        $stmt = $this->conn->prepare("SELECT role FROM users WHERE user_uuid = ? AND role = 'admin'");
        $stmt->bind_param('s', $admin_uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['error' => 'You do not have permission to view this data'];
        }

        // Calculate offset for pagination
        $offset = ($page - 1) * $limit;

        // Get total number of users
        $total_stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM users");
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $total = $total_result->fetch_assoc()['total'];

        // Fetch users with pagination
        $stmt = $this->conn->prepare("
            SELECT user_uuid, role
            FROM users
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        // Initialize an array to hold user data
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'user_id' => $row['user_uuid'],
                'role' => $row['role']
            ];
        }

        return [
            'page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
            'users' => $users
        ];
    }
}
