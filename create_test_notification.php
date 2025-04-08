<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Create a test notification for the current user
    $message = "This is a test notification. Created at " . date('Y-m-d H:i:s');
    
    // Simple query without the type column
    $query = "INSERT INTO Notifications (user_id, message, is_read, created_at) 
              VALUES (?, ?, 0, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $message);
    
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Test notification created']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create notification']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 