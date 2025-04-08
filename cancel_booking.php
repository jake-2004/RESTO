<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if booking_id is provided
if (!isset($_POST['booking_id']) || empty($_POST['booking_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

$booking_id = intval($_POST['booking_id']);

try {
    // Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // First, verify that the booking belongs to the current user
    $verify_query = "SELECT booking_id, status, booking_date, booking_time 
                    FROM TableBookings 
                    WHERE booking_id = ? AND user_id = ?";
    
    $verify_stmt = $conn->prepare($verify_query);
    if (!$verify_stmt) {
        throw new Exception("Failed to prepare verification query: " . $conn->error);
    }

    $verify_stmt->bind_param("ii", $booking_id, $user_id);
    if (!$verify_stmt->execute()) {
        throw new Exception("Failed to execute verification query: " . $verify_stmt->error);
    }

    $verify_result = $verify_stmt->get_result();
    if ($verify_result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Booking not found or does not belong to you']);
        exit();
    }

    $booking_data = $verify_result->fetch_assoc();

    // Check if booking is already cancelled
    if ($booking_data['status'] === 'cancelled') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'This booking is already cancelled']);
        exit();
    }

    // Check if booking is in the past
    $booking_datetime = strtotime($booking_data['booking_date'] . ' ' . $booking_data['booking_time']);
    if ($booking_datetime < time()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Cannot cancel a past booking']);
        exit();
    }

    // Update the booking status to cancelled
    $update_query = "UPDATE TableBookings SET status = 'cancelled' WHERE booking_id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    
    if (!$update_stmt) {
        throw new Exception("Failed to prepare update query: " . $conn->error);
    }

    $update_stmt->bind_param("ii", $booking_id, $user_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to execute update query: " . $update_stmt->error);
    }

    // Check if the update was successful
    if ($update_stmt->affected_rows > 0) {
        // Try to create notification if the table exists
        try {
            $notification_message = "Your table booking #" . $booking_id . " has been cancelled.";
            $notification_query = "INSERT INTO Notifications (user_id, message, type, reference_id) VALUES (?, ?, 'booking_cancelled', ?)";
            $notification_stmt = $conn->prepare($notification_query);
            
            if ($notification_stmt) {
                $notification_stmt->bind_param("isi", $user_id, $notification_message, $booking_id);
                if (!$notification_stmt->execute()) {
                    // Log the error but don't fail the cancellation
                    error_log("Failed to create notification: " . $notification_stmt->error);
                }
            }
        } catch (Exception $e) {
            // Log the error but don't fail the cancellation
            error_log("Notification creation failed: " . $e->getMessage());
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to cancel booking. Please try again.']);
    }

} catch (Exception $e) {
    error_log("Error in cancel_booking.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'An error occurred while cancelling the booking. Please try again.']);
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?> 