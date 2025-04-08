<?php
require_once 'check_staff_session.php';
require_once 'config/database.php';

// Fetch new table bookings
$new_bookings = [];
$new_orders = [];
$new_preorders = [];

try {
    // Fetch new table bookings
    $booking_query = "SELECT booking_id, name, num_persons, booking_date, booking_time, status FROM TableBookings WHERE booking_date >= CURDATE() AND status = 'pending' ORDER BY booking_date, booking_time LIMIT 5";
    $booking_stmt = $conn->prepare($booking_query);
    $booking_stmt->execute();
    $new_bookings = $booking_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch new food orders
    $order_query = "SELECT o.order_id, o.order_date, u.name, SUM(oi.quantity) as total_items FROM Orders o JOIN OrderItems oi ON o.order_id = oi.order_id JOIN Users u ON o.user_id = u.user_id WHERE o.order_date >= CURDATE() GROUP BY o.order_id ORDER BY o.order_date DESC LIMIT 5";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->execute();
    $new_orders = $order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch the latest home delivery orders
    $query = "SELECT h.delivery_id as order_id, h.delivery_time as order_date, u.name, h.address, h.total_amount, 
              h.food_status
              FROM homedeliveryorders h 
              JOIN Users u ON h.user_id = u.user_id 
              WHERE h.delivery_time >= CURDATE()
              ORDER BY h.delivery_time DESC 
              LIMIT 10"; // Adjust the limit as needed

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare order query: " . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute order query: " . $stmt->error);
    }

    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch new preorders with detailed debugging
    $preorder_query = "SELECT 
        p.preorder_id, 
        u.user_id,
        u.name AS user_name, 
        u.email, 
        u.phone, 
        u.address, 
        m.name AS item_name, 
        p.quantity, 
        p.pickup_date, 
        p.pickup_time, 
        p.order_status, 
        p.total_amount, 
        p.created_at
    FROM preorders p
    JOIN users u ON p.user_id = u.user_id
    JOIN menuItems m ON p.menu_id = m.menu_id
    ORDER BY p.created_at DESC
    LIMIT 10";

    // Debug: Log the query
    error_log("Preorder query: " . $preorder_query);

    $preorder_stmt = $conn->prepare($preorder_query);
    if (!$preorder_stmt) {
        error_log("Failed to prepare preorder query: " . $conn->error);
    } else {
        if (!$preorder_stmt->execute()) {
            error_log("Failed to execute preorder query: " . $preorder_stmt->error);
        } else {
            $result = $preorder_stmt->get_result();
            if (!$result) {
                error_log("Failed to get result from preorder query: " . $preorder_stmt->error);
            } else {
                $new_preorders = $result->fetch_all(MYSQLI_ASSOC);
                error_log("Fetched " . count($new_preorders) . " preorders");
                
                // Debug: Dump the first preorder if available
                if (count($new_preorders) > 0) {
                    error_log("First preorder: " . print_r($new_preorders[0], true));
                } else {
                    // Check if there are any preorders in the database
                    $count_query = "SELECT COUNT(*) as count FROM preorders";
                    $count_result = $conn->query($count_query);
                    if ($count_result) {
                        $count = $count_result->fetch_assoc()['count'];
                        error_log("Total preorders in database: " . $count);
                    }
                }
            }
        }
    }

} catch (Exception $e) {
    error_log("Error fetching new bookings or orders: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Resto Staff Panel - Restaurant Management System">
    <meta name="author" content="Resto">
    <meta name="keywords" content="restaurant, staff, management, food, orders">
    
    <!-- Prevent caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon.png" type="">
    
    <!-- Page Title -->
    <title>Resto Staff - Dashboard</title>

    <!-- Core CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">

    <!-- Custom Staff Styles -->
    <style>
        /* Inherit base styles from admin.html */
        .hero_area {
            height: 100vh;
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

        /* Navigation styling */
        .navbar-nav .nav-link {
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .navbar-nav .nav-link::before {
            background: linear-gradient(135deg, var(--staff-primary) 0%, var(--staff-secondary) 100%);
        }

        .navbar-nav .nav-link:hover {
            background: rgba(76, 175, 80, 0.1);
            color: var(--staff-primary);
        }

        /* Badge styling for staff */
        .badge-staff {
            background: linear-gradient(135deg, var(--staff-primary) 0%, var(--staff-secondary) 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(76, 175, 80, 0.3);
        }

        /* Staff content area - Enhanced */
        .staff-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 40px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
        }

        /* Welcome message styling */
        .welcome-message {
            margin-bottom: 2.5rem;
            padding: 2rem;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 20px;
            border-left: 5px solid var(--staff-primary);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: left;
        }

        .welcome-message h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--staff-primary);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-message p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Task Cards */
        .task-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .task-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 16px;
            text-align: left;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .task-card:hover {
            transform: translateY(-5px);
            background: rgba(76, 175, 80, 0.1);
            box-shadow: 0 8px 32px rgba(76, 175, 80, 0.2);
        }

        .task-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .task-title {
            color: var(--staff-primary);
            font-size: 1.2em;
            font-weight: 600;
        }

        .task-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-in-progress {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .status-completed {
            background: rgba(33, 150, 243, 0.2);
            color: #2196F3;
        }

        .task-details {
            margin: 15px 0;
            color: #e0e0e0;
        }

        .task-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            font-size: 0.9em;
            color: #bdbdbd;
        }

        /* Quick Actions - Updated for larger buttons */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }

        .action-button {
            background: rgba(76, 175, 80, 0.15);
            color: var(--staff-primary);
            border: 1px solid rgba(76, 175, 80, 0.3);
            padding: 30px 20px;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .action-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1) 0%, rgba(76, 175, 80, 0.3) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .action-button:hover {
            background: rgba(76, 175, 80, 0.25);
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .action-button:hover::before {
            opacity: 1;
        }

        .action-button i {
            font-size: 3em;
            margin-bottom: 15px;
            display: block;
            color: var(--staff-primary);
            transition: all 0.3s ease;
        }

        .action-button:hover i {
            color: white;
            transform: scale(1.1);
        }

        .action-button span {
            font-size: 1.3em;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        /* Container styling */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Alert styling */
        .alert {
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border-left: 5px solid #4CAF50;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .welcome-message h2 {
                font-size: 2rem;
            }
            
            .welcome-message p {
                font-size: 1rem;
            }
            
            .action-button {
                padding: 25px 15px;
            }
            
            .action-button i {
                font-size: 2.5em;
            }
            
            .action-button span {
                font-size: 1.1em;
            }
        }

        @media (max-width: 767px) {
            .staff-content {
                padding: 30px 15px;
            }
            
            .welcome-message {
                padding: 1.5rem;
            }
            
            .welcome-message h2 {
                font-size: 1.8rem;
            }
            
            .quick-actions {
                gap: 15px;
            }
        }

        /* Dropdown styling - Enhanced */
        .dropdown-menu {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            padding: 12px 8px;
            border: none;
            min-width: 200px;
            margin-top: 10px;
            backdrop-filter: blur(10px);
        }

        .dropdown-item {
            color: #333;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--staff-primary);
            transform: translateX(5px);
        }

        .dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: var(--staff-primary);
        }

        .dropdown-divider {
            margin: 8px 0;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* Add new dropdown icon styles */
        .dropdown-icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-left: 8px;
            position: relative;
            transition: transform 0.3s ease;
        }

        .dropdown-icon::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid currentColor;
        }

        .dropdown.show .dropdown-icon {
            transform: rotate(180deg);
        }

        /* Ensure dropdown toggle has proper spacing */
        .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Add this to your CSS style section */
        .booking-actions {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }

        .booking-action-form {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        .booking-action-form button {
            flex: 1;
            padding: 8px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .status-accepted {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .status-declined {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
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
        <div class="staff-content">
            <div class="container">
                <!-- Welcome Message with Staff Name -->
                <div class="welcome-message mb-4">
                    <h2 class="text-white">Hello, <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Staff Member'; ?>!</h2>
                    <p class="text-light">Welcome to your staff dashboard. What would you like to manage today?</p>
                </div>
                
                <!-- Latest Preorder Alert -->
                <?php if (!empty($new_preorders)): ?>
                    <div class="container mt-4">
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading">Latest Preorder Added!</h4>
                            <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($new_preorders[0]['preorder_id']); ?></p>
                            <p><strong>Customer Details:</strong></p>
                            <ul>
                                <li><strong>Name:</strong> <?php echo htmlspecialchars($new_preorders[0]['user_name']); ?></li>
                                <li><strong>Email:</strong> <?php echo htmlspecialchars($new_preorders[0]['email']); ?></li>
                                <li><strong>Phone:</strong> <?php echo htmlspecialchars($new_preorders[0]['phone']); ?></li>
                                <li><strong>Address:</strong> <?php echo htmlspecialchars($new_preorders[0]['address']); ?></li>
                            </ul>
                            <p><strong>Order Details:</strong></p>
                            <ul>
                                <li><strong>Item:</strong> <?php echo htmlspecialchars($new_preorders[0]['item_name']); ?></li>
                                <li><strong>Quantity:</strong> <?php echo htmlspecialchars($new_preorders[0]['quantity']); ?></li>
                                <li><strong>Total Amount:</strong> Rs<?php echo number_format($new_preorders[0]['total_amount'], 2); ?></li>
                                <li><strong>Pickup Date:</strong> <?php echo htmlspecialchars($new_preorders[0]['pickup_date']); ?></li>
                                <li><strong>Pickup Time:</strong> <?php echo htmlspecialchars($new_preorders[0]['pickup_time']); ?></li>
                                <li><strong>Order Status:</strong> <?php echo htmlspecialchars($new_preorders[0]['order_status']); ?></li>
                                <li><strong>Created At:</strong> <?php echo htmlspecialchars($new_preorders[0]['created_at']); ?></li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="quick-actions">
                    <a href="booking-details.php" class="action-button">
                        <i class="fa fa-calendar"></i>
                        <span>Manage Bookings</span>
                    </a>
                    <a href="preorder-management.php" class="action-button">
                        <i class="fa fa-shopping-bag"></i>
                        <span>Manage Preorders</span>
                    </a>
                    <a href="view_deliveries.php" class="action-button">
                        <i class="fa fa-truck"></i>
                        <span>Manage Deliveries</span>
                    </a>
                    <a href="menu-items.php" class="action-button">
                        <i class="fa fa-cutlery"></i>
                        <span>Manage Menu Items</span>
                    </a>
                    <!-- Add other quick action buttons as needed -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script>
        // Custom JavaScript for staff page
        document.addEventListener('DOMContentLoaded', function() {
            // Set current year in footer if the element exists
            const yearElement = document.getElementById('current-year');
            if (yearElement) {
                yearElement.innerHTML = new Date().getFullYear();
            }
            
            // Add print functionality only if the print button exists
            const printButton = document.getElementById('print-bookings');
            if (printButton) {
                printButton.addEventListener('click', function() {
                    window.print();
                });
            }
        });
    </script>
</body>
</html>
