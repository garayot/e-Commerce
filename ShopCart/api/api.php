<?php

require_once './Controller/CartController.php';
require_once './Controller/PaymentController.php';
require_once './Controller/ReviewOrderController.php';
require_once './Controller/ConfirmOrderController.php';
require_once './Controller/CancelCheckoutController.php'; 
require_once './Controller/OrderController.php'; 

header('Content-Type: application/json');

$cartController = new CartController();
$paymentController = new PaymentController();
$reviewOrderController = new ReviewOrderController();
$confirmOrderController = new ConfirmOrderController();
$cancelCheckoutController = new CancelCheckoutController(); // Instantiate CancelCheckoutController
$orderController = new OrderController();
$method = $_SERVER['REQUEST_METHOD'];
$user_uuid = $_GET['user_uuid'] ?? null;

switch ($method) {
    case 'POST':
        // Handling add, checkout, payment selection, order confirmation, and cancellation
        if ($_GET['action'] === 'add') {
            echo json_encode($cartController->addToCart());
        } elseif ($_GET['action'] === 'checkout') {
            echo json_encode($cartController->initiateCheckout());
        } elseif ($_GET['action'] === 'choose_payment') {
            echo json_encode($paymentController->choosePaymentMethod());
        } elseif ($_GET['action'] === 'confirm') {
            $checkout_id = $_POST['checkout_id'] ?? null; // Get checkout_id from request body
            if ($checkout_id) {
                $response = $confirmOrderController->confirmOrder($checkout_id);
                echo $response; // Echo the response to the client
            } else {
                echo json_encode(['error' => 'Checkout ID is required']);
            }
        } elseif ($_GET['action'] === 'cancel') {  // Handle checkout cancellation
            $checkout_id = $_POST['checkout_id'] ?? null; // Get checkout_id from request body
            if ($checkout_id) {
                $response = $cancelCheckoutController->cancelCheckout($checkout_id);
                echo $response; // Echo the response to the client
            } else {
                echo json_encode(['error' => 'Checkout ID is required']);
            }
        }
        break;
    

        case 'GET':
            // Track order status
            if ($_GET['action'] === 'track_status') {
                $order_uuid = $_GET['order_uuid'] ?? null; // Get order_uuid from the request
                if ($order_uuid) {
                    $orderController->trackOrderStatus($order_uuid); // Call the method to track order status
                } else {
                    echo json_encode(['error' => 'order_uuid is required']);
                }
    
            } elseif ($_GET['action'] === 'review') {
                $checkout_id = $_GET['checkout_id'] ?? null; // Adjusted to 'checkout_id'
                if ($checkout_id) {
                    echo json_encode($reviewOrderController->reviewOrder($checkout_id));
                } else {
                    echo json_encode(['error' => 'checkout_id is required']);
                }
            } elseif ($_GET['action'] === 'view') {
                echo json_encode($cartController->getCartContents($user_uuid));
            }
            break;
        
            

    case 'DELETE':
        if ($_GET['action'] === 'remove') {
            echo json_encode($cartController->removeFromCart($_GET['product_id'] ?? null));
        } elseif ($_GET['action'] === 'clear') {
            echo json_encode($cartController->clearCart($user_uuid));
        }
        break;

    case 'PUT':
        if ($_GET['action'] === 'update') {
            $data = json_decode(file_get_contents("php://input"), true);
            echo json_encode($cartController->updateItemQuantity($_GET['cart_item_id'] ?? null, $data['quantity'] ?? 0));
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid request method']);
        break;
}
?>
