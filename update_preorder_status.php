<?php
require_once 'check_staff_session.php';
require_once 'config/database.php';
require_once 'notification_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preorder_id']) && isset($_POST['order_status'])) {
    $preorder_id = $_POST['preorder_id'];
    $order_status = $_POST['order_status'];
    
    try {
        // Update the preorder status
        $update_query = "UPDATE preorders SET order_status = ? WHERE preorder_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $order_status, $preorder_id);
        
        if ($update_stmt->execute()) {
            // Get user details for notification
            $user_query = "SELECT p.user_id, u.email, u.user_name, m.name AS item_name, p.pickup_date, p.pickup_time 
                          FROM preorders p 
                          JOIN users u ON p.user_id = u.user_id 
                          JOIN menuItems m ON p.menu_id = m.menu_id
                          WHERE p.preorder_id = ?";
            $user_stmt = $conn->prepare($user_query);
            $user_stmt->bind_param("i", $preorder_id);
            $user_stmt->execute();
            $result = $user_stmt->get_result();
            
            if ($user_data = $result->fetch_assoc()) {
                $user_id = $user_data['user_id'];
                
                // Only send notifications for cancelled, ready, or completed status
                if ($order_status == 'cancelled' || $order_status == 'ready' || $order_status == 'completed') {
                    // Create appropriate notification message based on status
                    if ($order_status == 'cancelled') {
                        $message = "Your preorder #$preorder_id has been cancelled.";
                    } elseif ($order_status == 'ready') {
                        $message = "Your preorder #$preorder_id is ready for pickup!";
                    } else { // completed
                        $message = "Your preorder #$preorder_id has been completed. Thank you for your order!";
                    }
                    
                    error_log("DEBUG: Sending notification for status: $order_status");
                    
                    // Try to insert into Notifications table
                    try {
                        $notification_query = "INSERT INTO Notifications (user_id, message, is_read, created_at) 
                                              VALUES (?, ?, 0, NOW())";
                        $notification_stmt = $conn->prepare($notification_query);
                        
                        if (!$notification_stmt) {
                            error_log("DEBUG: Failed to prepare statement for Notifications: " . $conn->error);
                        } else {
                            $notification_stmt->bind_param("is", $user_id, $message);
                            
                            if (!$notification_stmt->execute()) {
                                error_log("DEBUG: Failed to insert into Notifications: " . $notification_stmt->error);
                            } else {
                                error_log("DEBUG: Successfully inserted into Notifications table");
                            }
                        }
                    } catch (Exception $e) {
                        error_log("DEBUG: Exception when inserting into Notifications: " . $e->getMessage());
                    }
                    
                    // Send email notification
                    $to = $user_data['email'];
                    $subject = "Preorder Status Update - #$preorder_id";
                    $email_message = "Dear " . $user_data['user_name'] . ",\n\n";
                    $email_message .= $message . "\n\n";
                    $email_message .= "Order Details:\n";
                    $email_message .= "Item: " . $user_data['item_name'] . "\n";
                    $email_message .= "Pickup Date: " . $user_data['pickup_date'] . "\n";
                    $email_message .= "Pickup Time: " . $user_data['pickup_time'] . "\n\n";
                    
                    if ($order_status == 'ready') {
                        $email_message .= "Please come to our restaurant to pick up your order.\n\n";
                    }
                    
                    $email_message .= "Thank you for choosing our service!\n\n";
                    $email_message .= "Regards,\nResto Team";
                    
                    $headers = "From: noreply@resto.com";
                    
                    mail($to, $subject, $email_message, $headers);
                } else {
                    error_log("DEBUG: No notification sent for status: $order_status");
                }
            } else {
                error_log("No user data found for preorder ID: $preorder_id");
            }
            
            $_SESSION['message'] = "Preorder status updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Failed to update preorder status.";
            $_SESSION['message_type'] = "danger";
            error_log("Failed to update preorder status: " . $update_stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
        error_log("Exception in update_preorder_status.php: " . $e->getMessage());
    }
    
    // Redirect back to the staff dashboard
    header("Location: staff.php");
    exit();
} else {
    error_log("Invalid request to update_preorder_status.php");
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = "danger";
    header("Location: staff.php");
    exit();
}
?> 