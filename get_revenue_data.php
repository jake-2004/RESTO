<?php
require_once 'check_admin_session.php';
require_once 'config/database.php';

// Temporary debug code - remove after testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Get total revenue from both home deliveries and preorders
    $query = "SELECT 
        COALESCE(
            (SELECT SUM(total_amount) FROM homedeliveryorders) +
            (SELECT SUM(total_amount) FROM preorders),
            0
        ) as total_revenue";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'total_revenue' => floatval($row['total_revenue'])
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch revenue data: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 