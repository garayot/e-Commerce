<?php

require './controllers/ProductCatalogController.php';
require './controllers/SearchEngineController.php';
require './controllers/SellerProductController.php';
require './controllers/SellerSearchController.php';
require './controllers/AdminController.php';

use Api\Controllers\ProductCatalogController;
use Api\Controllers\SearchEngineController;
use Api\Controllers\SellerProductController;
use Api\Controllers\SellerSearchController;
use Api\Controllers\AdminController;

class Router
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function route($action, $data)
    {
        switch ($action) {
            // Product Catalog Actions
            case 'products':
                return $this->getAllProducts($data['page']);
            case 'product':
                return $this->getProduct($data['product_id']);
            case 'filterCat':
                return $this->filterByCategory($data['category']);
            case 'filterBrand':
                return $this->filterProductsByBrand($data['brand']); 
            case 'filterSize':
                return $this->filterProductsBySize($data['size']); 
            case 'sortDate':
                return $this->sortByDate($data['order'], $data['page'] ?? 1, $data['limit'] ?? 30);

            // Product Search Actions
            case 'search':
                return $this->searchByName($data['query']);
            case 'searchRange':
                return $this->searchByPriceRange($data['query'], $data['min_price'], $data['max_price']);
            case 'searchByBrand':
                return $this->searchByBrand($data['brand']);
            case 'searchBySize':
                return $this->searchBySize($data['query'], $data['size']);

            // Seller Product Actions
            case 'addProduct':
                return $this->addProduct($data);
            case 'listProducts':
                return $this->getListedProducts($data);
            case 'delProduct':
                return $this->deleteProduct($data['product_id']);
            case 'updProduct':
                return $this->updateProduct($data['product_id']);
            case 'sellerProduct':
                return $this->sellerGetProduct($data['product_id']);
            case 'sellerfilterCat':
                return $this->sellerfilterByCategory($data['category']);
            case 'sellerfilterBrand':
                return $this->sellerfilterProductsByBrand($data['brand']); 
            case 'sellerfilterSize':
                return $this->sellerfilterProductsBySize($data['size']); 

            // Seller Search Actions
            case 'sellerSearch':
                return $this->sellerSearchByName($data['query']);
            case 'sellerSearchCat':
                return $this->sellerSearchByCategory($data['category']);
            case 'sellerRange':
                return $this->sellerSearchByRange($data['query'], $data['min_price'], $data['max_price']);

            // Admin Product Actions
            case 'admindel':
                return $this->adminDeleteProduct($data['id']);
            case 'adminprod':
                return $this->adminGetProductDetails($data['id']);
            case 'adminProdPerSeller':
                return $this->adminShowProductsPerSeller($data['id']);

            default:
                return ['error' => 'Invalid action'];
        }
    }

    // Product Catalog Actions
    private function getAllProducts($page)
    {
        $controller = new ProductCatalogController($this->db);
        return $controller->getAllProducts($page);
    }

    private function getProduct($product_id)
    {
        $controller = new ProductCatalogController($this->db);
        return $controller->getProductDetails($product_id);
    }

    private function filterByCategory($category)
    {
        $controller = new ProductCatalogController($this->db);
        return $controller->filterProductsByCategory($category);
    }

    private function filterProductsByBrand($brand_name)
    {
        $controller = new ProductCatalogController($this->db);
        return $controller->filterProductsByBrand($brand_name);
    }

    private function filterProductsBySize($size)
    {
        $controller = new ProductCatalogController($this->db);
        return $controller->filterProductsBySize($size);
    }

    private function sortByDate($order, $page, $limit)
    {
        $controller = new ProductCatalogController($this->db);
        return $controller->sortProductsByCreationDate($order, $page, $limit);
    }

    // Product Search Actions
    private function searchByName($query)
    {
        $controller = new SearchEngineController($this->db);
        return $controller->searchProductsByName($query);
    }

    private function searchByPriceRange($query, $min_price, $max_price)
    {
        $controller = new SearchEngineController($this->db);
        return $controller->searchProductsWithPriceRange($query, $min_price, $max_price);
    }

    private function searchByBrand($brand_name)
    {
        $controller = new SearchEngineController($this->db);
        return $controller->searchProductsByBrand($brand_name);
    }

    private function searchBySize($query, $size)
    {
        $controller = new SearchEngineController($this->db);
        return $controller->searchProductsBySize($query, $size);
    }

    // Seller Product Actions
    private function addProduct($data)
    {
        $controller = new SellerProductController($this->db);
        return $controller->addNewProduct($data);
    }

    private function getListedProducts($data)
    {
        $controller = new SellerProductController($this->db);
        return $controller->getListedProducts($data);
    }

    private function deleteProduct($product_id)
    {
        $controller = new SellerProductController($this->db);
        return $controller->deleteProduct($product_id);
    }

    private function updateProduct($product_id)
    {
        $controller = new SellerProductController($this->db);
        return $controller->updateProduct($product_id);
    }

    private function sellerGetProduct($product_id)
    {
        $controller = new SellerProductController($this->db);
        return $controller->getProductDetails($product_id);
    }
    private function sellerfilterByCategory($category)
    {
        $controller = new ProductCatalogController($this->db);
        return $controller->filterProductsByCategory($category);
    }

    private function sellerfilterProductsByBrand($brand_name)
    {
        $controller = new ProductCatalogController($this->db);
        return $controller->filterProductsByBrand($brand_name);
    }

    private function sellerfilterProductsBySize($size)
    {
        $controller = new ProductCatalogController($this->db);
        return $controller->filterProductsBySize($size);
    }

    // Seller Search Actions
    private function sellerSearchByName($query)
    {
        $controller = new SellerSearchController($this->db);
        return $controller->searchProductsByName($query);
    }

    private function sellerSearchByCategory($category)
    {
        $controller = new SellerSearchController($this->db);
        return $controller->searchProductsByCategory($category);
    }

    private function sellerSearchByRange($query, $min_price, $max_price)
    {
        $controller = new SellerSearchController($this->db);
        return $controller->searchProductsWithPriceRange($query, $min_price, $max_price);
    }

    // Admin Product Actions
    private function adminDeleteProduct($product_id)
    {
        $controller = new AdminController($this->db);
        return $controller->deleteProduct($product_id);
    }

    private function adminShowProductsPerSeller($sellerId)
    {
        $controller = new AdminController($this->db);
        return $controller->showProductsPerSeller($sellerId);
    }

    private function adminGetProductDetails($product_id)
    {
        $controller = new AdminController($this->db);
        return $controller->getProductDetails($product_id);
    }
}
