<?php

require_once __DIR__ . '/../../../UserAuth/database/db.php';

use Database\Database;

class Payment {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getDbConnection() {
        return $this->db->getConnection();
    }

    // Process the payment method
    public function processPayment($checkout_id, $payment_method) {
        // Validate payment method
        $valid_payment_methods = ['credit_card', 'gcash', 'COD'];
        if (!in_array($payment_method['type'], $valid_payment_methods)) {
            return ['error' => 'Invalid payment method'];
        }

        // Retrieve the order amount using checkout_id
        $amount = $this->getOrderAmountByCheckoutId($checkout_id);
        if ($amount === 0) {
            return ['error' => 'Order not found'];
        }

        // Update the payment method in the orders table
        $sql = "UPDATE orders SET payment_method = ? WHERE checkout_id = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('ss', $payment_method['type'], $checkout_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Process payment based on the selected method
            if ($payment_method['type'] === 'credit_card') {
                return $this->processCreditCardPayment($checkout_id, $amount);
            } elseif ($payment_method['type'] === 'gcash') {
                return $this->processGcashPayment($checkout_id, $amount);
            } elseif ($payment_method['type'] === 'COD') {
                return $this->processCODPayment($checkout_id, $amount);
            }
        }

        return ['error' => 'Failed to update payment method'];
    }

    // Get order amount by checkout_id
    public function getOrderAmountByCheckoutId($checkout_id) {
        $sql = "SELECT total_amount FROM orders WHERE checkout_id = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('s', $checkout_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            return $order['total_amount']; // Return the total amount for this order
        }

        return 0; // Return 0 if no order is found
    }

    // Example method to process credit card payment
    private function processCreditCardPayment($checkout_id, $amount) {
        // Simulate a successful credit card payment
        return ['message' => 'Payment successful via Credit Card', 'checkout_id' => $checkout_id, 'amount' => $amount];
    }

    // Example method to process GCash payment
    private function processGcashPayment($checkout_id, $amount) {
        // Simulate a successful GCash payment
        return ['message' => 'Payment successful via GCash', 'checkout_id' => $checkout_id, 'amount' => $amount];
    }

    // Example method to process Cash on Delivery payment
    private function processCODPayment($checkout_id, $amount) {
        // Simulate a successful COD payment
        return ['message' => 'Payment successful via Cash on Delivery', 'checkout_id' => $checkout_id, 'amount' => $amount];
    }
}
?>
