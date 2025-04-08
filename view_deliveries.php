<?php
require_once 'check_staff_session.php';
require_once 'config/database.php';

// Simple SMS function that can be modified later to use any SMS service
function sendSMS($to, $message) {
    // Placeholder for SMS implementation
    // You can implement your preferred SMS service here
    // For now, we'll just log the message
    error_log("SMS would be sent to $to with message: $message");
    return true;
}

// Function to get status message
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

// Initialize filter variables
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Build the query with potential filters
$query = "SELECT h.delivery_id, h.delivery_time, u.user_id, u.user_name as name, u.phone, h.address, 
          SUM(h.total_amount) as total_amount, 
          CASE 
              WHEN COUNT(DISTINCT h.food_status) > 1 THEN 'mixed'
              ELSE MAX(h.food_status) 
          END as food_status,
          GROUP_CONCAT(
              DISTINCT
              CONCAT(m.name, ' (x', h.quantity, ')')
              ORDER BY m.name
              SEPARATOR ', '
          ) as ordered_items
          FROM homedeliveryorders h 
          JOIN Users u ON h.user_id = u.user_id 
          JOIN MenuItems m ON h.menu_id = m.menu_id
          WHERE 1=1";

// Add filters if provided
if (!empty($status_filter)) {
    $query .= " AND h.food_status = ?";
}
if (!empty($date_filter)) {
    $query .= " AND DATE(h.delivery_time) = ?";
}
if (!empty($search_term)) {
    $query .= " AND (u.user_name LIKE ? OR h.address LIKE ? OR u.phone LIKE ?)";
}

// Group by delivery time and customer to show orders made at the same time
$query .= " GROUP BY h.delivery_time, u.user_id, h.address 
            ORDER BY h.delivery_time DESC";

