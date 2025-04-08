<?php
require_once 'check_admin_session.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Set a random token in session to verify page state
if (!isset($_SESSION['page_token'])) {
    $_SESSION['page_token'] = bin2hex(random_bytes(32));
}
$pageToken = $_SESSION['page_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Resto Admin Panel - Restaurant Management System">
    <meta name="author" content="Resto">
    <meta name="keywords" content="restaurant, admin, management, food, orders">
    
    <link rel="shortcut icon" href="images/favicon.png" type="">
    <title>Resto Admin - <?php echo htmlspecialchars($_SESSION['email']); ?></title>

    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <!-- Include all the CSS styles from admin.html -->
    <style>
        /* Body alignment and structure */
        body {
            min-height: 100vh;
            background: #1a1a1a;
            color: #ffffff;
            line-height: 1.6;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }

        .hero_area {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .admin-content {
            flex: 1;
            padding: 2rem 4rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .container {
            max-width: 1400px;
            padding: 0 2rem;
            margin: 0 auto;
        }

        .welcome-box {
            text-align: center;
            max-width: 800px;
            margin: 2rem auto 4rem;
        }

        .welcome-box p {
            color: #a0a0a0;
            font-size: 1.1em;
            max-width: 600px;
            margin: 1rem auto;
            line-height: 1.8;
        }

        /* Grid Layout Improvements */
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 0 auto;
            max-width: 1400px;
        }

        /* Card Alignment */
        .stat-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        /* Responsive Spacing */
        @media (max-width: 1200px) {
            .admin-content {
                padding: 2rem;
            }
        }

        @media (max-width: 768px) {
            .admin-content {
                padding: 1.5rem;
            }

            .welcome-box {
                margin: 1rem auto 2rem;
            }

            .stat-cards {
                gap: 1rem;
            }
        }

        @media (max-width: 576px) {
            .admin-content {
                padding: 1rem;
            }

            .welcome-box h1 {
                font-size: 1.8em;
            }

            .stat-card {
                min-height: 180px;
            }
        }

        /* Admin-specific styles */
        :root {
            --admin-primary: #2196F3;
            --admin-secondary: #1976D2;
            --admin-accent: #64B5F6;
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
            position: relative;
            z-index: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .welcome-box {
            text-align: center;
            margin-bottom: 40px;
        }

        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(33, 150, 243, 0.1);
            box-shadow: 0 8px 32px rgba(33, 150, 243, 0.2);
        }

        .stat-card i {
            font-size: 2em;
            color: var(--admin-primary);
            margin-bottom: 15px;
        }

        .stat-card h3 {
            font-size: 1.8em;
            margin: 10px 0;
            color: white;
        }

        .stat-card p {
            color: #bdbdbd;
            margin: 0;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .action-card {
            background: rgba(33, 150, 243, 0.1);
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(33, 150, 243, 0.2);
        }

        .action-card:hover {
            transform: translateY(-3px);
            background: rgba(33, 150, 243, 0.2);
        }

        .action-card i {
            font-size: 2em;
            color: var(--admin-primary);
            margin-bottom: 10px;
        }

        /* Recent Activity */
        .activity-section {
            margin-top: 40px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .activity-list {
            display: grid;
            gap: 15px;
        }

        .activity-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(33, 150, 243, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--admin-primary);
        }

        .activity-details {
            flex: 1;
        }

        .activity-time {
            color: #bdbdbd;
            font-size: 0.9em;
        }

        @media (max-width: 991px) {
            .stat-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .stat-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Enhanced Dropdown Styles */
        .user_option .dropdown-toggle::after {
            display: none;  /* Remove default dropdown arrow */
        }

        .user_option .user_link {
            color: #fff;
            font-size: 1.2em;
            padding: 8px;
            border-radius: 50%;
            background: rgba(33, 150, 243, 0.1);
            transition: all 0.3s ease;
            display: inline-block;
        }

        .user_option .user_link:hover {
            background: rgba(33, 150, 243, 0.2);
            transform: translateY(-2px);
        }

        .admin-dropdown {
            background: rgba(25, 28, 36, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin-top: 10px;
            min-width: 220px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            padding: 0;
            overflow: hidden;
        }

        .admin-header {
            padding: 16px;
            background: rgba(33, 150, 243, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-header i {
            color: var(--admin-primary);
            font-size: 1.2em;
        }

        .admin-header span {
            color: #fff;
            font-size: 0.9em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-dropdown .dropdown-item {
            color: #fff;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .admin-dropdown .dropdown-item i {
            width: 20px;
            text-align: center;
            font-size: 1.1em;
        }

        .admin-dropdown .dropdown-item:hover {
            background: rgba(33, 150, 243, 0.1);
            color: var(--admin-primary);
        }

        .admin-dropdown .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 0;
        }

        .admin-dropdown .text-danger:hover {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="hero_area">
        <div class="bg-box">
            <img src="images/indexpic.jpeg" alt="Restaurant Background">
        </div>

        <header class="header_section">
            <div class="container">
                <nav class="navbar navbar-expand-lg custom_nav-container">
                    <a class="navbar-brand" href="admin.php">
                        <span>Resto Admin</span>
                    </a>

                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
                        <span class=""></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav mx-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php">
                                    <i class="fa fa-tachometer"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="adminlist.php">
                                    <i class="fa fa-users"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="change_password.php">
                                    <i class="fa fa-cog"></i> Settings
                                </a>
                            </li>
                        </ul>
                        
                        <div class="user_option">
                            <div class="dropdown">
                                <a href="#" class="user_link dropdown-toggle" id="adminDropdown" 
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-user-secret"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right admin-dropdown">
                                    <div class="admin-header">
                                        <i class="fa fa-user-secret"></i>
                                        <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                                    </div>
                                    <a class="dropdown-item" href="change_password.php">
                                        <i class="fa fa-cog"></i> Settings
                                    </a>
                                    
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <i class="fa fa-sign-out"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <div class="admin-content">
            <div class="welcome-box">
                <h1>Welcome to Resto Admin Panel</h1>
                <p>Manage your restaurant operations efficiently from one central dashboard.</p>
                
                <div class="stat-cards">
                    <div class="stat-card">
                        <i class="fa fa-truck"></i>
                        <h3 id="deliveryCount">0</h3>
                        <p>Delivery Orders</p>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-clock-o"></i>
                        <h3 id="preorderCount">0</h3>
                        <p>Preorders</p>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-calendar"></i>
                        <h3 id="reservationCount">0</h3>
                        <p>Reservations</p>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-users"></i>
                        <h3 id="customerCount">0</h3>
                        <p>Customers</p>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-money"></i>
                        <h3 id="revenueTotal">$0</h3>
                        <p>Revenue</p>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-id-badge"></i>
                        <h3 id="staffCount">0</h3>
                        <p>Staff Members</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/custom.js"></script>

    <script>
        // Store the page token for session verification
        const pageToken = '<?php echo $pageToken; ?>';

        $(document).ready(function() {
            // Your existing admin panel JavaScript code here
            $('#customerCount').html('<i class="fa fa-spinner fa-spin"></i>');
            $('#revenueTotal').html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: 'get_customer_count.php',
                method: 'GET',
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.error) {
                            console.error('Server error:', data.error);
                            $('#customerCount').text('--');
                            return;
                        }
                        if (data.customer_count !== undefined) {
                            const count = formatNumber(data.customer_count);
                            $('#customerCount').fadeOut(200, function() {
                                $(this).text(count).fadeIn(200);
                            });
                        }
                    } catch (error) {
                        console.error('Error parsing response:', error);
                        $('#customerCount').text('--');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching customer count:', error);
                    $('#customerCount').text('--');
                }
            });

            // Update current time
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit'
                });
                $('.current-time').text(timeString);
            }
            
            updateTime();
            setInterval(updateTime, 60000);

            // Update the reservation count function with debugging
            function updateReservationCount() {
                $.ajax({
                    url: 'get_reservation_count.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.count !== undefined) {
                            const count = formatNumber(response.count);
                            $('#reservationCount').fadeOut(200, function() {
                                $(this).text(count).fadeIn(200);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching reservation count:', error);
                        $('#reservationCount').text('--');
                    }
                });
            }
            
            // Call this function when the page loads and every 5 minutes
            updateReservationCount();
            setInterval(updateReservationCount, 300000);

            // Update the delivery and preorder counts
            function updateOrderCounts() {
                $.ajax({
                    url: 'get_order_counts.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.delivery_count !== undefined) {
                            const deliveryCount = formatNumber(response.delivery_count);
                            $('#deliveryCount').fadeOut(200, function() {
                                $(this).text(deliveryCount).fadeIn(200);
                            });
                        }
                        if (response.preorder_count !== undefined) {
                            const preorderCount = formatNumber(response.preorder_count);
                            $('#preorderCount').fadeOut(200, function() {
                                $(this).text(preorderCount).fadeIn(200);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching order counts:', error);
                        $('#deliveryCount, #preorderCount').text('--');
                    }
                });
            }

            // Initial load and refresh of order counts
            updateOrderCounts();
            setInterval(updateOrderCounts, 300000); // Refresh every 5 minutes

            // Update the revenue (total from home deliveries and preorders)
            function updateRevenue() {
                $('#revenueTotal').html('<i class="fa fa-spinner fa-spin"></i>');
                
                $.ajax({
                    url: 'get_revenue_data.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && typeof response.total_revenue !== 'undefined') {
                            const formattedRevenue = formatCurrency(response.total_revenue);
                            $('#revenueTotal').fadeOut(200, function() {
                                $(this).html(formattedRevenue).fadeIn(200);
                            });
                        } else {
                            console.error('Invalid response format:', response);
                            $('#revenueTotal').html('$--');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching revenue data:', error);
                        $('#revenueTotal').html('$--');
                    }
                });
            }
            
            // Call immediately and set up interval
            updateRevenue(); // Initial call
            setInterval(updateRevenue, 300000); // Refresh every 5 minutes

            // Update the staff count
            function updateStaffCount() {
                $.ajax({
                    url: 'get_staff_count.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.count !== undefined) {
                            const count = formatNumber(response.count);
                            $('#staffCount').fadeOut(200, function() {
                                $(this).text(count).fadeIn(200);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching staff count:', error);
                        $('#staffCount').text('--');
                    }
                });
            }

            // Call staff count update function on load and every 5 minutes
            updateStaffCount();
            setInterval(updateStaffCount, 300000);
        });

        function formatNumber(num) {
            if (typeof num !== 'number') {
                num = parseInt(num);
            }
            if (isNaN(num)) return '--';
            return num > 999 ? (num/1000).toFixed(1) + 'k' : num.toString();
        }
        
        function formatCurrency(amount) {
            if (typeof amount !== 'number') {
                amount = parseFloat(amount);
            }
            if (isNaN(amount)) return '$--';
            
            return amount >= 1000 
                ? '$' + (amount/1000).toFixed(1) + 'k' 
                : '$' + amount.toFixed(2);
        }
    </script>
</body>
</html>
