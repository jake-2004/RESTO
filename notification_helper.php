<?php
require_once 'config/database.php';

/**
 * Create a notification for a user
 * 
 * @param int $user_id The user ID to send the notification to
 * @param string $message The notification message
 * @param string $type The notification type (booking_confirmed, booking_declined, preorder_confirmed, etc.)
 * @return bool True if notification was created successfully, false otherwise
 */
function create_notification($user_id, $message, $type = 'general') {
    global $conn;
    
    try {
        // Check if type column exists in Notifications table
        $check_type_column = $conn->query("SHOW COLUMNS FROM Notifications LIKE 'type'");
        $type_exists = ($check_type_column->num_rows > 0);
        
        // Insert notification based on table structure
        if ($type_exists) {
            $query = "INSERT INTO Notifications (user_id, message, type, is_read, created_at) 
                      VALUES (?, ?, ?, 0, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $user_id, $message, $type);
        } else {
            $query = "INSERT INTO Notifications (user_id, message, is_read, created_at) 
                      VALUES (?, ?, 0, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $user_id, $message);
        }
        
        $result = $stmt->execute();
        return $result;
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}
?> 