<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to remove items']);
    exit;
}

if (!isset($_POST['cart_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete the cart item
    $delete_sql = "DELETE FROM Cart WHERE cart_id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare delete: ' . $conn->error);
    }

    $stmt->bind_param("ii", $cart_id, $user_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to remove item: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Cart item not found');
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart'
    ]);

} catch (Exception $e) {
    // Rollback transaction
    if ($conn && $conn->ping()) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
