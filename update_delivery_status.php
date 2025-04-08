<?php
require_once 'check_staff_session.php';
require_once 'config/database.php';
require_once 'notification_helper.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $delivery_id = $_POST['delivery_id'];
    $user_id = $_POST['user_id'];
    $phone = $_POST['phone'];
    $current_status = $_POST['current_status'];
    $new_status = $_POST['new_status'];
    $message = $_POST['message'];
    $delivery_time = $_POST['delivery_time'];
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update all menu items for this delivery (same user and delivery time)
        $update_query = "UPDATE HomeDeliveryOrders 
                        SET food_status = ? 
                        WHERE user_id = ? 
                        AND delivery_time = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sis", $new_status, $user_id, $delivery_time);
        $stmt->execute();
        
        // Send SMS notification if status changed
        if ($current_status !== $new_status) {
            sendSMS($phone, $message);
            
            // Create an appropriate notification message based on the new status
            $formatted_time = date('F j, Y \a\t g:i A', strtotime($delivery_time));
            
            switch ($new_status) {
                case 'confirmed':
                    $notification_message = "Your delivery order for {$formatted_time} has been confirmed.";
                    $notification_type = "delivery_confirmed";
                    break;
                case 'out_for_delivery':
                    $notification_message = "Your order is out for delivery! Expected arrival: {$formatted_time}.";
                    $notification_type = "delivery_out";
                    break;
                case 'delivered':
                    $notification_message = "Your order has been delivered. Enjoy your meal!";
                    $notification_type = "delivery_completed";
                    break;
                case 'cancelled':
                    $notification_message = "Your delivery order for {$formatted_time} has been cancelled.";
                    $notification_type = "delivery_cancelled";
                    break;
                default:
                    $notification_message = "Your delivery order status has been updated to: {$new_status}.";
                    $notification_type = "delivery_update";
            }
            
            // Create notification
            create_notification($user_id, $notification_message, $notification_type);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        $_SESSION['message'] = "Delivery status updated successfully.";
        $_SESSION['message_type'] = "success";
        
        // Redirect back to the delivery management page
        header("Location: view_deliveries.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $_SESSION['message'] = "Error updating delivery status: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
        header("Location: view_deliveries.php");
        exit;
    }
}

// Optimized SMS function for speed
function sendSMS($to, $message) {
    // Format the phone number properly
    $to = formatPhoneNumber($to);
    
    // Make sure we have a valid phone number
    if (empty($to)) {
        error_log("Invalid phone number for SMS");
        return false;
    }
    
    // Keep messages short for faster delivery
    $message = substr($message, 0, 160);
    
    try {
        // Placeholder for actual SMS API call
        // Example with cURL (you'd replace this with your actual SMS provider's API)
        /*
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://your-sms-provider.com/api/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'phone' => $to,
            'message' => $message,
            'api_key' => 'YOUR_API_KEY'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout for speed
        $response = curl_exec($ch);
        curl_close($ch);
        */
        
        error_log("FAST SMS sent to $to with message: $message");
        return true;
    } catch (Exception $e) {
        error_log("SMS sending error: " . $e->getMessage());
        return false;
    }
}

function getStatusMessage($status) {
    $messages = [
        'ordered' => 'Your order has been received and is being processed.',
        'preparing' => 'Your order is now being prepared in our kitchen.',
        'ready' => 'Your order is ready for delivery.',
        'out_for_delivery' => 'Your order is out for delivery and will arrive soon.',
        'delivered' => 'Your order has been delivered. Enjoy your meal!',
        'cancelled' => 'Your order has been cancelled.'
    ];
    return $messages[$status] ?? 'Your order status has been updated.';
}

function formatPhoneNumber($phone) {
    // Remove any non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add country code if not present (assuming Indian numbers)
    if (strlen($phone) === 10) {
        return '+91' . $phone;
    }
    
    return $phone;
}
?> 