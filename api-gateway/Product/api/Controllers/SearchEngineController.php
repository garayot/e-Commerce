<?php

namespace Api\Controllers;

use Database\Database;
use PDO;

class SearchEngineController
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection(); // Get the connection object from the Database class
    }

    /**
     * Search products by name or description.
     *
     * @param string $query The search query.
     * @return array The search results.
     */
    public function searchProductsByName($query)
    {
        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p 
            WHERE SOUNDEX(product_name) = SOUNDEX(:query) 
               OR SOUNDEX(description) = SOUNDEX(:query)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', $query, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->formatSearchResults($query, $products);
    }

    /**
     * Search products by name or description within a price range.
     *
     * @param string $query The search query.
     * @param float $min_price The minimum price.
     * @param float $max_price The maximum price.
     * @return array The search results.
     */
    public function searchProductsWithPriceRange($query, $min_price, $max_price)
    {
        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p 
            WHERE (product_name LIKE :query OR description LIKE :query) 
            AND price BETWEEN :min_price AND :max_price";

        $stmt = $this->db->prepare($sql);
        $likeQuery = "%$query%";
        $stmt->bindValue(':query', $likeQuery, PDO::PARAM_STR);
        $stmt->bindValue(':min_price', $min_price, PDO::PARAM_STR);
        $stmt->bindValue(':max_price', $max_price, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->formatSearchResults(
            $query,
            $products,
            $min_price,
            $max_price
        );
    }

    /**
     * Search products by brand name.
     *
     * @param string $brand_name The brand name.
     * @return array The search results.
     */
    public function searchProductsByBrand($data)
    {
        if (!isset($data['brand_name'])) {
            return ['error' => 'Brand name is required'];
        }

        $brand_name = $data['brand_name'];

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            JOIN brands b ON p.brand_id = b.brand_id
            WHERE b.brand_name LIKE :brand_name";

        $stmt = $this->db->prepare($sql);
        $likeBrand = "%$brand_name%";
        $stmt->bindValue(':brand_name', $likeBrand, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->formatSearchResults($brand_name, $products);
    }

    /**
     * Search products by size.
     *
     * @param string $query The search query.
     * @param string $size The product size.
     * @return array The search results.
     */
    public function searchProductsBySize($query, $size)
    {
        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            WHERE (p.product_name LIKE :query OR p.description LIKE :query)
            AND p.size = :size";

        $stmt = $this->db->prepare($sql);
        $likeQuery = "%$query%";
        $stmt->bindValue(':query', $likeQuery, PDO::PARAM_STR);
        $stmt->bindValue(':size', $size, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->formatSearchResults($query, $products, $size);
    }

    /**
     * Format the search results into a structured array.
     *
     * @param string $query The search query.
     * @param array $products The products to format.
     * @param mixed $extra Additional filters (price range, size, etc.).
     * @return array The formatted results.
     */
    private function formatSearchResults($query, $products, $extra = null)
    {
        $results = array_map(function ($product) {
            return [
                'product_name' => (string) $product['product_name'],
                'description' => (string) $product['description'],
                'price' => (float) $product['price'],
                'stock_quantity' => (int) $product['stock_quantity'],
                'image_url' => (string) $product['image_url'],
            ];
        }, $products);

        $response = [
            'query' => (string) $query,
            'results' => $results,
        ];

        if ($extra) {
            $response['extra'] = $extra;
        }

        return $response;
    }
}
