<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's table bookings
try {
    $query = "SELECT tb.*, 
              CASE 
                  WHEN tb.status = 'pending' THEN 'Pending'
                  WHEN tb.status = 'confirmed' THEN 'Confirmed'
                  WHEN tb.status = 'cancelled' THEN 'Cancelled'
                  WHEN tb.status = 'completed' THEN 'Completed'
                  ELSE tb.status
              END as status_display,
              CASE 
                  WHEN tb.approval_status = 'pending' THEN 'Awaiting Approval'
                  WHEN tb.approval_status = 'approved' THEN 'Approved'
                  WHEN tb.approval_status = 'rejected' THEN 'Rejected'
                  ELSE tb.approval_status
              END as approval_status_display
              FROM TableBookings tb
              WHERE tb.user_id = ?
              ORDER BY tb.booking_date DESC, tb.booking_time DESC";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare booking query: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute booking query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Error in user_booking_status.php: " . $e->getMessage());
    $error_message = $e->getMessage();
}

// Function to format date
function formatDate($date) {
    return date("F j, Y", strtotime($date));
}

// Function to format time
function formatTime($time) {
    return date("g:i A", strtotime($time));
}

// Function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'badge-warning';
        case 'confirmed':
            return 'badge-success';
        case 'cancelled':
            return 'badge-danger';
        case 'completed':
            return 'badge-info';
        default:
            return 'badge-secondary';
    }
}

// Function to get approval status badge class
function getApprovalStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'badge-warning';
        case 'approved':
            return 'badge-success';
        case 'rejected':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}

// Function to check if booking is in the past
function isPastBooking($date, $time) {
    $booking_datetime = strtotime($date . ' ' . $time);
    $current_datetime = time();
    return $booking_datetime < $current_datetime;
}

// Set flag to hide notifications for this page
$hide_notifications = true;

