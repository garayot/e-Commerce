<?php

require_once __DIR__ . '/../../../UserAuth/database/db.php';

use Database\Database;

class Cart {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getDbConnection() {
        return $this->db->getConnection();
    }

    public function addToCart($product_id, $quantity, $user_id) {
        $sql = "SELECT cart_uuid FROM cart WHERE user_uuid = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('s', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $cart_uuid = $result->fetch_assoc()['cart_uuid'];
        } else {
            $cart_uuid = $this->createNewCart($user_id);
        }

        $sql = "INSERT INTO cartitems (cart_uuid, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('sii', $cart_uuid, $product_id, $quantity);

        if ($stmt->execute()) {
            return ['message' => 'Product added to cart'];
        } else {
            return ['error' => 'Failed to add product to cart'];
        }
    }

    private function createNewCart($user_id) {
        $sql = "SELECT MAX(CAST(SUBSTRING(cart_uuid, 6) AS UNSIGNED)) AS max_cart_number FROM cart";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $next_cart_number = $result->fetch_assoc()['max_cart_number'] + 1;
        $cart_uuid = 'cart_' . str_pad($next_cart_number, 3, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO cart (user_uuid, cart_uuid) VALUES (?, ?)";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('ss', $user_id, $cart_uuid);
        $stmt->execute();

        return $cart_uuid;
    }

    public function getCartDetails($user_id) {
        // Query to get the cart ID and user ID
        $sql = "SELECT cart_uuid FROM cart WHERE user_uuid = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('s', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            return ['error' => 'Cart not found'];
        }
    
        $cart = $result->fetch_assoc();
        $cart_uuid = $cart['cart_uuid'];
    
        // Query to get cart items
        $sql = "SELECT p.product_id, p.product_name, ci.quantity, p.price, (p.price * ci.quantity) AS total
                FROM cartitems ci
                JOIN products p ON ci.product_id = p.product_id
                WHERE ci.cart_uuid = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('s', $cart_uuid);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
        // Calculate total items and total price
        $total_items = 0;
        $total_price = 0.0;
    
        foreach ($items as $item) {
            $total_items += $item['quantity'];
            $total_price += $item['total'];
        }
    
        return [
            'cart_id' => $cart_uuid,
            'user_id' => $user_id,
            'items' => $items,
            'total_items' => $total_items,
            'total_price' => $total_price,
        ];
    }
    

    public function removeFromCart($product_id) {
        $sql = "DELETE FROM cartitems WHERE product_id = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        return ['message' => 'Product removed from cart'];
    }

    public function clearCart($user_id) {
        $sql = "DELETE ci FROM cartitems ci
                JOIN cart c ON ci.cart_uuid = c.cart_uuid
                WHERE c.user_uuid = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('s', $user_id);
        $stmt->execute();
        return ['message' => 'Cart cleared'];
    }

    public function updateItemQuantity($cart_item_id, $quantity) {
        if ($quantity <= 0) {
            return ['error' => 'Invalid quantity'];
        }

        $sql = "UPDATE cartitems SET quantity = ? WHERE cart_item_id = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('ii', $quantity, $cart_item_id);
        $stmt->execute();
        return ['message' => 'Quantity updated'];
    }

    public function initiateCheckout($cart_id, $shipping_address, $user_uuid) {
        // Lock the cart to prevent further changes
        $sql = "UPDATE cart SET is_locked = 1, shipping_address = ? WHERE cart_uuid = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('ss', $shipping_address, $cart_id);
    
        if ($stmt->execute()) {
            // Retrieve cart details for order summary
            $sql = "SELECT p.product_id, p.product_name, ci.quantity, p.price, (p.price * ci.quantity) AS total
                    FROM cartitems ci
                    JOIN products p ON ci.product_id = p.product_id
                    WHERE ci.cart_uuid = ?";
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bind_param('s', $cart_id);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
            // Calculate total items and price
            $total_items = 0;
            $total_price = 0.0;
    
            foreach ($items as $item) {
                $total_items += $item['quantity'];
                $total_price += $item['total'];
            }
    
            // Generate a unique order UUID
            $order_uuid = $this->generateUUID();
    
            // Insert a new order record into the orders table
            $sql = "INSERT INTO orders (order_uuid, checkout_id, user_uuid, total_amount, shipping_address, status)
                    VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $this->db->conn->prepare($sql);
            $checkout_session_id = uniqid('checkout_');
            $stmt->bind_param('sssds', $order_uuid, $checkout_session_id, $user_uuid, $total_price, $shipping_address);
    
            if ($stmt->execute()) {
                // Insert items into the orderitems table
                $sql = "INSERT INTO orderitems (order_uuid, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->conn->prepare($sql);
    
                foreach ($items as $item) {
                    $stmt->bind_param('siid', $order_uuid, $item['product_id'], $item['quantity'], $item['price']);
                    $stmt->execute();
                }
    
                // Return order details along with the checkout session ID
                return [
                    'checkout_session_id' => $checkout_session_id,
                    'cart_id' => $cart_id,
                    'shipping_address' => $shipping_address,
                    'order_summary' => [
                        'items' => $items,
                        'total_items' => $total_items,
                        'total_price' => $total_price
                    ],
                    'available_payment_methods' => ['Credit Card', 'PayPal', 'Cash on Delivery']
                ];
            }
        }
    
        return ['error' => 'Failed to initiate checkout'];
    }    
    

// Add the generateUUID method
private function generateUUID() {
    return strtoupper(bin2hex(random_bytes(16))); // Generates a 32-character UUID
}

    
}
?>
