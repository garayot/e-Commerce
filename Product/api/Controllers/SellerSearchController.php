<?php

namespace Api\Controllers;

require_once __DIR__ . '/../../../UserAuth/Auth/UserProfile.php';

use Database\Database;
use Auth\UserProfile;

class SellerSearchController
{
    private $db;
    private $userProfile;

    const ROLE_SELLER = 'seller';

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection(); // Get the connection object from the Database class
        $this->userProfile = new UserProfile($db);
    }

    /**
     * Get Bearer token from the Authorization header.
     *
     * @return string|null
     */
    private function getBearerToken()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Validate if the user is a seller and retrieve their UUID.
     *
     * @return string|array
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
            return ['error' => 'Unauthorized access - Only sellers are allowed to perform this action'];
        }

        return $user_uuid;
    }

    /**
     * Search products by name or description.
     *
     * @param string $query The search query.
     * @return array
     */
    public function searchProductsByName($query)
    {
        $user_uuid = $this->validateSeller();
        if (is_array($user_uuid)) return $user_uuid;

        // Escape and sanitize the query
        $escapedQuery = $this->db->real_escape_string(strtolower($query));

        $stmt = $this->db->prepare("
            SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p 
            WHERE (SOUNDEX(p.product_name) = SOUNDEX(?) OR SOUNDEX(p.description) = SOUNDEX(?))
              AND p.user_uuid = ?
        ");
        
        $stmt->bind_param('sss', $escapedQuery, $escapedQuery, $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        return empty($products) ? ['error' => 'No products found'] : [
            'query' => (string)$query,
            'results' => array_map([$this, 'mapProduct'], $products)
        ];
    }

    /**
     * Search products by name or description within a price range.
     *
     * @param string $query The search query.
     * @param float $min_price The minimum price.
     * @param float $max_price The maximum price.
     * @return array
     */
    public function searchProductsWithPriceRange($query, $min_price, $max_price)
    {
        $user_uuid = $this->validateSeller();
        if (is_array($user_uuid)) return $user_uuid;

        // Escape and sanitize the inputs
        $escapedQuery = $this->db->real_escape_string(strtolower($query));
        $min_price = (float)$min_price;
        $max_price = (float)$max_price;

        $stmt = $this->db->prepare("
            SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p 
            WHERE (p.product_name LIKE CONCAT('%', ?, '%') OR p.description LIKE CONCAT('%', ?, '%'))
              AND p.price BETWEEN ? AND ?
              AND p.user_uuid = ?
        ");
        
        $stmt->bind_param('sddss', $escapedQuery, $escapedQuery, $min_price, $max_price, $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'query' => (string)$query, 
            'min_price' => (float)$min_price, 
            'max_price' => (float)$max_price, 
            'results' => array_map([$this, 'mapProduct'], $products)
        ];
    }

    /**
     * Search products by category name.
     *
     * @param string $category_name The category name.
     * @return array
     */
    public function searchProductsByCategory($category_name)
    {
        $user_uuid = $this->validateSeller();
        if (is_array($user_uuid)) return $user_uuid;

        // Escape and sanitize the category name
        $escapedCategoryName = $this->db->real_escape_string(strtolower($category_name));

        $stmt = $this->db->prepare("
            SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url, c.category_name
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE LOWER(c.category_name) = ? AND p.user_uuid = ?
        ");
        
        $stmt->bind_param('ss', $escapedCategoryName, $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'category' => ['category_name' => ucfirst((string)$category_name)],
            'products' => array_map([$this, 'mapProduct'], $products)
        ];
    }

    /**
     * Helper function to map product data.
     *
     * @param array $product The product data.
     * @return array
     */
    private function mapProduct($product)
    {
        return [
            'product_id' => (int)$product['product_id'],
            'product_name' => (string)$product['product_name'],
            'description' => (string)$product['description'],
            'price' => (float)$product['price'],
            'stock_quantity' => (int)$product['stock_quantity'],
            'image_url' => (string)$product['image_url']
        ];
    }
}