include 'includes/head.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Feane Restaurant">

    <title>Your Booking Status - Feane Restaurant</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon.png" type="">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    
    <style>
        .booking-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .booking-header {
            background: linear-gradient(45deg, #ffbe33, #ff9900);
            color: #000;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.2em;
        }
        
        .booking-body {
            padding: 25px;
            color: #000;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .detail-label {
            font-size: 0.9em;
            color: #333;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .detail-value {
            font-size: 1.3em;
            font-weight: 600;
            color: #000;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .badge {
            padding: 10px 15px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .badge-warning {
            background-color: #ffa502;
            color: #000;
        }
        
        .badge-success {
            background-color: #2ed573;
            color: #000;
        }
        
        .badge-danger {
            background-color: #ff4757;
            color: #fff;
        }
        
        .badge-info {
            background-color: #1e90ff;
            color: #fff;
        }
        
        .badge-secondary {
            background-color: #747d8c;
            color: #fff;
        }
        
        .status-badges {
            display: flex;
            gap: 10px;
        }
        
        .booking-date {
            font-weight: 600;
            color: #000;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .empty-bookings {
            text-align: center;
            padding: 50px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin: 30px 0;
        }
        
        .empty-bookings i {
            font-size: 3em;
            color: #ffbe33;
            margin-bottom: 20px;
        }
        
        .empty-bookings h4 {
            color: #000;
            margin-bottom: 15px;
        }
        
        .empty-bookings p {
            color: #333;
            margin-bottom: 25px;
        }
        
        .btn-book {
            background: linear-gradient(45deg, #ffbe33, #ff9900);
            color: #000;
            padding: 12px 35px;
            border-radius: 30px;
            display: inline-block;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .btn-book:hover {
            background: linear-gradient(45deg, #ff9900, #ffbe33);
            color: #000;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 190, 51, 0.4);
        }
        
        .past-booking {
            opacity: 0.7;
        }
        
        .past-booking .booking-header {
            background: linear-gradient(45deg, #747d8c, #57606f);
        }
        
        .booking-actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-cancel {
            background: linear-gradient(45deg, #ff4757, #ff6b81);
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(255, 71, 87, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9em;
        }
        
        .btn-cancel:hover {
            background: linear-gradient(45deg, #ff6b81, #ff4757);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 71, 87, 0.4);
            color: #fff;
        }
        
        .btn-cancel i {
            font-size: 1.1em;
        }

        /* Modal Styles */
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .modal-title {
            color: #000;
            font-weight: 600;
        }

        .modal-body {
            padding: 25px;
            color: #333;
        }

        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .btn-confirm-cancel {
            background: linear-gradient(45deg, #ff4757, #ff6b81);
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-confirm-cancel:hover {
            background: linear-gradient(45deg, #ff6b81, #ff4757);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 71, 87, 0.4);
            color: #fff;
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: #fff;
        }
        
        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="sub_page">
    <div class="hero_area">
        <!-- Include the user header -->
        <?php include 'includes/user_header.php'; ?>
    </div>

    <section class="food_section layout_padding">
        <div class="container">
            <div class="heading_container heading_center">
                <h2>Your Table Bookings</h2>
                <p class="text-white-50">View and manage your table reservations</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($bookings)): ?>
                <div class="empty-bookings">
                    <i class="fa fa-calendar-times-o"></i>
                    <h4>No Bookings Found</h4>
                    <p>You haven't made any table reservations yet.</p>
                    <a href="book.php" class="btn-book">Book a Table</a>
                </div>
            <?php else: ?>
                <div id="bookingsContainer">
                    <?php foreach ($bookings as $booking): 
                        $is_past = isPastBooking($booking['booking_date'], $booking['booking_time']);
                        $booking_class = $is_past ? 'past-booking' : '';
                    ?>
                        <div class="booking-card <?php echo $booking_class; ?>">
                            <div class="booking-header">
                                <span class="booking-date"><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></span>
                                <div class="status-badges">
                                    <span class="badge badge-<?php echo strtolower($booking['status']); ?>">
                                        <?php echo $booking['status']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="booking-body">
                                <div class="booking-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Booking Time</span>
                                        <span class="detail-value"><?php echo date('h:i A', strtotime($booking['booking_time'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Number of Guests</span>
                                        <span class="detail-value"><?php echo $booking['num_persons']; ?> People</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Contact Number</span>
                                        <span class="detail-value"><?php echo $booking['phone']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Email</span>
                                        <span class="detail-value"><?php echo $booking['email']; ?></span>
                                    </div>
                                </div>
                                <?php if (!empty($booking['special_requests'])): ?>
                                <div class="special-requests">
                                    <span class="detail-label">Special Requests</span>
                                    <p class="detail-value"><?php echo $booking['special_requests']; ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($booking['status'] !== 'cancelled' && !$is_past): ?>
                                <div class="booking-actions">
                                    <button class="btn-cancel" data-booking-id="<?php echo $booking['booking_id']; ?>">
                                        <i class="fa fa-times-circle"></i> Cancel Booking
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- jQery -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <!-- popper js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <!-- bootstrap js -->
    <script src="js/bootstrap.js"></script>
    <!-- custom js -->
    <script src="js/custom.js"></script>
    
    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Confirm Cancellation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel this booking? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Keep Booking</button>
                    <button type="button" class="btn btn-confirm-cancel" id="confirmCancel">Yes, Cancel Booking</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let currentBookingId = null;

            // Handle cancel booking button click
            $('.btn-cancel').click(function() {
                currentBookingId = $(this).data('booking-id');
                $('#cancelModal').modal('show');
            });

            // Handle confirm cancellation
            $('#confirmCancel').click(function() {
                if (currentBookingId) {
                    $.ajax({
                        url: 'cancel_booking.php',
                        type: 'POST',
                        data: { booking_id: currentBookingId },
                        dataType: 'json',
                        success: function(response) {
                            $('#cancelModal').modal('hide');
                            if (response.success) {
                                // Show success message with a slight delay
                                setTimeout(function() {
                                    alert('Booking cancelled successfully');
                                    // Reload the page after showing the success message
                                    window.location.reload();
                                }, 100);
                            } else {
                                alert(response.message || 'Error cancelling booking');
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#cancelModal').modal('hide');
                            console.error('Error:', error);
                            alert('Error cancelling booking. Please try again.');
                        }
                    });
                }
            });

            // Handle modal close
            $('#cancelModal').on('hidden.bs.modal', function () {
                currentBookingId = null;
            });
        });
    </script>
</body>
</html> 