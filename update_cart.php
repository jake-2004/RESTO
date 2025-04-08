<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

header('Content-Type: application/json');

// Debug logging
error_log("Update cart request received: " . json_encode($_POST));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'User not logged in'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate input
if (!isset($_POST['cart_id']) || !isset($_POST['action'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request parameters'
    ]);
    exit();
}

$cart_id = intval($_POST['cart_id']);
$action = $_POST['action'];

try {
    // First, verify the cart item belongs to the current user
    $check_query = "SELECT cart_id, menu_id, quantity 
                    FROM Cart 
                    WHERE cart_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $cart_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Cart item not found or does not belong to user');
    }
    
    $cart_item = $result->fetch_assoc();
    $current_quantity = $cart_item['quantity'];

    // Determine new quantity based on action
    if ($action === 'increase') {
        $new_quantity = $current_quantity + 1;
    } elseif ($action === 'decrease') {
        $new_quantity = max(1, $current_quantity - 1); // Prevent quantity from going below 1
    } else {
        throw new Exception('Invalid action');
    }

    // Update quantity in database
    $update_query = "UPDATE Cart SET quantity = ? WHERE cart_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ii", $new_quantity, $cart_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update cart quantity: ' . $conn->error);
    }

    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Quantity updated successfully',
        'new_quantity' => $new_quantity
    ]);

} catch (Exception $e) {
    // Log the error
    error_log('Cart Update Error: ' . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    // Close statements and connection if needed
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
    if (isset($conn)) {
        $conn->close();
    }
}
