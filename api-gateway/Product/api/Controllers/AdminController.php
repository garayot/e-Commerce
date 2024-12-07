<?php

namespace Api\Controllers;

use Database\Database;
use Api\Controllers\UserProfileController;
use PDO;

class AdminController
{
    private $db;
    private $userProfile;

    const ROLE_ADMIN = 'admin';

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
        $this->userProfile = new UserProfileController($db);
    }

    /**
     * Get Bearer Token from Authorization header
     *
     * @return string|null
     */
    private function getBearerToken()
    {
        $headers = getallheaders();
        return isset($headers['Authorization']) &&
            preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)
            ? $matches[1]
            : null;
    }

    /**
     * Validate if the current user is an admin
     *
     * @return string|array User UUID if valid, error message if not
     */
    private function validateAdmin()
    {
        $token = $this->getBearerToken();
        $user_uuid = $this->userProfile->validateToken($token);

        if (!$user_uuid) {
            return [
                'error' => 'Unauthorized access - Invalid or expired token',
            ];
        }

        $stmt = $this->db->prepare(
            'SELECT role FROM users WHERE user_uuid = :user_uuid'
        );
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['role'] !== self::ROLE_ADMIN) {
            return ['error' => 'Unauthorized access'];
        }

        return $user_uuid;
    }

    /**
     * Delete a product by product_id
     *
     * @param int $product_id
     * @return array Success or error message
     */
    public function deleteProduct($product_id)
    {
        $admin_uuid = $this->validateAdmin();
        if (is_array($admin_uuid)) {
            return $admin_uuid;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM products WHERE product_id = :product_id'
        );
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);

        return $stmt->execute()
            ? ['success' => 'Product deleted successfully']
            : ['error' => 'Failed to delete product'];
    }

    /**
     * Search products by name or description
     *
     * @param string $query
     * @return array List of products matching the query
     */
    public function searchProduct($query)
    {
        $admin_uuid = $this->validateAdmin();
        if (is_array($admin_uuid)) {
            return $admin_uuid;
        }

        $stmt = $this->db->prepare("
            SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p 
            WHERE p.product_name LIKE CONCAT('%', :query, '%') 
               OR p.description LIKE CONCAT('%', :query, '%')
        ");

        $stmt->bindValue(':query', $query, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'query' => $query,
            'results' => $products,
        ];
    }

    /**
     * Get details of a product by product_id
     *
     * @param int $product_id
     * @return array Product details or error message
     */
    public function getProductDetails($product_id)
    {
        $admin_uuid = $this->validateAdmin();
        if (is_array($admin_uuid)) {
            return $admin_uuid;
        }

        $stmt = $this->db->prepare("
            SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url, 
                   p.size, p.color, c.category_name, b.brand_name, u.user_uuid
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            LEFT JOIN users u ON p.user_uuid = u.user_uuid
            WHERE p.product_id = :product_id
        ");
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product ?: ['error' => 'Product not found'];
    }

    /**
     * Show all products added by a specific seller
     *
     * @param string $seller_uuid
     * @return array Seller's products or error message
     */
    public function showProductsPerSeller($seller_uuid)
    {
        $admin_uuid = $this->validateAdmin();
        if (is_array($admin_uuid)) {
            return $admin_uuid;
        }

        // Validate the seller UUID
        if (empty($seller_uuid)) {
            return ['error' => 'Seller UUID is required'];
        }

        // Check if the seller exists and has a valid role
        $stmt = $this->db->prepare(
            'SELECT role FROM users WHERE user_uuid = :seller_uuid'
        );
        $stmt->bindValue(':seller_uuid', $seller_uuid, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['error' => 'Seller not found'];
        }

        if ($user['role'] !== 'seller') {
            return ['error' => 'This user is not a seller'];
        }

        // Get products for the specific seller
        $stmt = $this->db->prepare("
            SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url, 
                   p.created_at, c.category_name, b.brand_name, u.first_name, u.last_name
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            JOIN brands b ON p.brand_id = b.brand_id
            JOIN users u ON p.user_uuid = u.user_uuid
            WHERE p.user_uuid = :seller_uuid
        ");
        $stmt->bindValue(':seller_uuid', $seller_uuid, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products)) {
            return ['error' => 'No products found for the specified seller'];
        }

        return [
            'seller_uuid' => $seller_uuid,
            'first_name' => $products[0]['first_name'],
            'last_name' => $products[0]['last_name'],
            'products' => $products,
        ];
    }
}
