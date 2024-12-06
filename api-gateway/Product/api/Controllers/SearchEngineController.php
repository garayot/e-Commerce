<?php

namespace Api\Controllers;

use Database\Database;

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
        $escapedQuery = $this->db->real_escape_string(strtolower($query)); // Escape and lowercase input

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p 
            WHERE SOUNDEX(product_name) = SOUNDEX(?) 
               OR SOUNDEX(description) = SOUNDEX(?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $escapedQuery, $escapedQuery);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
        $escapedQuery = $this->db->real_escape_string($query);
        $min_price = floatval($min_price);
        $max_price = floatval($max_price);

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p 
            WHERE (product_name LIKE ? OR description LIKE ?) 
            AND price BETWEEN ? AND ?";

        $stmt = $this->db->prepare($sql);
        $likeQuery = "%$escapedQuery%";
        $stmt->bind_param('ssdd', $likeQuery, $likeQuery, $min_price, $max_price);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return $this->formatSearchResults($query, $products, $min_price, $max_price);
    }

    /**
     * Search products by brand name.
     *
     * @param string $brand_name The brand name.
     * @return array The search results.
     */
    public function searchProductsByBrand($brand_name)
    {
        $escapedBrand = $this->db->real_escape_string(strtolower($brand_name));

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            JOIN brands b ON p.brand_id = b.brand_id
            WHERE b.brand_name LIKE ?";

        $stmt = $this->db->prepare($sql);
        $likeBrand = "%$escapedBrand%";
        $stmt->bind_param('s', $likeBrand);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
        $escapedQuery = $this->db->real_escape_string($query);
        $escapedSize = $this->db->real_escape_string($size);

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            WHERE (p.product_name LIKE ? OR p.description LIKE ?)
            AND p.size = ?";

        $stmt = $this->db->prepare($sql);
        $likeQuery = "%$escapedQuery%";
        $stmt->bind_param('sss', $likeQuery, $likeQuery, $escapedSize);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
                'product_name'   => (string)$product['product_name'],
                'description'    => (string)$product['description'],
                'price'          => (float)$product['price'],
                'stock_quantity' => (int)$product['stock_quantity'],
                'image_url'      => (string)$product['image_url']
            ];
        }, $products);

        $response = [
            'query'   => (string)$query,
            'results' => $results
        ];

        if ($extra) {
            $response['extra'] = $extra;
        }

        return $response;
    }
}
