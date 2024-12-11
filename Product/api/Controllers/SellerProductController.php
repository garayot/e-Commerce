<?php

namespace Api\Controllers;

require_once __DIR__ . '/../../../UserAuth/Auth/UserProfile.php';

use Database\Database;
use Auth\UserProfile;

class SellerProductController
{
    private $db;
    private $userProfile;

    const ROLE_SELLER = 'seller';

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
        $this->userProfile = new UserProfile($db);
    }

    /**
     * Get Bearer Token from request headers.
     *
     * @return string|null The token or null if not found.
     */
    private function getBearerToken()
    {
        $headers = getallheaders();
        return isset($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches) ? $matches[1] : null;
    }

    /**
     * Validate that the current user is a seller.
     *
     * @return string|array The user UUID or an error message.
     */
    private function validateSeller()
    {
        $token = $this->getBearerToken();
        $user_uuid = $this->userProfile->validateToken($token);

        if (!$user_uuid) {
            return ['error' => 'Unauthorized access - Invalid or expired token'];
        }

        $stmt = $this->db->prepare("SELECT role FROM users WHERE user_uuid = ?");
        $stmt->bind_param('s', $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || $user['role'] !== self::ROLE_SELLER) {
            return ['error' => 'Unauthorized access'];
        }

        return $user_uuid;
    }

    /**
     * Add a new product for the authenticated seller.
     *
     * @param array $data The product data.
     * @return array The success or error message.
     */
    public function addNewProduct($data)
    {
        $user_uuid = $this->validateSeller();
        if (is_array($user_uuid)) {
            return $user_uuid;
        }

        // Strict data type checks
        if (!is_string($data['product_name']) || empty($data['product_name']) ||
            !is_string($data['description']) || empty($data['description']) ||
            !(is_float($data['price']) || (is_numeric($data['price']) && (float)$data['price'] > 0)) ||
            !(is_int($data['category_id']) || ctype_digit(strval($data['category_id']))) ||
            !(is_int($data['brand_id']) || ctype_digit(strval($data['brand_id']))) ||
            !(is_int($data['stock_quantity']) || ctype_digit(strval($data['stock_quantity']))) ||
            !is_string($data['size']) || empty($data['size']) ||
            !is_string($data['color']) || empty($data['color']) ||
            !is_string($data['image_url']) || empty($data['image_url'])) {
            return ['error' => 'Invalid data types provided'];
        }

        // Cast data types where necessary
        $data['price'] = (float)$data['price'];
        $data['category_id'] = (int)$data['category_id'];
        $data['brand_id'] = (int)$data['brand_id'];
        $data['stock_quantity'] = (int)$data['stock_quantity'];

        // Prepare and bind the statement
        $stmt = $this->db->prepare(
            "INSERT INTO products (product_name, description, price, stock_quantity, category_id, brand_id, size, color, image_url, user_uuid) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            'ssdiiissss',
            $data['product_name'],
            $data['description'],
            $data['price'],
            $data['stock_quantity'],
            $data['category_id'],
            $data['brand_id'],
            $data['size'],
            $data['color'],
            $data['image_url'],
            $user_uuid
        );

        // Execute and return success or error
        return $stmt->execute() ? ['success' => 'Product added successfully'] : ['error' => 'Failed to add product'];
    }

    /**
     * Get all products listed by the seller.
     *
     * @return array List of products or error.
     */
    public function getListedProducts()
    {
        $user_uuid = $this->validateSeller();
        if (is_array($user_uuid)) {
            return $user_uuid;
        }

        $stmt = $this->db->prepare("SELECT product_id, product_name, description, price, stock_quantity, image_url, created_at
            FROM products WHERE user_uuid = ?");
        $stmt->bind_param('s', $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = $result->fetch_all(MYSQLI_ASSOC);
        return [
            'products' => array_map(function ($product) {
                return [
                    'product_id' => (int)$product['product_id'],
                    'product_name' => (string)$product['product_name'],
                    'description' => (string)$product['description'],
                    'price' => (float)$product['price'],
                    'stock_quantity' => (int)$product['stock_quantity'],
                    'image_url' => (string)$product['image_url'],
                    'created_at' => (string)$product['created_at']
                ];
            }, $products)
        ];
    }

    /**
     * Update an existing product.
     *
     * @param int $product_id The product ID.
     * @return array The success or error message.
     */
    public function updateProduct($product_id)
    {
        $user_uuid = $this->validateSeller();
        if (is_array($user_uuid)) {
            return $user_uuid;
        }

        // Ensure product_id is an integer
        $product_id = (int)$product_id;

        if (empty($product_id)) {
            return ['error' => 'Product ID is required for updating'];
        }

        // Decode JSON input data
        $data = json_decode(file_get_contents('php://input'), true);

        if ($data === null) {
            return ['error' => 'Invalid JSON data'];
        }

        // Validate data and update the product in the database
        // (similar data validation as in addNewProduct)

        // Check if product exists and belongs to the seller
        $stmt = $this->db->prepare("SELECT * FROM products WHERE product_id = ? AND user_uuid = ?");
        $stmt->bind_param('is', $product_id, $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            return ['error' => 'Product not found or unauthorized'];
        }

        // Update the product data
        $stmt = $this->db->prepare(
            "UPDATE products SET product_name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, 
            brand_id = ?, size = ?, color = ?, image_url = ? WHERE product_id = ? AND user_uuid = ?"
        );

        // Bind parameters and execute the query
        $stmt->bind_param(
            'ssdiiissssi',
            $data['product_name'],
            $data['description'],
            $data['price'],
            $data['stock_quantity'],
            $data['category_id'],
            $data['brand_id'],
            $data['size'],
            $data['color'],
            $data['image_url'],
            $product_id,
            $user_uuid
        );

        return $stmt->execute() ? ['success' => 'Product updated successfully'] : ['error' => 'Failed to update product'];
    }

    /**
     * Delete a product.
     *
     * @param int $product_id The product ID.
     * @return array The success or error message.
     */
    public function deleteProduct($product_id)
    {
        $user_uuid = $this->validateSeller();
        if (is_array($user_uuid)) {
            return $user_uuid;
        }

        $stmt = $this->db->prepare("SELECT user_uuid FROM products WHERE product_id = ?");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            return ['error' => 'Product not found'];
        }

        if ($product['user_uuid'] !== $user_uuid) {
            return ['error' => 'Unauthorized access'];
        }

        $delete_stmt = $this->db->prepare("DELETE FROM products WHERE product_id = ?");
        $delete_stmt->bind_param('i', $product_id);
        
        return $delete_stmt->execute() ? ['success' => 'Product deleted successfully'] : ['error' => 'Failed to delete product'];
    }

    /**
     * Filter products by brand.
     *
     * @param string $brand_name The brand name.
     * @return array Filtered products or error message.
     */
    public function filterProductsByBrand($brand_name)
    {
        $brand_name = $this->db->real_escape_string(strtolower($brand_name));
    
        $stmt = $this->db->prepare("SELECT * FROM products WHERE LOWER(brand_name) = ?");
        $stmt->bind_param('s', $brand_name);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = $result->fetch_all(MYSQLI_ASSOC);

        return $products ? $products : ['error' => 'No products found for this brand'];
    }
        /**
     * Get details of a specific product.
     *
     * @param int $product_id The product ID.
     * @return array The product details or error message.
     */
    public function getProductDetails($product_id)
    {
        // Validate if the seller is authenticated
        $user_uuid = $this->validateSeller();
        if (is_array($user_uuid)) {
            return $user_uuid;
        }

        // Ensure product_id is an integer
        $product_id = (int)$product_id;

        // Check if the product exists and belongs to the seller
        $stmt = $this->db->prepare("SELECT * FROM products WHERE product_id = ? AND user_uuid = ?");
        $stmt->bind_param('is', $product_id, $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            return ['error' => 'Product not found or unauthorized access'];
        }

        // Return product details
        return [
            'product_id' => (int)$product['product_id'],
            'product_name' => (string)$product['product_name'],
            'description' => (string)$product['description'],
            'price' => (float)$product['price'],
            'stock_quantity' => (int)$product['stock_quantity'],
            'category_id' => (int)$product['category_id'],
            'brand_id' => (int)$product['brand_id'],
            'size' => (string)$product['size'],
            'color' => (string)$product['color'],
            'image_url' => (string)$product['image_url'],
            'created_at' => (string)$product['created_at']
        ];
    }

}

?>
