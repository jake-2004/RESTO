<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // First, check if the 'type' column exists in the Notifications table
    $checkTypeColumn = $conn->query("SHOW COLUMNS FROM Notifications LIKE 'type'");
    $typeColumnExists = ($checkTypeColumn->num_rows > 0);
    
    // Prepare the query based on whether the type column exists
    if ($typeColumnExists) {
        $query = "SELECT notification_id as id, message, type, is_read, created_at 
                  FROM Notifications 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT 20";
    } else {
        $query = "SELECT notification_id as id, message, is_read, created_at 
                  FROM Notifications 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT 20";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        // If type column doesn't exist, add a default type
        if (!$typeColumnExists) {
            $row['type'] = 'general';
        }
        $notifications[] = $row;
    }
    
    echo json_encode(['notifications' => $notifications]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 