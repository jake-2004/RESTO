<?php
require_once 'check_staff_session.php';
require_once 'config/database.php';

// Initialize variables
$bookings = [];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

try {
    // Base query
    $query = "SELECT booking_id, name, email, phone, num_persons, booking_date, booking_time, status, created_at 
              FROM TableBookings 
              WHERE 1=1";
    
    // Add filters
    if ($filter === 'pending') {
        $query .= " AND status = 'pending'";
    } elseif ($filter === 'confirmed') {
        $query .= " AND status = 'confirmed'";
    } elseif ($filter === 'cancelled') {
        $query .= " AND status = 'cancelled'";
    } elseif ($filter === 'today') {
        $query .= " AND booking_date = CURDATE()";
    } elseif ($filter === 'upcoming') {
        $query .= " AND booking_date > CURDATE()";
    }
    
    // Add search
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    }
    
    // Add date filter
    if (!empty($date_filter)) {
        $query .= " AND booking_date = ?";
    }
    
    // Order and limit
    $query .= " ORDER BY booking_date ASC, booking_time ASC";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    if (!empty($search) && !empty($date_filter)) {
        $search_param = "%$search%";
        $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $date_filter);
    } elseif (!empty($search)) {
        $search_param = "%$search%";
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    } elseif (!empty($date_filter)) {
        $stmt->bind_param("s", $date_filter);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Resto Staff Panel - Booking Management">
    <meta name="author" content="Resto">
    
    <!-- Prevent caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon.png" type="">
    
    <!-- Page Title -->
    <title>Booking Details - Resto Staff</title>

    <!-- Core CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">

    <!-- Custom Staff Styles -->
    <style>
        /* Inherit base styles from staff.php */
        .hero_area {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .bg-box {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .bg-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.4);
        }

        /* Header & Navigation */
        .header_section {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            position: relative;
            z-index: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Modified color scheme for staff */
        :root {
            --staff-primary: #4CAF50;
            --staff-secondary: #45a049;
            --staff-accent: #81c784;
        }

        /* Main content area */
        .booking-content {
            flex: 1;
            padding: 40px 0;
            color: white;
        }

        /* Booking table styling */
        .booking-table {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            overflow: hidden;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .booking-table .table {
            color: white;
            margin-bottom: 0;
        }

        .booking-table th {
            background: rgba(76, 175, 80, 0.2);
            color: var(--staff-primary);
            border-top: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
        }

        .booking-table td {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
            vertical-align: middle;
        }

        /* Status badges */
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
            display: inline-block;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-accepted {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .status-declined {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
        }

        /* Action buttons */
        .booking-actions {
            display: flex;
            gap: 8px;
        }

        .booking-actions button {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85em;
            transition: all 0.3s ease;
        }

        /* Filter section */
        .filter-section {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .filter-section .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .filter-section .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Fix for dropdown visibility */
        .filter-section select.form-control option {
            background-color: #333;
            color: white;
        }

        .filter-section .btn-filter {
            background: var(--staff-primary);
            color: white;
            border: none;
        }

        /* Booking details modal */
        .booking-modal .modal-content {
            background: rgba(0, 0, 0, 0.9);
            color: white;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .booking-modal .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .booking-modal .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .booking-modal .close {
            color: white;
        }

        .booking-detail-row {
            margin-bottom: 15px;
        }

        .booking-detail-label {
            font-weight: 500;
            color: var(--staff-primary);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .booking-table {
                overflow-x: auto;
            }
            
            .filter-form {
                flex-direction: column;
            }
            
            .filter-form .form-group {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="hero_area">
        <!-- Background Image -->
        <div class="bg-box">
            <img src="images/indexpic.jpeg" alt="Restaurant Background">
        </div>

        <!-- Include the staff header -->
        <?php include 'staff-header.php'; ?>

        <!-- Display messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="container mt-3">
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <?php 
            // Clear the message after displaying
            unset($_SESSION['message']); 
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <!-- Main Content Section -->
        <div class="booking-content">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-white">Table Booking Management</h2>
                    <a href="staff.php" class="btn btn-outline-light">
                        <i class="fa fa-arrow-left mr-2"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <form action="" method="GET" class="d-flex flex-wrap align-items-end filter-form">
                        <div class="form-group mr-3 flex-grow-1">
                            <label for="filter">Filter Status:</label>
                            <select name="filter" id="filter" class="form-control">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Bookings</option>
                                <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today's Bookings</option>
                                <option value="upcoming" <?php echo $filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming Bookings</option>
                            </select>
                        </div>
                        <div class="form-group mr-3 flex-grow-1">
                            <label for="date">Filter by Date:</label>
                            <input type="date" name="date" id="date" class="form-control" value="<?php echo $date_filter; ?>">
                        </div>
                        <div class="form-group mr-3 flex-grow-1">
                            <label for="search">Search:</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Name, Email or Phone" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-filter">Apply Filters</button>
                            <a href="booking-details.php" class="btn btn-outline-secondary ml-2">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Bookings Table -->
                <div class="booking-table">
                    <table class="table table-responsive-lg">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Persons</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No bookings found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['num_persons']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="booking-actions">
                                                <button type="button" class="btn btn-info btn-sm view-details" 
                                                        data-id="<?php echo htmlspecialchars($booking['booking_id']); ?>"
                                                        data-name="<?php echo htmlspecialchars($booking['name']); ?>"
                                                        data-email="<?php echo htmlspecialchars($booking['email']); ?>"
                                                        data-phone="<?php echo htmlspecialchars($booking['phone']); ?>"
                                                        data-persons="<?php echo htmlspecialchars($booking['num_persons']); ?>"
                                                        data-date="<?php echo htmlspecialchars($booking['booking_date']); ?>"
                                                        data-time="<?php echo htmlspecialchars($booking['booking_time']); ?>"
                                                        data-status="<?php echo htmlspecialchars($booking['status']); ?>"
                                                        data-created="<?php echo htmlspecialchars($booking['created_at']); ?>">
                                                    <i class="fa fa-eye"></i> View
                                                </button>
                                                
                                                <?php if ($booking['status'] === 'pending'): ?>
                                                    <form method="post" action="update_booking_status.php" class="d-inline">
                                                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                                        <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">
                                                            <i class="fa fa-check"></i> Accept
                                                        </button>
                                                        <button type="submit" name="action" value="decline" class="btn btn-danger btn-sm">
                                                            <i class="fa fa-times"></i> Decline
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade booking-modal" id="bookingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingDetailsModalLabel">Booking Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="booking-detail-row">
                        <div class="booking-detail-label">Booking ID:</div>
                        <div id="modal-booking-id"></div>
                    </div>
                    <div class="booking-detail-row">
                        <div class="booking-detail-label">Customer Name:</div>
                        <div id="modal-name"></div>
                    </div>
                    <div class="booking-detail-row">
                        <div class="booking-detail-label">Email:</div>
                        <div id="modal-email"></div>
                    </div>
                    <div class="booking-detail-row">
                        <div class="booking-detail-label">Phone:</div>
                        <div id="modal-phone"></div>
                    </div>
                    <div class="booking-detail-row">
                        <div class="booking-detail-label">Number of Persons:</div>
                        <div id="modal-persons"></div>
                    </div>
                    <div class="booking-detail-row">
                        <div class="booking-detail-label">Booking Date:</div>
                        <div id="modal-date"></div>
                    </div>
                    <div class="booking-detail-row">
                        <div class="booking-detail-label">Booking Time:</div>
                        <div id="modal-time"></div>
                    </div>
                    <div class="booking-detail-row">
                        <div class="booking-detail-label">Status:</div>
                        <div id="modal-status"></div>
                    </div>
                    <div class="booking-detail-row">
                        <div class="booking-detail-label">Created At:</div>
                        <div id="modal-created"></div>
                    </div>
                </div>
                <div class="modal-footer" id="modal-actions">
                    <!-- Actions will be dynamically added here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script>
        $(document).ready(function() {
            // View booking details
            $('.view-details').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const email = $(this).data('email');
                const phone = $(this).data('phone');
                const persons = $(this).data('persons');
                const date = $(this).data('date');
                const time = $(this).data('time');
                const status = $(this).data('status');
                const created = $(this).data('created');
                
                // Populate modal
                $('#modal-booking-id').text('#' + id);
                $('#modal-name').text(name);
                $('#modal-email').text(email);
                $('#modal-phone').text(phone);
                $('#modal-persons').text(persons);
                $('#modal-date').text(date);
                $('#modal-time').text(time);
                
                // Set status with appropriate styling
                let statusHtml = `<span class="status-badge status-${status.toLowerCase()}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
                $('#modal-status').html(statusHtml);
                
                $('#modal-created').text(created);
                
                // Add action buttons if status is pending
                if (status === 'pending') {
                    $('#modal-actions').html(`
                        <form method="post" action="update_booking_status.php" class="d-flex w-100 justify-content-between">
                            <input type="hidden" name="booking_id" value="${id}">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <div>
                                <button type="submit" name="action" value="accept" class="btn btn-success">
                                    <i class="fa fa-check"></i> Accept Booking
                                </button>
                                <button type="submit" name="action" value="decline" class="btn btn-danger">
                                    <i class="fa fa-times"></i> Decline Booking
                                </button>
                            </div>
                        </form>
                    `);
                } else {
                    $('#modal-actions').html(`
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    `);
                }
                
                // Show modal
                $('#bookingDetailsModal').modal('show');
            });
        });
    </script>
</body>
</html> 