try {
    $stmt = $conn->prepare($query);
    
    // Bind parameters if filters are applied
    if (!empty($status_filter) && !empty($date_filter) && !empty($search_term)) {
        $search_param = "%$search_term%";
        $stmt->bind_param("sssss", $status_filter, $date_filter, $search_param, $search_param, $search_param);
    } elseif (!empty($status_filter) && !empty($date_filter)) {
        $stmt->bind_param("ss", $status_filter, $date_filter);
    } elseif (!empty($status_filter) && !empty($search_term)) {
        $search_param = "%$search_term%";
        $stmt->bind_param("ssss", $status_filter, $search_param, $search_param, $search_param);
    } elseif (!empty($date_filter) && !empty($search_term)) {
        $search_param = "%$search_term%";
        $stmt->bind_param("ssss", $date_filter, $search_param, $search_param, $search_param);
    } elseif (!empty($status_filter)) {
        $stmt->bind_param("s", $status_filter);
    } elseif (!empty($date_filter)) {
        $stmt->bind_param("s", $date_filter);
    } elseif (!empty($search_term)) {
        $search_param = "%$search_term%";
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    }
    
    $stmt->execute();
    $deliveries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $_SESSION['message'] = "Error fetching deliveries: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    $deliveries = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Resto Home Delivery Management">
    <meta name="author" content="Resto">
    <meta name="keywords" content="restaurant, staff, deliveries, food, orders">
    
    <!-- Prevent caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon.png" type="">
    
    <!-- Page Title -->
    <title>Home Deliveries - Staff Portal</title>

    <!-- Core CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    
    <style>
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
        
        .header_section {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .delivery-content {
            flex: 1;
            padding: 40px 0;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
        }
        
        .card-header {
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        
        .card-body {
            padding: 20px;
            color: #fff;
        }
        
        .table {
            color: #fff;
        }
        
        .table th, .table td {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            vertical-align: middle;
        }
        
        .badge {
            padding: 8px 12px;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .badge-ordered {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-preparing {
            background-color: #ffc107;
            color: black;
        }
        
        .badge-ready {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge-out_for_delivery {
            background-color: #007bff;
            color: white;
        }
        
        .badge-delivered {
            background-color: #28a745;
            color: white;
        }
        
        .badge-cancelled {
            background-color: #dc3545;
            color: white;
        }
        
        .badge-mixed {
            background-color: #ffc107;
            color: black;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .filter-card {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
        
        .btn-primary:hover {
            background-color: #45a049;
            border-color: #45a049;
        }
        
        .alert {
            margin-top: 20px;
            border-radius: 15px;
        }
        
        .pagination {
            margin-top: 20px;
            justify-content: center;
        }
        
        .page-item.active .page-link {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
        
        .page-link {
            color: #4CAF50;
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .page-link:hover {
            color: #fff;
            background-color: #45a049;
            border-color: #45a049;
        }
        
        /* Enhanced Dropdown Styles for Better Clickability */
        .navbar-nav .dropdown-menu {
            margin-top: 10px;
            background-color: rgba(0, 0, 0, 0.9) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 8px !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5) !important;
            padding: 0 !important;
            min-width: 200px; /* Wider dropdown menu */
        }
        
        .navbar-nav .dropdown-item {
            color: white !important;
            padding: 15px 20px !important; /* Increased padding for larger click area */
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 16px; /* Slightly larger font */
            line-height: 1.5;
            transition: all 0.2s ease;
        }
        
        .navbar-nav .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .navbar-nav .dropdown-item:hover, 
        .navbar-nav .dropdown-item:focus {
            background-color: #4CAF50 !important;
            color: white !important;
            transform: translateX(5px); /* Slight movement on hover for feedback */
        }
        
        /* Ensure dropdown toggle has enough space */
        .dropdown-toggle {
            padding-right: 25px !important;
            position: relative;
        }
        
        .dropdown-toggle::after {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        /* Make sure dropdown stays open when interacting with it */
        .dropdown-menu.show {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Add a slight delay before closing dropdown */
        .dropdown {
            position: relative;
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
        <div class="delivery-content">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2 class="text-center text-white mb-4">Home Delivery Management</h2>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="filter-card">
                            <form method="GET" action="view_deliveries.php" class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status" class="text-white">Filter by Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="">All Statuses</option>
                                            <option value="ordered" <?php echo $status_filter === 'ordered' ? 'selected' : ''; ?>>Ordered</option>
                                            <option value="preparing" <?php echo $status_filter === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                            <option value="ready" <?php echo $status_filter === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                            <option value="out_for_delivery" <?php echo $status_filter === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                            <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date" class="text-white">Filter by Date</label>
                                        <input type="date" name="date" id="date" class="form-control" value="<?php echo $date_filter; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="search" class="text-white">Search</label>
                                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, address, phone..." value="<?php echo htmlspecialchars($search_term); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="text-white">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">Apply</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Deliveries Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Home Deliveries</h5>
                                <a href="view_deliveries.php" class="btn btn-sm btn-outline-light">Reset Filters</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($deliveries)): ?>
                                    <p class="text-center">No deliveries found matching your criteria.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer</th>
                                                    <th>Contact</th>
                                                    <th>Address</th>
                                                    <th>Amount</th>
                                                    <th>Delivery Time</th>
                                                    <th>Status</th>
                                                    <th>Ordered Items</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($deliveries as $delivery): ?>
                                                    <tr>
                                                        <td>#<?php echo htmlspecialchars($delivery['delivery_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($delivery['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($delivery['phone']); ?></td>
                                                        <td><?php echo htmlspecialchars($delivery['address']); ?></td>
                                                        <td>Rs<?php echo number_format($delivery['total_amount'], 2); ?></td>
                                                        <td><?php echo htmlspecialchars(date('Y-m-d g:i A', strtotime($delivery['delivery_time']))); ?></td>
                                                        <td>
                                                            <?php if ($delivery['food_status'] === 'mixed'): ?>
                                                                <span class="badge badge-warning">Mixed Status</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-<?php echo htmlspecialchars($delivery['food_status']); ?>">
                                                                    <?php echo ucfirst(htmlspecialchars(str_replace('_', ' ', $delivery['food_status']))); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($delivery['ordered_items']); ?></td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <select class="form-control form-control-sm" onchange="updateStatus('<?php echo $delivery['delivery_id']; ?>', '<?php echo $delivery['user_id']; ?>', '<?php echo $delivery['phone']; ?>', '<?php echo $delivery['food_status']; ?>', this.value)">
                                                                    <option value="ordered" <?php echo $delivery['food_status'] === 'ordered' ? 'selected' : ''; ?>>Ordered</option>
                                                                    <option value="preparing" <?php echo $delivery['food_status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                                    <option value="ready" <?php echo $delivery['food_status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                                                    <option value="out_for_delivery" <?php echo $delivery['food_status'] === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                                                    <option value="delivered" <?php echo $delivery['food_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                                    <option value="cancelled" <?php echo $delivery['food_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                                </select>
                                                                <input type="hidden" id="delivery_time_<?php echo $delivery['delivery_id']; ?>" value="<?php echo htmlspecialchars($delivery['delivery_time']); ?>">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/custom.js"></script>
    <script>
    function updateStatus(deliveryId, userId, phone, currentStatus, newStatus) {
        if (confirm('Are you sure you want to update the delivery status?')) {
            // Get the status message for SMS
            const statusMessages = {
                'ordered': 'Your order has been received and is being processed.',
                'preparing': 'Your order is now being prepared in our kitchen.',
                'ready': 'Your order is ready for delivery.',
                'out_for_delivery': 'Your order is out for delivery and will arrive soon.',
                'delivered': 'Your order has been delivered. Enjoy your meal!',
                'cancelled': 'Your order has been cancelled.'
            };
            
            const message = statusMessages[newStatus] || 'Your order status has been updated.';
            
            // Set form values
            document.getElementById('statusUpdateForm').elements['delivery_id'].value = deliveryId;
            document.getElementById('statusUpdateForm').elements['user_id'].value = userId;
            document.getElementById('statusUpdateForm').elements['phone'].value = phone;
            document.getElementById('statusUpdateForm').elements['current_status'].value = currentStatus;
            document.getElementById('statusUpdateForm').elements['new_status'].value = newStatus;
            document.getElementById('statusUpdateForm').elements['message'].value = message;
            document.getElementById('statusUpdateForm').elements['delivery_time'].value = document.getElementById('delivery_time_' + deliveryId).value;
            document.getElementById('statusUpdateForm').submit();
        }
    }

    // Improved dropdown functionality for better clickability
    $(document).ready(function() {
        // Variables to track dropdown state
        let dropdownTimeout;
        const DROPDOWN_DELAY = 300; // milliseconds to delay before closing
        
        // Toggle dropdown on click
        $('.dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $dropdown = $(this).parent('.dropdown');
            const $dropdownMenu = $(this).next('.dropdown-menu');
            
            // Close other dropdowns
            $('.dropdown').not($dropdown).removeClass('show');
            $('.dropdown-menu').not($dropdownMenu).removeClass('show');
            
            // Toggle current dropdown
            $dropdown.toggleClass('show');
            $dropdownMenu.toggleClass('show');
        });
        
        // Handle hover behavior on desktop
        if (window.innerWidth >= 992) {
            $('.dropdown').hover(
                function() {
                    // Clear any existing timeout
                    clearTimeout(dropdownTimeout);
                    
                    // Close other dropdowns
                    $('.dropdown').not(this).removeClass('show');
                    $('.dropdown').not(this).find('.dropdown-menu').removeClass('show');
                    
                    // Open this dropdown
                    $(this).addClass('show');
                    $(this).find('.dropdown-menu').addClass('show');
                },
                function() {
                    const $this = $(this);
                    
                    // Set timeout to close dropdown
                    dropdownTimeout = setTimeout(function() {
                        $this.removeClass('show');
                        $this.find('.dropdown-menu').removeClass('show');
                    }, DROPDOWN_DELAY);
                }
            );
            
            // Prevent dropdown from closing when hovering over the menu
            $('.dropdown-menu').hover(
                function() {
                    clearTimeout(dropdownTimeout);
                },
                function() {
                    const $dropdown = $(this).closest('.dropdown');
                    
                    dropdownTimeout = setTimeout(function() {
                        $dropdown.removeClass('show');
                        $dropdown.find('.dropdown-menu').removeClass('show');
                    }, DROPDOWN_DELAY);
                }
            );
        }
        
        // Ensure dropdown items work correctly with improved click handling
        $('.dropdown-item').on('click', function(e) {
            e.stopPropagation(); // Prevent event bubbling
            
            const href = $(this).attr('href');
            if (href && href !== '#') {
                window.location.href = href;
            }
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown').removeClass('show');
                $('.dropdown-menu').removeClass('show');
            }
        });
        
        // Make sure mobile menu works properly
        $('.navbar-toggler').on('click', function() {
            $('.dropdown-menu').removeClass('show');
            $('.dropdown').removeClass('show');
        });
    });
    </script>

    <!-- Add a hidden form for status updates -->
    <form id="statusUpdateForm" method="post" action="update_delivery_status.php" style="display: none;">
        <input type="hidden" name="delivery_id" value="">
        <input type="hidden" name="user_id" value="">
        <input type="hidden" name="phone" value="">
        <input type="hidden" name="current_status" value="">
        <input type="hidden" name="new_status" value="">
        <input type="hidden" name="message" value="">
        <input type="hidden" name="delivery_time" value="">
    </form>
</body>
</html> 