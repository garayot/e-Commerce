<?php

require_once __DIR__ . '/../Model/Cart.php';

class CartController {
    private $cartModel;

    public function __construct() {
        $this->cartModel = new Cart();
    }

    public function addToCart() {
        $data = json_decode(file_get_contents("php://input"), true);
        $user_uuid = $_GET['user_uuid'] ?? null;
    
        if (isset($data['product_id'], $data['quantity'], $user_uuid)) {
            return $this->cartModel->addToCart($data['product_id'], $data['quantity'], $user_uuid);
        }
    
        return ['error' => 'Missing parameters'];
    }    

    public function getCartContents($user_id) {
        return $this->cartModel->getCartDetails($user_id);
    }
    

    public function removeFromCart($product_id) {
        return $this->cartModel->removeFromCart($product_id);
    }

    public function clearCart($user_uuid) {
        return $this->cartModel->clearCart($user_uuid);
    }

    public function updateItemQuantity($cart_item_id, $quantity) {
        return $this->cartModel->updateItemQuantity($cart_item_id, $quantity);
    }

    public function initiateCheckout() {
        $data = json_decode(file_get_contents("php://input"), true);
        $user_uuid = $_GET['user_uuid'] ?? null;
        
        if (isset($data['cart_id'], $data['shipping_address'], $user_uuid)) {
            return $this->cartModel->initiateCheckout($data['cart_id'], $data['shipping_address'], $user_uuid);
        }
        
        return ['error' => 'Missing parameters'];
    }
    
}
?>
