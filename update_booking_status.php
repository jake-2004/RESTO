<?php
require_once 'check_staff_session.php';
require_once 'config/database.php';
require_once 'notification_helper.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['action'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    
    try {
        // Validate the action
        if ($action !== 'accept' && $action !== 'decline') {
            throw new Exception("Invalid action specified");
        }
        
        // Set the new status based on the action
        $status = ($action === 'accept') ? 'confirmed' : 'cancelled';
        
        // Get booking details for notification before updating
        $booking_query = "SELECT user_id, name, booking_date, booking_time FROM TableBookings WHERE booking_id = ?";
        $booking_stmt = $conn->prepare($booking_query);
        $booking_stmt->bind_param("i", $booking_id);
        $booking_stmt->execute();
        $booking_result = $booking_stmt->get_result();
        $booking = $booking_result->fetch_assoc();
        
        if (!$booking) {
            throw new Exception("Booking not found");
        }
        
        // Update the booking status
        $query = "UPDATE TableBookings SET status = ? WHERE booking_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $booking_id);
        
        if ($stmt->execute()) {
            // Format date and time for notification
            $formatted_date = date('F j, Y', strtotime($booking['booking_date']));
            $formatted_time = date('g:i A', strtotime($booking['booking_time']));
            
            // Create notification message based on action
            if ($action === 'accept') {
                $notification_message = "Your table booking for {$formatted_date} at {$formatted_time} has been confirmed.";
                $notification_type = "booking_confirmed";
            } else {
                $notification_message = "Your table booking for {$formatted_date} at {$formatted_time} has been declined.";
                $notification_type = "booking_declined";
            }
            
            // Create notification
            create_notification($booking['user_id'], $notification_message, $notification_type);
            
            // Set success message
            $_SESSION['message'] = "Booking #" . $booking_id . " has been " . $status . " successfully.";
            $_SESSION['message_type'] = "success";
            
        } else {
            throw new Exception("Failed to update booking status: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirect back to the booking details page
    header("Location: booking-details.php");
    exit;
} else {
    // If accessed directly without proper parameters
    $_SESSION['message'] = "Invalid request";
    $_SESSION['message_type'] = "warning";
    header("Location: booking-details.php");
    exit;
}
?>

<!-- New Table Bookings Section -->
<section class="shift-info">
    <h3>New Table Bookings</h3>
    <div class="task-cards">
        <?php if (!empty($new_bookings)): ?>
            <?php foreach ($new_bookings as $booking): ?>
                <div class="task-card">
                    <div class="task-header">
                        <span class="task-title"><?php echo htmlspecialchars($booking['name']); ?></span>
                        <span class="task-status status-pending">Upcoming</span>
                    </div>
                    <div class="task-details">
                        <p><strong>Persons:</strong> <?php echo htmlspecialchars($booking['num_persons']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars($booking['booking_time']); ?></p>
                    </div>
                    <form method="POST" action="update_booking_status.php" class="booking-actions">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                        <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">Accept</button>
                        <button type="submit" name="action" value="decline" class="btn btn-danger btn-sm">Decline</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">No new bookings.</p>
        <?php endif; ?>
    </div>
</section> 