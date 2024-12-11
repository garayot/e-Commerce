<?php

require_once __DIR__ . '/../Model/Order.php';

class OrderController {
    private $order;

    public function __construct() {
        $this->order = new Order();
    }

    // Method to track order status
public function trackOrderStatus($order_uuid) {
    // Call the method in the Order model to get order status
    $orderStatus = $this->order->getOrderStatusByUuid($order_uuid);

    if (isset($orderStatus['error'])) {
        // Return error if something goes wrong
        echo json_encode($orderStatus);
    } else {
        // Return the order status without the estimated_delivery attribute
        echo json_encode([
            'order_uuid' => $orderStatus['order_uuid'],
            'status' => $orderStatus['status']
        ]);
    }
}

}

