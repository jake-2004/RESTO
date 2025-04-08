<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Set content type to JSON for all responses
header('Content-Type: application/json');

// Debug log function
function logError($message) {
    error_log('[Payment Debug] ' . $message);
    return $message;
}

// Validate request
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => logError('User not logged in')]);
    exit();
}

if (!isset($_POST['razorpay_payment_id'])) {
    echo json_encode(['success' => false, 'message' => logError('Payment ID missing')]);
    exit();
}

if (!isset($_POST['order_type']) || !isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => logError('Order details missing')]);
    exit();
}

// Get user and payment details
$user_id = $_SESSION['user_id'];
$payment_id = $_POST['razorpay_payment_id'];
$order_type = $_POST['order_type'];
$order_id = $_POST['order_id'];

logError("Processing payment: user_id=$user_id, payment_id=$payment_id, order_type=$order_type, order_id=$order_id");

try {
    // Start transaction
    $conn->begin_transaction();
    
    // If we have payment_order in session, use that
    if (isset($_SESSION['payment_order'])) {
        $payment_order = $_SESSION['payment_order'];
        $amount = $payment_order['amount'] / 100; // Convert back from paise to rupees
        logError("Using session payment_order. Amount: $amount");
    } else {
        // Otherwise fetch order details from the database
        logError("No session payment_order, fetching from database");
        if ($order_type == 'delivery') {
            // Calculate total from menu prices directly
            $query = "SELECT SUM(m.price * d.quantity) as total
                      FROM HomeDeliveryOrders d 
                      JOIN MenuItems m ON d.menu_id = m.menu_id 
                      WHERE d.user_id = ? AND d.delivery_id = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $user_id, $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order_data = $result->fetch_assoc();
            
        } else if ($order_type == 'preorder') {
            // First get the specific preorder's date and time
            $get_order_query = "SELECT pickup_date, pickup_time, DATE(created_at) as order_date 
                               FROM Preorders 
                               WHERE preorder_id = ? AND user_id = ? LIMIT 1";
            $stmt = $conn->prepare($get_order_query);
            if (!$stmt) {
                throw new Exception("Get order query preparation failed: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order_data = $result->fetch_assoc();
            
            if (!$order_data) {
                throw new Exception("Order not found: type=$order_type, id=$order_id");
            }
            
            // Calculate total directly from menu prices
            $query = "SELECT SUM(m.price * p.quantity) as total
                      FROM Preorders p 
                      JOIN MenuItems m ON p.menu_id = m.menu_id 
                      WHERE p.user_id = ? 
                      AND p.pickup_date = ? 
                      AND p.pickup_time = ? 
                      AND DATE(p.created_at) = ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Sum query preparation failed: " . $conn->error);
            }
            
            $stmt->bind_param("isss", $user_id, $order_data['pickup_date'], 
                             $order_data['pickup_time'], $order_data['order_date']);
            $stmt->execute();
            $result = $stmt->get_result();
            $sum_data = $result->fetch_assoc();
            
            if (!$sum_data) {
                throw new Exception("Failed to calculate total amount");
            }
            
            $order_data = $sum_data;
        } else {
            throw new Exception("Invalid order type: $order_type");
        }
        
        if (!$order_data) {
            throw new Exception("Order data not found: type=$order_type, id=$order_id");
        }
        
        $amount = $order_data['total'];
        logError("Fetched amount from database: $amount");
        
        // Check if amount is valid
        if (!$amount || $amount <= 0) {
            throw new Exception("Invalid order amount: $amount");
        }
    }
    
    // Insert payment record once for the entire transaction
    $payment_query = "INSERT INTO payments (user_id, payment_id, amount, payment_method, status, created_at) 
                     VALUES (?, ?, ?, 'razorpay', 'completed', NOW())";
    $stmt = $conn->prepare($payment_query);
    $stmt->bind_param("isd", $user_id, $payment_id, $amount);
    $stmt->execute();
    $payment_db_id = $conn->insert_id; // Get the auto-generated payment ID
    
    logError("Created payment record with ID: $payment_db_id for amount: $amount");
    
    // Now update all relevant items with this single payment ID
    
    if ($order_type == 'delivery') {
        logError("Updating delivery order status");
        
        // Get reference delivery order details
        $get_reference = "SELECT delivery_time, created_at 
                         FROM HomeDeliveryOrders 
                         WHERE delivery_id = ? AND user_id = ? LIMIT 1";
        $stmt = $conn->prepare($get_reference);
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reference = $result->fetch_assoc();
        
        if ($reference) {
            // Update all delivery orders from the same batch (created around the same time)
            $update_batch = "UPDATE HomeDeliveryOrders 
                            SET payment_status = 'paid', payment_id = ? 
                            WHERE user_id = ? 
                            AND DATE(created_at) = DATE(?) 
                            AND HOUR(created_at) = HOUR(?) 
                            AND MINUTE(created_at) = MINUTE(?)
                            AND payment_status = 'pending'";
            
            $stmt = $conn->prepare($update_batch);
            $stmt->bind_param("iisss", $payment_db_id, $user_id, 
                             $reference['created_at'], $reference['created_at'], $reference['created_at']);
            $stmt->execute();
            $updated_rows = $stmt->affected_rows;
            
            logError("Updated batch of delivery orders - Affected rows: " . $updated_rows);
            
            if ($updated_rows == 0) {
                // Fallback to just updating the specific order
                $update_single = "UPDATE HomeDeliveryOrders 
                                 SET payment_status = 'paid', payment_id = ? 
                                 WHERE delivery_id = ? AND user_id = ? AND payment_status = 'pending'";
                $stmt = $conn->prepare($update_single);
                $stmt->bind_param("iii", $payment_db_id, $order_id, $user_id);
                $stmt->execute();
                $updated_rows = $stmt->affected_rows;
                logError("Fallback update for delivery ID $order_id - Affected rows: " . $updated_rows);
            }
        } else {
            logError("Warning: Reference delivery order not found");
        }
    } else if ($order_type == 'preorder') {
        logError("Updating preorder status");
        
        // Get reference preorder details
        $get_reference = "SELECT created_at, pickup_date, pickup_time 
                         FROM Preorders 
                         WHERE preorder_id = ? AND user_id = ? LIMIT 1";
        $stmt = $conn->prepare($get_reference);
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reference = $result->fetch_assoc();
        
        if ($reference) {
            // Update all preorders from the same batch (created around the same time)
            $update_batch = "UPDATE Preorders 
                            SET payment_status = 'paid', payment_id = ? 
                            WHERE user_id = ? 
                            AND DATE(created_at) = DATE(?) 
                            AND HOUR(created_at) = HOUR(?) 
                            AND MINUTE(created_at) = MINUTE(?)
                            AND payment_status = 'pending'";
            
            $stmt = $conn->prepare($update_batch);
            $stmt->bind_param("iisss", $payment_db_id, $user_id, 
                             $reference['created_at'], $reference['created_at'], $reference['created_at']);
            $stmt->execute();
            $updated_rows = $stmt->affected_rows;
            
            logError("Updated batch of preorders - Affected rows: " . $updated_rows);
            
            if ($updated_rows == 0) {
                // Fallback to just updating the specific order
                $update_single = "UPDATE Preorders 
                                 SET payment_status = 'paid', payment_id = ? 
                                 WHERE preorder_id = ? AND user_id = ? AND payment_status = 'pending'";
                $stmt = $conn->prepare($update_single);
                $stmt->bind_param("iii", $payment_db_id, $order_id, $user_id);
                $stmt->execute();
                $updated_rows = $stmt->affected_rows;
                logError("Fallback update for preorder ID $order_id - Affected rows: " . $updated_rows);
            }
        } else {
            logError("Warning: Reference preorder not found");
        }
    }
    
    // Commit transaction
    $conn->commit();
    logError("Transaction committed successfully");
    
    // Clear payment session data if it exists
    if (isset($_SESSION['payment_order'])) {
        unset($_SESSION['payment_order']);
        logError("Session payment data cleared");
    }
    
    // Return success JSON
    echo json_encode(['success' => true, 'message' => 'Payment completed successfully!']);
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
        logError("Transaction rolled back");
    }
    
    // Log error
    $errorMessage = "Payment processing error: " . $e->getMessage();
    logError($errorMessage);
    
    // Return error JSON
    echo json_encode(['success' => false, 'message' => $errorMessage]);
    exit();
} 