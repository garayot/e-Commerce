<?php

namespace Api\Controllers;

use Database\Database;

class ProductCatalogController
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }

    public function getAllProducts($page, $limit = 30)
    {
        $offset = ($page - 1) * $limit;

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url, c.category_name
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $countResult = $this->db->query("SELECT COUNT(*) as total FROM products");
        $total = $countResult->fetch_assoc()['total'];

        return [
            'products' => array_map(function ($product) {
                return [
                    'product_name'   => (string)$product['product_name'],
                    'description'    => (string)$product['description'],
                    'price'          => (float)$product['price'],
                    'stock_quantity' => (int)$product['stock_quantity'],
                    'image_url'      => (string)$product['image_url'],
                    'category_name'  => (string)$product['category_name']
                ];
            }, $products),
        ];
    }

    public function getProductDetails($product_id)
    {
        $product_id = $this->db->real_escape_string($product_id);

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, b.brand_id, c.category_name, 
                   p.size, p.color, p.image_url
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            JOIN brands b ON p.brand_id = b.brand_id
            WHERE product_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            return ['error' => 'Product not found'];
        }

        return [
            'product_name'   => (string)$product['product_name'],
            'description'    => (string)$product['description'],
            'price'          => (float)$product['price'],
            'stock_quantity' => (int)$product['stock_quantity'],
            'brand_id'       => (int)$product['brand_id'],
            'category_name'  => (string)$product['category_name'],
            'size'           => (string)$product['size'],
            'color'          => (string)$product['color'],
            'image_url'      => (string)$product['image_url']
        ];
    }

    public function filterProductsByCategory($category_name)
    {
        $category_name = $this->db->real_escape_string(strtolower($category_name));

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE LOWER(c.category_name) = ? AND p.stock_quantity > 0
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $category_name);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'category' => ['category_name' => ucfirst((string)$category_name)],
            'products' => array_map(function ($product) {
                return [
                    'product_name'   => (string)$product['product_name'],
                    'description'    => (string)$product['description'],
                    'price'          => (float)$product['price'],
                    'stock_quantity' => (int)$product['stock_quantity']
                ];
            }, $products)
        ];
    }

    public function sortProductsByCreationDate($order = 'new', $page = 1, $limit = 30)
    {
        $order = strtolower(trim($order));
        $sortOrder = $order === 'old' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $limit;

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            ORDER BY created_at $sortOrder
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $countResult = $this->db->query("SELECT COUNT(*) as total FROM products");
        $total = $countResult->fetch_assoc()['total'];

        return [
            'products' => array_map(function ($product) {
                return [
                    'product_name'   => (string)$product['product_name'],
                    'description'    => (string)$product['description'],
                    'price'          => (float)$product['price'],
                    'stock_quantity' => (int)$product['stock_quantity'],
                    'image_url'      => (string)$product['image_url']
                ];
            }, $products),
            'total'        => (int)$total,
            'page'         => (int)$page,
            'limit'        => (int)$limit,
            'total_pages'  => (int)ceil($total / $limit)
        ];
    }

    public function filterProductsByBrand($brand_name)
    {
        $brand_name = $this->db->real_escape_string(strtolower($brand_name));

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            JOIN brands b ON p.brand_id = b.brand_id
            WHERE LOWER(b.brand_name) = ? AND p.stock_quantity > 0
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $brand_name);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'brand' => ['brand_name' => ucfirst((string)$brand_name)],
            'products' => array_map(function ($product) {
                return [
                    'product_name'   => (string)$product['product_name'],
                    'description'    => (string)$product['description'],
                    'price'          => (float)$product['price'],
                    'stock_quantity' => (int)$product['stock_quantity'],
                    'image_url'      => (string)$product['image_url']
                ];
            }, $products)
        ];
    }

    public function filterProductsBySize($size)
    {
        $size = $this->db->real_escape_string($size);

        $sql = "
            SELECT p.product_name, p.description, p.price, p.stock_quantity, p.image_url
            FROM products p
            WHERE p.size = ? AND p.stock_quantity > 0
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $size);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'size' => ['size' => (string)$size],
            'products' => array_map(function ($product) {
                return [
                    'product_name'   => (string)$product['product_name'],
                    'description'    => (string)$product['description'],
                    'price'          => (float)$product['price'],
                    'stock_quantity' => (int)$product['stock_quantity'],
                    'image_url'      => (string)$product['image_url']
                ];
            }, $products)
        ];
    }
}
