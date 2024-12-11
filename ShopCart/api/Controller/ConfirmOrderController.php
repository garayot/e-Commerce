<?php

require_once __DIR__ . '/../Model/Order.php';

class ConfirmOrderController {

    private $order;

    public function __construct() {
        $this->order = new Order();
    }

    // Confirm the order and log transaction details
    public function confirmOrder($checkout_id) {
        // Call the confirmOrder method from the Order model to process the order
        $orderDetails = $this->order->confirmOrder($checkout_id);

        if (isset($orderDetails['error'])) {
            // Return error if something goes wrong
            return json_encode($orderDetails);
        } else {
            // Return successful confirmation with order and transaction details
            return json_encode([
                'message' => $orderDetails['message'],
                'order_details' => $orderDetails['order_details'],
                'transaction_details' => $orderDetails['transaction_details']
            ]);
        }
    }
}
