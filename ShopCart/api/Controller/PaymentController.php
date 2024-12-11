<?php

require_once __DIR__ . '/../Model/Payment.php';

class PaymentController {
    private $paymentModel;

    public function __construct() {
        $this->paymentModel = new Payment();
    }

    // Choose Payment Method
    // In the PaymentController class

public function choosePaymentMethod() {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Extract checkout_id and payment method from the request body
    $checkout_id = $data['checkout_id'] ?? null; 
    $payment_method = $data['payment_method'] ?? null;

    if ($checkout_id && is_array($payment_method) && isset($payment_method['type'])) {
        return $this->paymentModel->processPayment($checkout_id, $payment_method);
    }

    return ['error' => 'Missing parameters'];
}

}

?>
