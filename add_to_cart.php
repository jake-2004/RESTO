<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

// Validate input
if (!isset($_POST['menu_id']) || !is_numeric($_POST['menu_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid menu item']);
    exit();
}

$menu_id = (int)$_POST['menu_id'];
$user_id = $_SESSION['user_id'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate quantity
if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if menu item exists
    $check_menu = $conn->prepare("SELECT menu_id FROM MenuItems WHERE menu_id = ?");
    $check_menu->bind_param("i", $menu_id);
    $check_menu->execute();
    $menu_result = $check_menu->get_result();

    if ($menu_result->num_rows === 0) {
        throw new Exception("Menu item not found");
    }

    // Check if item already exists in cart
    $check_cart = $conn->prepare("SELECT cart_id, quantity FROM Cart WHERE user_id = ? AND menu_id = ?");
    $check_cart->bind_param("ii", $user_id, $menu_id);
    $check_cart->execute();
    $cart_result = $check_cart->get_result();

    if ($cart_result->num_rows > 0) {
        // Update existing cart item
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        $update_cart = $conn->prepare("UPDATE Cart SET quantity = ? WHERE cart_id = ?");
        $update_cart->bind_param("ii", $new_quantity, $cart_item['cart_id']);
        
        if (!$update_cart->execute()) {
            throw new Exception("Failed to update cart");
        }
    } else {
        // Add new cart item
        $insert_cart = $conn->prepare("INSERT INTO Cart (user_id, menu_id, quantity) VALUES (?, ?, ?)");
        $insert_cart->bind_param("iii", $user_id, $menu_id, $quantity);
        
        if (!$insert_cart->execute()) {
            throw new Exception("Failed to add item to cart");
        }
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Item added to cart successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Error in add_to_cart.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error adding item to cart. Please try again.']);
}

// Close connection
$conn->close();
?>
