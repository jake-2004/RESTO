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

// Set current page for navigation highlighting
$current_page = 'booking_status.php';

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
    error_log("Error in booking_status.php: " . $e->getMessage());
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
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Feane Restaurant">

    <title>Booking Status - Feane Restaurant</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon.png" type="">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    
    <style>
        .booking-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .booking-header {
            background: linear-gradient(45deg, #ffbe33, #ff9900);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .booking-body {
            padding: 20px;
            color: white;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.85em;
            color: #aaa;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1.1em;
            font-weight: 500;
        }
        
        .booking-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-cancel {
            background-color: #ff4757;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .btn-cancel:hover {
            background-color: #e84118;
        }
        
        .btn-cancel:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85em;
        }
        
        .badge-warning {
            background-color: #ffa502;
            color: white;
        }
        
        .badge-success {
            background-color: #2ed573;
            color: white;
        }
        
        .badge-danger {
            background-color: #ff4757;
            color: white;
        }
        
        .badge-info {
            background-color: #1e90ff;
            color: white;
        }
        
        .badge-secondary {
            background-color: #747d8c;
            color: white;
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
            color: white;
            margin-bottom: 15px;
        }
        
        .empty-bookings p {
            color: #aaa;
            margin-bottom: 25px;
        }
        
        .btn-book {
            background: linear-gradient(45deg, #ffbe33, #ff9900);
            color: white;
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
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 190, 51, 0.4);
        }
        
        .booking-filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-group {
            display: flex;
            gap: 10px;
        }
        
        .filter-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: #ffbe33;
            color: white;
        }
        
        .search-box {
            position: relative;
            flex-grow: 1;
            max-width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 20px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }
        
        .booking-date {
            font-size: 1.2em;
            font-weight: 600;
        }
        
        .booking-time {
            font-size: 1.1em;
        }
        
        .booking-guests {
            font-size: 1.1em;
        }
        
        .booking-special-requests {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .special-requests-label {
            font-size: 0.85em;
            color: #aaa;
            margin-bottom: 5px;
        }
        
        .special-requests-value {
            font-style: italic;
            color: #ddd;
        }
        
        .no-special-requests {
            font-style: italic;
            color: #aaa;
        }
        
        .past-booking {
            opacity: 0.7;
        }
        
        .past-booking .booking-header {
            background: linear-gradient(45deg, #747d8c, #57606f);
        }
        
        @media (max-width: 768px) {
            .booking-filters {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-group {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .search-box {
                max-width: 100%;
            }
            
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

            <div class="booking-filters">
                <div class="filter-group">
                    <button class="filter-btn active" data-filter="all">All Bookings</button>
                    <button class="filter-btn" data-filter="upcoming">Upcoming</button>
                    <button class="filter-btn" data-filter="past">Past</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="confirmed">Confirmed</button>
                </div>
                <div class="filter-group">
                    <button class="filter-btn" data-filter="awaiting-approval">Awaiting Approval</button>
                    <button class="filter-btn" data-filter="approved">Approved</button>
                    <button class="filter-btn" data-filter="rejected">Rejected</button>
                </div>
                <div class="search-box">
                    <i class="fa fa-search"></i>
                    <input type="text" id="bookingSearch" placeholder="Search bookings...">
                </div>
            </div>

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
                        <div class="booking-card <?php echo $booking_class; ?>" 
                             data-date="<?php echo $booking['booking_date']; ?>"
                             data-time="<?php echo $booking['booking_time']; ?>"
                             data-status="<?php echo $booking['status']; ?>"
                             data-approval="<?php echo $booking['approval_status']; ?>"
                             data-guests="<?php echo $booking['num_persons']; ?>">
                            <div class="booking-header">
                                <div class="booking-date">
                                    <?php echo formatDate($booking['booking_date']); ?>
                                </div>
                                <div class="status-badges">
                                    <span class="badge <?php echo getStatusBadgeClass($booking['status']); ?>">
                                        <?php echo $booking['status_display']; ?>
                                    </span>
                                    <span class="badge <?php echo getApprovalStatusBadgeClass($booking['approval_status']); ?>">
                                        <?php echo $booking['approval_status_display']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="booking-body">
                                <div class="booking-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Time</span>
                                        <span class="detail-value"><?php echo formatTime($booking['booking_time']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Guests</span>
                                        <span class="detail-value"><?php echo $booking['num_persons']; ?> people</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Table Number</span>
                                        <span class="detail-value"><?php echo $booking['table_number']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Booking ID</span>
                                        <span class="detail-value">#<?php echo $booking['booking_id']; ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($booking['special_requests'])): ?>
                                    <div class="booking-special-requests">
                                        <span class="special-requests-label">Special Requests</span>
                                        <div class="special-requests-value">
                                            <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="booking-special-requests">
                                        <span class="special-requests-label">Special Requests</span>
                                        <div class="no-special-requests">No special requests</div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!$is_past && $booking['status'] == 'pending'): ?>
                                    <div class="booking-actions">
                                        <button class="btn-cancel" data-booking-id="<?php echo $booking['booking_id']; ?>">
                                            Cancel Booking
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
    
    <script>
        $(document).ready(function() {
            // Filter bookings
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                const filter = $(this).data('filter');
                filterBookings(filter);
            });
            
            // Search bookings
            $('#bookingSearch').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                searchBookings(searchTerm);
            });
            
            // Cancel booking
            $('.btn-cancel').click(function() {
                if (confirm('Are you sure you want to cancel this booking?')) {
                    const bookingId = $(this).data('booking-id');
                    
                    $.ajax({
                        url: 'cancel_booking.php',
                        type: 'POST',
                        data: { booking_id: bookingId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showNotification('Booking cancelled successfully', 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                showNotification(response.message || 'Error cancelling booking', 'error');
                            }
                        },
                        error: function() {
                            showNotification('Error cancelling booking. Please try again.', 'error');
                        }
                    });
                }
            });
            
            // Function to filter bookings
            function filterBookings(filter) {
                const now = new Date();
                const currentDate = now.toISOString().split('T')[0];
                const currentTime = now.toTimeString().split(' ')[0];
                
                $('.booking-card').each(function() {
                    const date = $(this).data('date');
                    const time = $(this).data('time');
                    const status = $(this).data('status');
                    const approval = $(this).data('approval');
                    const isPast = new Date(date + ' ' + time) < now;
                    
                    let show = false;
                    
                    switch(filter) {
                        case 'all':
                            show = true;
                            break;
                        case 'upcoming':
                            show = !isPast;
                            break;
                        case 'past':
                            show = isPast;
                            break;
                        case 'pending':
                            show = status === 'pending';
                            break;
                        case 'confirmed':
                            show = status === 'confirmed';
                            break;
                        case 'awaiting-approval':
                            show = approval === 'pending';
                            break;
                        case 'approved':
                            show = approval === 'approved';
                            break;
                        case 'rejected':
                            show = approval === 'rejected';
                            break;
                    }
                    
                    if (show) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                
                // Check if any bookings are visible
                checkEmptyState();
            }
            
            // Function to search bookings
            function searchBookings(searchTerm) {
                $('.booking-card').each(function() {
                    const date = $(this).data('date');
                    const time = $(this).data('time');
                    const guests = $(this).data('guests');
                    const status = $(this).data('status');
                    
                    const searchText = date + ' ' + time + ' ' + guests + ' ' + status;
                    
                    if (searchText.toLowerCase().includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                
                // Check if any bookings are visible
                checkEmptyState();
            }
            
            // Function to check if any bookings are visible
            function checkEmptyState() {
                const visibleBookings = $('.booking-card:visible').length;
                
                if (visibleBookings === 0) {
                    if ($('.empty-search-results').length === 0) {
                        $('.booking-filters').after(`
                            <div class="empty-bookings empty-search-results">
                                <i class="fa fa-search"></i>
                                <h4>No Bookings Found</h4>
                                <p>No bookings match your search criteria.</p>
                            </div>
                        `);
                    }
                } else {
                    $('.empty-search-results').remove();
                }
            }
            
            // Define notification function if not already defined
            if (typeof window.showNotification !== 'function') {
                window.showNotification = function(message, type) {
                    const container = $('#notification-container');
                    if (container.length === 0) {
                        $('body').append('<div id="notification-container"></div>');
                    }
                    
                    const notification = $(`
                        <div class="notification ${type}">
                            <div class="message">${message}</div>
                        </div>
                    `).appendTo('#notification-container');
                    
                    setTimeout(() => {
                        notification.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 3000);
                };
            }
        });
    </script>
</body>
</html> 