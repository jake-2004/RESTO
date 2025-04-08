<?php
require_once 'config/database.php';
require_once 'check_admin_session.php';

header('Content-Type: application/json');

try {
    // Get delivery count
    $deliveryStmt = $conn->query("SELECT COUNT(*) FROM homedeliveryorders");
    if (!$deliveryStmt) {
        throw new Exception("Failed to execute delivery count query: " . $conn->error);
    }
    $deliveryCount = $deliveryStmt->fetch_row()[0];

    // Get preorder count
    $preorderStmt = $conn->query("SELECT COUNT(*) FROM preorders");
    if (!$preorderStmt) {
        throw new Exception("Failed to execute preorder count query: " . $conn->error);
    }
    $preorderCount = $preorderStmt->fetch_row()[0];

    // Get reservation count
    $reservationStmt = $conn->query("SELECT COUNT(*) FROM TableBookings");
    if (!$reservationStmt) {
        throw new Exception("Failed to execute reservation count query: " . $conn->error);
    }
    $reservationCount = $reservationStmt->fetch_row()[0];

    echo json_encode([
        'success' => true,
        'delivery_count' => (int)$deliveryCount,
        'preorder_count' => (int)$preorderCount,
        'reservation_count' => (int)$reservationCount
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 