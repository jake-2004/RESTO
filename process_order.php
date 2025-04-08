<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate input data
$allowed_order_types = ['preorder', 'delivery']; // Updated order types
if (!isset($_POST['order_type']) || !in_array($_POST['order_type'], $allowed_order_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order type']);
    exit();
}

$order_type = $_POST['order_type'];

// Validate dates and times based on order type
if ($order_type === 'preorder') {
    if (empty($_POST['pickup_datetime'])) {
        echo json_encode(['success' => false, 'message' => 'Pickup date and time are required']);
        exit();
    }
    $pickup_datetime = new DateTime($_POST['pickup_datetime']);
    // Validate pickup datetime is in the future
    if ($pickup_datetime < new DateTime()) {
        echo json_encode(['success' => false, 'message' => 'Pickup time must be in the future']);
        exit();
    }
    $pickup_date = $pickup_datetime->format('Y-m-d');
    $pickup_time = $pickup_datetime->format('H:i:s');
} elseif ($order_type === 'delivery') {
    if (empty($_POST['delivery_datetime'])) {
        echo json_encode(['success' => false, 'message' => 'Delivery time is required']);
        exit();
    }
    $delivery_datetime = new DateTime($_POST['delivery_datetime']);
    // Validate delivery time is in the future
    if ($delivery_datetime < new DateTime()) {
        echo json_encode(['success' => false, 'message' => 'Delivery time must be in the future']);
        exit();
    }
}

try {
    // Check cart items before starting transaction
    $cart_check = $conn->prepare("SELECT COUNT(*) FROM Cart WHERE user_id = ?");
    $cart_check->bind_param("i", $user_id);
    $cart_check->execute();
    $cart_count = $cart_check->get_result()->fetch_row()[0];
    
    if ($cart_count === 0) {
        throw new Exception("Cart is empty");
    }

    // Start transaction
    $conn->begin_transaction();

    // Get cart items and user details
    $cart_query = "SELECT c.*, m.price, m.name, u.address, u.phone 
                   FROM Cart c 
                   JOIN MenuItems m ON c.menu_id = m.menu_id 
                   JOIN Users u ON c.user_id = u.user_id 
                   WHERE c.user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calculate total and prepare order details
    $total = 0;
    $order_details = [];
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
        $order_details[] = $item['name'] . ' x ' . $item['quantity'];
    }
    $order_details_str = implode(', ', $order_details);

    // Insert into appropriate table based on order type
    $order_ids = [];
    if ($order_type === 'preorder') {
        foreach ($cart_items as $item) {
            $order_query = "INSERT INTO Preorders (user_id, menu_id, quantity, pickup_date, pickup_time, total_amount, payment_status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($order_query);
            $item_total = $item['price'] * $item['quantity'];
            $stmt->bind_param("iiissd", $user_id, $item['menu_id'], $item['quantity'], $pickup_date, $pickup_time, $item_total);
            $stmt->execute();
            if ($stmt->insert_id > 0) {
                $order_ids[] = $stmt->insert_id;
            }
        }
    } else {
        $delivery_time = $_POST['delivery_datetime'];
        foreach ($cart_items as $item) {
            $order_query = "INSERT INTO HomeDeliveryOrders (user_id, menu_id, quantity, delivery_time, address, total_amount, food_status, payment_method, payment_status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'ordered', 'razorpay', 'pending')";
            $stmt = $conn->prepare($order_query);
            $item_total = $item['price'] * $item['quantity'];
            $stmt->bind_param("iiissd", $user_id, $item['menu_id'], $item['quantity'], $delivery_time, $item['address'], $item_total);
            $stmt->execute();
            if ($stmt->insert_id > 0) {
                $order_ids[] = $stmt->insert_id;
            }
        }
    }

    // Clear cart
    $clear_cart = "DELETE FROM Cart WHERE user_id = ?";
    $stmt = $conn->prepare($clear_cart);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully', 
        'order_ids' => $order_ids,
        'order_type' => $order_type
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    error_log("Order processing error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing order: ' . $e->getMessage()]);
}

$conn->close();
?>