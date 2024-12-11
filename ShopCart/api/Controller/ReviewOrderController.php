<?php

require_once __DIR__ . '/../Model/Order.php';

class ReviewOrderController {
    private $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    public function reviewOrder($checkout_id) {
        // Get order details using checkout_id (which corresponds to order_uuid in orders table)
        $orderDetails = $this->orderModel->getOrderDetailsByCheckoutId($checkout_id);
        if (!$orderDetails) {
            return ['error' => 'Order not found'];
        }
    
        // Get order items using order_uuid from the order details
        $orderItems = $this->orderModel->getOrderItems($orderDetails['order_uuid']);  // Use order_uuid here
    
        // If no items, ensure total_items and total_price are calculated correctly
        $totalItems = 0;
        $totalPrice = 0;
        if (!empty($orderItems)) {
            $totalItems = array_sum(array_column($orderItems, 'quantity'));
            $totalPrice = array_sum(array_map(function($item) {
                return $item['quantity'] * $item['price'];
            }, $orderItems));
        }
    
        // Prepare the response
        return [
            'order_uuid' => $orderDetails['order_uuid'],
            'user_id' => $orderDetails['user_uuid'],
            'items' => $orderItems,
            'total_items' => $totalItems,
            'total_price' => $totalPrice,
            'shipping_address' => $orderDetails['shipping_address'],
            'payment_method' => ['type' => $orderDetails['payment_method']]
        ];
    }    
    
    
}

