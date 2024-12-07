<?php

namespace Api\Controllers;

use Database\Database;
use Auth\UserProfile;
use PDO;

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
        if (
            isset($headers['Authorization']) &&
            preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)
        ) {
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

        if (!$user || $user['role'] !== self::ROLE_SELLER) {
            return [
                'error' =>
                    'Unauthorized access - Only sellers are allowed to perform this action',
            ];
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
        if (is_array($user_uuid)) {
            return $user_uuid;
        }

        $sql = "
            SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p 
            WHERE (SOUNDEX(p.product_name) = SOUNDEX(:query) OR SOUNDEX(p.description) = SOUNDEX(:query))
              AND p.user_uuid = :user_uuid
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', $query, PDO::PARAM_STR);
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return empty($products)
            ? ['error' => 'No products found']
            : [
                'query' => (string) $query,
                'results' => array_map([$this, 'mapProduct'], $products),
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
        if (is_array($user_uuid)) {
            return $user_uuid;
        }

        $sql = "
            SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p 
            WHERE (p.product_name LIKE :query OR p.description LIKE :query)
              AND p.price BETWEEN :min_price AND :max_price
              AND p.user_uuid = :user_uuid
        ";

        $stmt = $this->db->prepare($sql);
        $likeQuery = "%$query%";
        $stmt->bindValue(':query', $likeQuery, PDO::PARAM_STR);
        $stmt->bindValue(':min_price', $min_price, PDO::PARAM_STR);
        $stmt->bindValue(':max_price', $max_price, PDO::PARAM_STR);
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'query' => (string) $query,
            'min_price' => (float) $min_price,
            'max_price' => (float) $max_price,
            'results' => array_map([$this, 'mapProduct'], $products),
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
        if (is_array($user_uuid)) {
            return $user_uuid;
        }

        $sql = "
            SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url, c.category_name
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE LOWER(c.category_name) = :category_name AND p.user_uuid = :user_uuid
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(
            ':category_name',
            strtolower($category_name),
            PDO::PARAM_STR
        );
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'category' => ['category_name' => ucfirst((string) $category_name)],
            'products' => array_map([$this, 'mapProduct'], $products),
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
            'product_id' => (int) $product['product_id'],
            'product_name' => (string) $product['product_name'],
            'description' => (string) $product['description'],
            'price' => (float) $product['price'],
            'stock_quantity' => (int) $product['stock_quantity'],
            'image_url' => (string) $product['image_url'],
        ];
    }
}
