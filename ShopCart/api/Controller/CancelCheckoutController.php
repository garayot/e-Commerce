<?php

require_once __DIR__ . '/../Model/Order.php';

class CancelCheckoutController {
    private $order;

    public function __construct() {
        $this->order = new Order();
    }

    public function cancelCheckout($checkout_id) {
        $result = $this->order->cancelCheckout($checkout_id);
        echo json_encode($result);
    }
}
