<?php

namespace Api\Controllers;

use Database\Database;
use PDO;

class ProductCatalogController
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }

    public function getAllProducts($page = 1, $limit = 30)
    {
        // Validate and sanitize the $page parameter
        $page = filter_var($page, FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 1,
                'min_range' => 1,
            ],
        ]);

        $limit = filter_var($limit, FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 30,
                'min_range' => 1,
            ],
        ]);

        $offset = ($page - 1) * $limit;

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url, c.category_name
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countResult = $this->db->query(
            'SELECT COUNT(*) as total FROM products'
        );
        $total = $countResult->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'products' => array_map(function ($product) {
                return [
                    'product_name' => (string) $product['product_name'],
                    'description' => (string) $product['description'],
                    'price' => (float) $product['price'],
                    'stock_quantity' => (int) $product['stock_quantity'],
                    'image_url' => (string) $product['image_url'],
                    'category_name' => (string) $product['category_name'],
                ];
            }, $products),
            'total' => (int) $total,
            'page' => (int) $page,
            'limit' => (int) $limit,
            'total_pages' => (int) ceil($total / $limit),
        ];
    }

    public function getProductDetails($product_id)
    {
        // Ensure product_id is an integer
        $product_id = (int) $product_id;

        if (!$product_id) {
            return ['error' => 'Product ID is required'];
        }

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, b.brand_id, c.category_name, 
                   p.size, p.color, p.image_url
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            JOIN brands b ON p.brand_id = b.brand_id
            WHERE p.product_id = :product_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return ['error' => 'Product not found'];
        }

        return [
            'product_name' => (string) $product['product_name'],
            'description' => (string) $product['description'],
            'price' => (float) $product['price'],
            'stock_quantity' => (int) $product['stock_quantity'],
            'brand_id' => (int) $product['brand_id'],
            'category_name' => (string) $product['category_name'],
            'size' => (string) $product['size'],
            'color' => (string) $product['color'],
            'image_url' => (string) $product['image_url'],
        ];
    }

    public function filterProductsByCategory($category_name)
    {
        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE LOWER(c.category_name) = :category_name AND p.stock_quantity > 0
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(
            ':category_name',
            strtolower($category_name),
            PDO::PARAM_STR
        );
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'category' => ['category_name' => ucfirst((string) $category_name)],
            'products' => array_map(function ($product) {
                return [
                    'product_name' => (string) $product['product_name'],
                    'description' => (string) $product['description'],
                    'price' => (float) $product['price'],
                    'stock_quantity' => (int) $product['stock_quantity'],
                ];
            }, $products),
        ];
    }

    public function sortProductsByCreationDate(
        $order = 'new',
        $page = 1,
        $limit = 30
    ) {
        $order = strtolower(trim($order));
        $sortOrder = $order === 'old' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $limit;

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            ORDER BY p.created_at $sortOrder
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countResult = $this->db->query(
            'SELECT COUNT(*) as total FROM products'
        );
        $total = $countResult->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'products' => array_map(function ($product) {
                return [
                    'product_name' => (string) $product['product_name'],
                    'description' => (string) $product['description'],
                    'price' => (float) $product['price'],
                    'stock_quantity' => (int) $product['stock_quantity'],
                    'image_url' => (string) $product['image_url'],
                ];
            }, $products),
            'total' => (int) $total,
            'page' => (int) $page,
            'limit' => (int) $limit,
            'total_pages' => (int) ceil($total / $limit),
        ];
    }

    public function filterProductsByBrand($brand_name)
    {
        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            JOIN brands b ON p.brand_id = b.brand_id
            WHERE LOWER(b.brand_name) = :brand_name AND p.stock_quantity > 0
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(
            ':brand_name',
            strtolower($brand_name),
            PDO::PARAM_STR
        );
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'brand' => ['brand_name' => ucfirst((string) $brand_name)],
            'products' => array_map(function ($product) {
                return [
                    'product_name' => (string) $product['product_name'],
                    'description' => (string) $product['description'],
                    'price' => (float) $product['price'],
                    'stock_quantity' => (int) $product['stock_quantity'],
                    'image_url' => (string) $product['image_url'],
                ];
            }, $products),
        ];
    }

    public function filterProductsBySize($size)
    {
        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            WHERE p.size = :size AND p.stock_quantity > 0
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':size', $size, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'size' => ['size' => (string) $size],
            'products' => array_map(function ($product) {
                return [
                    'product_name' => (string) $product['product_name'],
                    'description' => (string) $product['description'],
                    'price' => (float) $product['price'],
                    'stock_quantity' => (int) $product['stock_quantity'],
                    'image_url' => (string) $product['image_url'],
                ];
            }, $products),
        ];
    }
}
