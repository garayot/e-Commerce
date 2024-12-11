<?php

require_once __DIR__ . '/../../../UserAuth/database/db.php';

use Database\Database;

class Order {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Method to retrieve order details by checkout_id
    public function getOrderDetailsByCheckoutId($checkout_id) {
        $sql = "SELECT 
                    o.order_uuid, 
                    o.user_uuid, 
                    o.shipping_address, 
                    o.payment_method, 
                    o.total_amount
                FROM orders o
                WHERE o.checkout_id = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('s', $checkout_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getOrderItems($order_uuid) {
        $sql = "SELECT 
                    oi.order_item_id, 
                    oi.order_uuid, 
                    oi.product_id, 
                    oi.quantity, 
                    oi.price
                FROM orderitems oi
                WHERE oi.order_uuid = ?"; // Using order_uuid here instead of checkout_id
    
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('s', $order_uuid);  // Binding order_uuid here
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
      
    

    // Method to confirm the order
    public function confirmOrder($checkout_id) {
        // Step 1: Retrieve the order details first
        $orderDetails = $this->getOrderDetailsByCheckoutId($checkout_id);
    
        if ($orderDetails) {
            // Step 2: Prepare for transaction
            $paymentStatus = 'completed'; // Set payment status to 'completed' for confirmed orders
            $transactionAmount = $orderDetails['total_amount'];
            $userUuid = $orderDetails['user_uuid'];
            $orderUuid = $orderDetails['order_uuid'];
            $paymentMethod = $orderDetails['payment_method'];
    
            // Step 3: Insert into payment table
            $paymentSql = "INSERT INTO payment (order_uuid, payment_method, amount, status) 
                           VALUES (?, ?, ?, ?)";
            $paymentStmt = $this->db->conn->prepare($paymentSql);
            $paymentStmt->bind_param('ssds', $orderUuid, $paymentMethod, $transactionAmount, $paymentStatus);
            $paymentStmt->execute();
            $paymentId = $paymentStmt->insert_id; // Get the payment_id
    
            // Step 4: Insert into transactiondetails table
            $sql = "INSERT INTO transactiondetails 
                    (user_uuid, order_uuid, payment_id, transaction_amount, status) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bind_param('ssdis', $userUuid, $orderUuid, $paymentId, $transactionAmount, $paymentStatus);
    
            if ($stmt->execute()) {
                // Capture the transaction_id immediately after executing the query
                $transactionId = $stmt->insert_id;
    
                // Step 5: Return the order details including the transaction information
                return [
                    'message' => 'Order confirmed successfully',
                    'order_details' => $orderDetails,
                    'transaction_details' => [
                        'transaction_id' => $transactionId, // Use the fetched transaction_id
                        'status' => $paymentStatus
                    ]
                ];
            }
    
            return ['error' => 'Failed to insert into transactiondetails'];
        }
    
        return ['error' => 'Order not found'];
    }
    

    public function cancelCheckout($checkout_id) {
        // Step 1: Retrieve the order details to get the order_uuid and current payment status
        $sql = "SELECT order_uuid, status FROM orders WHERE checkout_id = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('s', $checkout_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if (!$order) {
            return ['error' => 'Checkout ID does not exist'];
        }
        
        // Step 2: Check if the order status is 'shipped' or 'delivered'
        if ($order['status'] === 'shipped' || $order['status'] === 'delivered') {
            return ['error' => 'This order cannot be cancelled because it has already been shipped or delivered'];
        }
    
        // Step 3: Check if the order has an associated payment
        $paymentStatusSql = "SELECT status FROM payment WHERE order_uuid = ?";
        $paymentStmt = $this->db->conn->prepare($paymentStatusSql);
        $paymentStmt->bind_param('s', $order['order_uuid']);
        $paymentStmt->execute();
        $paymentResult = $paymentStmt->get_result();
        $payment = $paymentResult->fetch_assoc();
        
        if (!$payment) {
            return ['error' => 'Payment record not found'];
        }
        
        // Step 4: Check if the payment has already been cancelled
        if ($payment['status'] === 'cancelled') {
            return ['error' => 'This checkout payment has already been cancelled'];
        }
        
        // Begin a transaction to ensure both updates are consistent
        $this->db->conn->begin_transaction();
        
        try {
            // Step 5: Update the payment status to 'cancelled' in the payment table
            $updatePaymentSql = "UPDATE payment SET status = 'cancelled' WHERE order_uuid = ?";
            $updatePaymentStmt = $this->db->conn->prepare($updatePaymentSql);
            $updatePaymentStmt->bind_param('s', $order['order_uuid']);
            $updatePaymentStmt->execute();
            
            // Check if payment status update was successful
            if ($updatePaymentStmt->affected_rows === 0) {
                throw new Exception('Payment status update failed.');
            }
            
            // Step 6: Update the transactiondetails status to 'failed' (instead of 'cancelled')
            $updateTransactionSql = "UPDATE transactiondetails SET status = 'failed' WHERE order_uuid = ?";
            $updateTransactionStmt = $this->db->conn->prepare($updateTransactionSql);
            $updateTransactionStmt->bind_param('s', $order['order_uuid']);
            $updateTransactionStmt->execute();
            
            // Check if transactiondetails status update was successful
            if ($updateTransactionStmt->affected_rows === 0) {
                throw new Exception('Transactiondetails status update failed.');
            }
            
            // Commit the transaction if both updates succeed
            $this->db->conn->commit();
            
            // Step 7: Return a success message
            return ['message' => 'Checkout payment and transaction details cancelled successfully'];
        } catch (Exception $e) {
            // Rollback the transaction if any step fails
            $this->db->conn->rollback();
            return ['error' => 'Failed to cancel checkout payment and transaction details: ' . $e->getMessage()];
        }
    }
    
    
    
    // Method to retrieve the order status by order_uuid
    public function getOrderStatusByUuid($order_uuid) {
        // SQL query to fetch order status by order_uuid
        $sql = "SELECT order_uuid, status FROM orders WHERE order_uuid = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param('s', $order_uuid);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Fetch order details
        if ($order = $result->fetch_assoc()) {
            return $order; // Return order details without estimated_delivery
        }
    
        return ['error' => 'Order not found'];
    }
}
