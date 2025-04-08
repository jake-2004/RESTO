<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Define Razorpay constants if not defined in config
if (!defined('rzp_test_7JZmKUBcWxl6xQ')) {
    define('rzp_test_7JZmKUBcWxl6xQ', 'rzp_test_7JZmKUBcWxl6xQ');  // Replace with your actual key
}
if (!defined('DTfkKiBVoQuBxkfAzLuitRdq')) {
    define('DTfkKiBVoQuBxkfAzLuitRdq', 'DTfkKiBVoQuBxkfAzLuitRdq');  // Replace with your actual key
}

// Set a flag to hide notifications before including the header
$hide_notifications = true;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Set current page for navigation highlighting
$current_page = 'cart.php';

try {
    // Get cart items with menu details
    $query = "SELECT c.cart_id, c.quantity, m.menu_id, m.name, m.price, m.image_path 
              FROM Cart c 
              JOIN MenuItems m ON c.menu_id = m.menu_id 
              WHERE c.user_id = ?";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare cart query: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute cart query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
    $total = 0;

    // Get user's address
    $addr_query = "SELECT address FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($addr_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_address = $stmt->get_result()->fetch_assoc()['address'];

    // Add this after the cart items query, before the HTML
    $cart_count = count($cart_items);

} catch (Exception $e) {
    error_log("Error in cart.php: " . $e->getMessage());
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Feane Restaurant">

    <title>Feane Restaurant</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon.png" type="">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    
    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <!-- Add Razorpay SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    
    <style>
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .cart-item-image:hover {
            transform: scale(1.1);
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 15px;
            border-radius: 25px;
            position: relative;
            z-index: 1;
        }
        .quantity-display {
            min-width: 30px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
            color: #ffbe33;
            padding: 5px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .quantity-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            margin-bottom: 10px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 100;
        }
        .quantity-tooltip:after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.8) transparent transparent transparent;
        }
        .quantity-display:hover .quantity-tooltip {
            opacity: 1;
            visibility: visible;
        }
        .quantity-btn {
            padding: 5px 15px;
            background-color: #ffbe33;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .quantity-btn:hover {
            background-color: #e69c00;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .quantity-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .cart-total {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 30px;
            text-align: right;
            padding: 25px;
            background: linear-gradient(45deg, rgba(255, 190, 51, 0.1), rgba(255, 255, 255, 0.05));
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .table {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            color: white;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .table thead th {
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            font-size: 1.1em;
            font-weight: 600;
            padding: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .table td {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            vertical-align: middle;
            padding: 20px 15px;
        }
        .btn-checkout {
            background: linear-gradient(45deg, #ffbe33, #ff9900);
            color: white;
            padding: 12px 35px;
            border-radius: 30px;
            border: none;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 190, 51, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-checkout:hover {
            background: linear-gradient(45deg, #ff9900, #ffbe33);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 190, 51, 0.4);
        }
        .empty-cart-message {
            text-align: center;
            padding: 50px 20px;
            background: linear-gradient(45deg, rgba(255, 190, 51, 0.1), rgba(255, 255, 255, 0.05));
            border-radius: 15px;
            margin: 30px 0;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .btn-browse-menu {
            background: linear-gradient(45deg, #ffbe33, #ff9900);
            color: white;
            padding: 12px 35px;
            border-radius: 30px;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 190, 51, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .btn-browse-menu:hover {
            background: linear-gradient(45deg, #ff9900, #ffbe33);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 190, 51, 0.4);
        }
        .remove-item {
            background-color: transparent;
            border: none;
            color: #ff4444;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 50%;
        }
        .remove-item:hover {
            background-color: rgba(255, 68, 68, 0.1);
            transform: scale(1.1);
        }
        .remove-item i {
            font-size: 1.2em;
        }
        .cart-item-container {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .cart-item-container:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        .item-name {
            font-weight: 600;
            font-size: 1.1em;
            margin-left: 15px;
            color: #222222;
            background: rgba(255, 255, 255, 0.9);
            padding: 8px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .price-column {
            font-weight: 500;
            color: #ffbe33;
        }
        .subtotal-column {
            font-weight: 600;
            color: #ffbe33;
        }
        .heading_container h2 {
            margin-bottom: 40px;
            position: relative;
        }
        .heading_container h2:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #ffbe33;
            border-radius: 3px;
        }
        .order-type-container {
            margin-bottom: 20px;
            background: rgba(26, 26, 26, 0.9);
            padding: 25px;
            border-radius: 12px;
            text-align: left;
            border: 1px solid rgba(255, 190, 51, 0.2);
        }
        .order-type-title {
            font-size: 1.2em;
            color: #ffbe33;
            margin-bottom: 15px;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        .order-type-options {
            display: flex;
            gap: 15px;
        }
        .order-type-option {
            flex: 1;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            color: #222222;
            border: 2px solid transparent;
        }
        .order-type-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .order-type-option.selected {
            border-color: #ffbe33;
            background: #fff;
            box-shadow: 0 4px 12px rgba(255, 190, 51, 0.2);
        }
        .order-type-option i {
            font-size: 1.5em;
            color: #ffbe33;
            margin-bottom: 8px;
            display: block;
        }
        .datetime-picker {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid #ffbe33;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            color: #222222;
            font-size: 1.1em;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .datetime-picker:focus {
            border-color: #ff9900;
            box-shadow: 0 0 0 2px rgba(255, 190, 51, 0.3);
            outline: none;
        }
        .order-form {
            background: rgba(26, 26, 26, 0.9);
            padding: 25px;
            border-radius: 12px;
            margin-top: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 190, 51, 0.2);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #ffbe33;
            font-size: 1.1em;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        .address-display {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px;
            border-radius: 8px;
            color: #222222;
            margin-top: 8px;
            font-size: 1.1em;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(255, 190, 51, 0.3);
        }
        .flatpickr-calendar {
            background: rgba(255, 255, 255, 0.98) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
            border: 2px solid #ffbe33 !important;
        }
        .flatpickr-day.selected {
            background: #ffbe33 !important;
            border-color: #ffbe33 !important;
        }
        .flatpickr-day:hover {
            background: rgba(255, 190, 51, 0.2) !important;
        }
        .flatpickr-time input:hover,
        .flatpickr-time input:focus {
            background: rgba(255, 190, 51, 0.1) !important;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ffbe33;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .nav-link {
            position: relative;
            display: inline-block;
        }

        /* Add these notification styles */
        #notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .notification {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px 25px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .notification.error {
            background: rgba(255, 0, 0, 0.8);
        }

        .notification.success {
            background: rgba(0, 128, 0, 0.8);
        }
    </style>
</head>

<body class="sub_page">
    <!-- Add notification container -->
    <div id="notification-container"></div>
    
    <div class="hero_area">
        <!-- Include the user header -->
        <?php include 'includes/user_header.php'; ?>
    </div>

    <section class="food_section layout_padding">
        <div class="container">
            <div class="heading_container heading_center">
                <h2>Your Cart</h2>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart-message">
                    <h4 class="text-white">Your cart is empty</h4>
                    <p class="text-white-50">Add some delicious items to your cart!</p>
                    <a href="menu.php" class="btn-browse-menu">Browse Menu</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                            ?>
                                <tr>
                                    <td>
                                        <div class="cart-item-container">
                                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="cart-item-image">
                                            <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="price-column">Rs<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <div class="quantity-control">
                                            <button class="quantity-btn update-quantity" 
                                                    data-cart-id="<?php echo $item['cart_id']; ?>" 
                                                    data-action="decrease">-</button>
                                            <div class="quantity-display">
                                                <?php echo $item['quantity']; ?>
                                                <div class="quantity-tooltip">
                                                    Quantity: <?php echo $item['quantity']; ?>
                                                </div>
                                            </div>
                                            <button class="quantity-btn update-quantity" 
                                                    data-cart-id="<?php echo $item['cart_id']; ?>" 
                                                    data-action="increase">+</button>
                                        </div>
                                    </td>
                                    <td class="subtotal-column">Rs<?php echo number_format($subtotal, 2); ?></td>
                                    <td>
                                        <button class="btn btn-danger remove-item" 
                                                data-cart-id="<?php echo $item['cart_id']; ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="cart-total">
                    <div class="order-type-container">
                        <div class="order-type-title">Select Order Type:</div>
                        <div class="order-type-options">
                            <div class="order-type-option" data-type="delivery">
                                <i class="fa fa-truck"></i>
                                <div>Home Delivery</div>
                            </div>
                            <div class="order-type-option" data-type="preorder">
                                <i class="fa fa-clock-o"></i>
                                <div>Pre-order</div>
                            </div>
                        </div>
                    </div>

                    <form id="orderForm" class="order-form" style="display: none;">
                        <!-- Preorder Fields -->
                        <div id="preorderFields" style="display: none;">
                            <div class="form-group">
                                <label for="pickupDate">Pickup Date:</label>
                                <input type="text" id="pickupDate" class="datetime-picker" required>
                            </div>
                            <div class="form-group">
                                <label for="pickupTime">Pickup Time:</label>
                                <input type="text" id="pickupTime" class="datetime-picker" required>
                            </div>
                        </div>

                        <!-- Delivery Fields -->
                        <div id="deliveryFields" style="display: none;">
                            <div class="form-group">
                                <label for="deliveryTime">Delivery Time:</label>
                                <input type="text" id="deliveryTime" class="datetime-picker" required>
                            </div>
                            <div class="form-group">
                                <label>Delivery Address:</label>
                                <div class="address-display">
                                    <?php echo htmlspecialchars($user_address); ?>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="cart-summary">
                        <div class="summary-line subtotal">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <!-- You can add taxes or other fees here if needed -->
                        <div class="summary-line total">
                            <span>Total Amount:</span>
                            <span id="cartTotal">₹<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    
                    <button class="btn-checkout disabled" id="checkoutBtn" disabled>
                        <i class="fa fa-shopping-cart mr-2"></i>
                        Place Order
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
       $(document).ready(function() {
    console.log('Cart page loaded - initializing quantity controls');
    
    // Define a simple notification function if not already defined
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
    
    // Direct event binding to quantity buttons
    $('.update-quantity').on('click', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const cartId = button.data('cart-id');
        const action = button.data('action');
        
        console.log(`Button clicked: ${action} quantity for cart ID ${cartId}`);
        
        // Disable button to prevent multiple clicks
        button.prop('disabled', true);
        
        // Make the AJAX request without alerts
        $.ajax({
            url: 'update_cart.php',
            type: 'POST',
            data: {
                cart_id: cartId,
                action: action
            },
            dataType: 'json',
            success: function(response) {
                console.log('Server response:', response);
                if (response.success) {
                    location.reload();
                } else {
                    showNotification(response.message || 'Error updating quantity', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                console.log('Response Text:', xhr.responseText);
                showNotification('Error updating cart. Please try again.', 'error');
            },
            complete: function() {
                // Re-enable the button
                button.prop('disabled', false);
            }
        });
    });
    
    // Initialize datetime pickers with more specific configurations
    flatpickr("#pickupDate", {
        defaultDate: "today",
        dateFormat: "Y-m-d",
        minDate: "today",
        maxDate: "today", // Restrict to current day only
        disable: [
            function(date) {
                // Disable past dates
                return date < new Date().setHours(0,0,0,0);
            }
        ],
        onChange: function(selectedDates, dateStr, instance) {
            // Update the time picker's min time based on current time
            const now = new Date();
            const selected = selectedDates[0];
            const isToday = selected.toDateString() === now.toDateString();
            
            const timePickr = document.querySelector("#pickupTime")._flatpickr;
            if (isToday) {
                const currentHour = now.getHours();
                const currentMinutes = now.getMinutes();
                const roundedMinutes = Math.ceil(currentMinutes / 30) * 30;
                let minTime = currentHour + ":" + (roundedMinutes === 60 ? "00" : roundedMinutes);
                
                if (roundedMinutes === 60) {
                    minTime = (currentHour + 1) + ":00";
                }
                
                timePickr.set("minTime", minTime);
            } else {
                timePickr.set("minTime", "09:00");
            }
        }
    });

    flatpickr("#pickupTime", {
        enableTime: true,
        noCalendar: true, // Hide the calendar and only show time picker
        dateFormat: "H:i", // Only format for time
        minTime: function() {
            // Get current time and add 30 minutes
            const now = new Date();
            const currentHour = now.getHours();
            const currentMinutes = now.getMinutes();
            const roundedMinutes = Math.ceil(currentMinutes / 30) * 30;
            
            if (roundedMinutes === 60) {
                return (currentHour + 1) + ":00";
            }
            return currentHour + ":" + (roundedMinutes < 10 ? "0" + roundedMinutes : roundedMinutes);
        },
        maxTime: "23:00", // 11 PM
        minuteIncrement: 30,
        defaultDate: new Date().setHours(new Date().getHours() + 1, 0, 0, 0),
        onChange: function(selectedDates, dateStr, instance) {
            validateDateTime();
        }
    });

    flatpickr("#deliveryTime", {
        enableTime: true,
        noCalendar: true, // Hide the calendar and only show time picker
        dateFormat: "H:i", // Only format for time
        minTime: function() {
            // Get current time and add 30 minutes
            const now = new Date();
            const currentHour = now.getHours();
            const currentMinutes = now.getMinutes();
            const roundedMinutes = Math.ceil(currentMinutes / 30) * 30;
            
            if (roundedMinutes === 60) {
                return (currentHour + 1) + ":00";
            }
            return currentHour + ":" + (roundedMinutes < 10 ? "0" + roundedMinutes : roundedMinutes);
        },
        maxTime: "23:00", // 11 PM
        minuteIncrement: 30,
        defaultDate: new Date().setHours(new Date().getHours() + 1, 0, 0, 0),
        onChange: function(selectedDates, dateStr, instance) {
            validateDateTime();
        }
    });

    function validateDateTime() {
        const now = new Date();
        const cutoffTime = new Date();
        cutoffTime.setHours(23, 0, 0, 0);

        // For pickup time
        const pickupDate = $('#pickupDate').val();
        const pickupTime = $('#pickupTime').val();
        if (pickupDate && pickupTime) {
            const selectedPickupDateTime = new Date(pickupDate + ' ' + pickupTime);
            const minimumPickupTime = new Date(now.getTime() + 30 * 60000); // Current time + 30 minutes

            if (selectedPickupDateTime < minimumPickupTime) {
                showNotification('Please select a time at least 30 minutes from now', 'error');
                return false;
            }
            if (selectedPickupDateTime > cutoffTime) {
                showNotification('Orders can only be placed until 11 PM', 'error');
                return false;
            }
        }

        // For delivery time
        const deliveryTime = $('#deliveryTime').val();
        if (deliveryTime) {
            // Create a date object for today with the selected time
            const today = new Date();
            const [hours, minutes] = deliveryTime.split(':').map(Number);
            const selectedDeliveryDateTime = new Date(today);
            selectedDeliveryDateTime.setHours(hours, minutes, 0, 0);
            
            const minimumDeliveryTime = new Date(now.getTime() + 30 * 60000); // Current time + 30 minutes

            if (selectedDeliveryDateTime < minimumDeliveryTime) {
                showNotification('Please select a delivery time at least 30 minutes from now', 'error');
                return false;
            }
            if (selectedDeliveryDateTime > cutoffTime) {
                showNotification('Orders can only be placed until 11 PM', 'error');
                return false;
            }
        }

        return true;
    }

    // Initialize form fields as hidden
    $('#orderForm').hide();
    $('#preorderFields').hide();
    $('#deliveryFields').hide();

    // Function to display the current date above the time fields
    function updateTimeDisplay() {
        const currentDate = new Date();
        const formattedDate = currentDate.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Update delivery time display
        const deliveryTimeInput = document.getElementById('deliveryTime');
        if (deliveryTimeInput) {
            // Add a label above the input to show the current date
            const label = document.createElement('div');
            label.className = 'current-date-label';
            label.style.marginBottom = '5px';
            label.style.color = '#ffbe33';
            label.style.fontWeight = 'bold';
            label.textContent = `Today's Date: ${formattedDate}`;
            
            // Check if the label already exists
            const existingLabel = deliveryTimeInput.parentNode.querySelector('.current-date-label');
            if (!existingLabel) {
                deliveryTimeInput.parentNode.insertBefore(label, deliveryTimeInput);
            } else {
                existingLabel.textContent = `Today's Date: ${formattedDate}`;
            }
        }
        
        // Update pickup time display
        const pickupTimeInput = document.getElementById('pickupTime');
        if (pickupTimeInput) {
            // Add a label above the input to show the current date
            const label = document.createElement('div');
            label.className = 'current-date-label';
            label.style.marginBottom = '5px';
            label.style.color = '#ffbe33';
            label.style.fontWeight = 'bold';
            label.textContent = `Today's Date: ${formattedDate}`;
            
            // Check if the label already exists
            const existingLabel = pickupTimeInput.parentNode.querySelector('.current-date-label');
            if (!existingLabel) {
                pickupTimeInput.parentNode.insertBefore(label, pickupTimeInput);
            } else {
                existingLabel.textContent = `Today's Date: ${formattedDate}`;
            }
        }
    }
    
    // Call the function when the page loads and when the order type is selected
    $(document).ready(function() {
        // Initial call
        updateTimeDisplay();
        
        // Update when order type is selected
        $('.order-type-option').click(function() {
            const orderType = $(this).data('type');
            setTimeout(updateTimeDisplay, 100);
        });
    });

    // Order type selection
    $('.order-type-option').click(function() {
        $('.order-type-option').removeClass('selected');
        $(this).addClass('selected');
        
        const orderType = $(this).data('type');
        $('#orderForm').show();
        
        if (orderType === 'preorder') {
            $('#preorderFields').show();
            $('#deliveryFields').hide();
        } else {
            $('#preorderFields').hide();
            $('#deliveryFields').show();
        }
        
        $('#checkoutBtn').removeClass('disabled').prop('disabled', false);
    });

    // Place order with improved validation
    $(document).ready(function() {
        console.log('Document ready - binding checkout button');
        
        // Direct click handler for checkout button
        $(document).on('click', '#checkoutBtn', function(e) {
            e.preventDefault();
            console.log('Checkout button clicked');
            
            if ($(this).hasClass('disabled')) {
                showNotification('Please select an order type before proceeding', 'error');
                return;
            }

            const orderType = $('.order-type-option.selected').data('type');
            if (!orderType) {
                showNotification('Please select an order type', 'error');
                return;
            }

            let orderData = {
                order_type: orderType
            };

            if (!validateDateTime()) {
                return;
            }

            let isValid = true;

            if (orderType === 'preorder') {
                const pickupTime = $('#pickupTime').val();
                
                if (!pickupTime) {
                    showNotification('Please select pickup time', 'error');
                    isValid = false;
                } else {
                    // For pickup, we only have time, so we need to add today's date
                    const today = new Date();
                    const formattedDate = today.toISOString().split('T')[0]; // YYYY-MM-DD format
                    orderData.pickup_datetime = formattedDate + ' ' + pickupTime;
                }
            } else {
                const deliveryTime = $('#deliveryTime').val();
                
                if (!deliveryTime) {
                    showNotification('Please select delivery time', 'error');
                    isValid = false;
                } else {
                    // For delivery, we only have time, so we need to add today's date
                    const today = new Date();
                    const formattedDate = today.toISOString().split('T')[0]; // YYYY-MM-DD format
                    orderData.delivery_datetime = formattedDate + ' ' + deliveryTime;
                }
            }

            if (!isValid) {
                return;
            }

            console.log('Submitting order with data:', orderData);
            
            // Disable the button to prevent double submission
            $(this).prop('disabled', true);
            
            // Make AJAX call to process_order.php
            $.ajax({
                url: 'process_order.php',
                type: 'POST',
                data: orderData,
                dataType: 'json',
                success: function(response) {
                    console.log('Order processing response:', response);
                    if (response.success) {
                        // Initialize Razorpay payment
                        initiatePayment(orderType, response.order_ids[0]);
                    } else {
                        showNotification(response.message || 'Error processing order', 'error');
                        $('#checkoutBtn').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Order processing error:', error);
                    console.log('Response Text:', xhr.responseText);
                    showNotification('Error processing order. Please try again.', 'error');
                    $('#checkoutBtn').prop('disabled', false);
                }
            });
        });
    });

    function initiatePayment(orderType, orderId) {
        // Get the total amount from the page
        const totalAmount = parseFloat(<?php echo $total; ?>);
        
        if (isNaN(totalAmount) || totalAmount <= 0) {
            showNotification('Invalid order amount', 'error');
            return;
        }
        
        // Convert to paise for Razorpay (multiply by 100)
        const amountInPaise = Math.round(totalAmount * 100);
        
        console.log('Initiating payment for ' + orderType + ' order #' + orderId + ', amount: ' + totalAmount);
        console.log('Order ID being sent: ' + orderId + ' for order type: ' + orderType);
        
        // Create Razorpay options
        var options = {
            "key": "rzp_test_7JZmKUBcWxl6xQ", // Replace with your key
            "amount": amountInPaise.toString(), // Amount in paise
            "currency": "INR",
            "name": "Royal Canteen",
            "description": orderType.charAt(0).toUpperCase() + orderType.slice(1) + " Order Payment",
            "image": "images/logo.png",
            "handler": function (response) {
                processPayment(response, orderType, orderId);
            },
            "prefill": {
                "name": "<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; ?>",
                "email": "<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>",
                "contact": "<?php echo isset($_SESSION['phone']) ? $_SESSION['phone'] : ''; ?>"
            },
            "theme": {
                "color": "#ffbe33"
            }
        };
        
        var rzp1 = new Razorpay(options);
        rzp1.open();
    }

    // Function to process payment after Razorpay success
    function processPayment(paymentData, orderType, orderId) {
        console.log('Processing payment:', paymentData);
        showNotification('Payment successful! Processing...', 'success');
        
        $.ajax({
            url: 'process_payment.php',
            type: 'POST',
            data: {
                razorpay_payment_id: paymentData.razorpay_payment_id,
                order_type: orderType,
                order_id: orderId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Payment response:', response);
                if (response.success) {
                    showNotification('Payment successful!', 'success');
                    // Redirect to order confirmation or orders page
                    setTimeout(function() {
                        window.location.href = 'orders.php';
                    }, 1500);
                } else {
                    showNotification(response.message || 'Payment verification failed', 'error');
                    $('#checkoutBtn').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Payment processing error:', error);
                console.log('Response Text:', xhr.responseText);
                
                // Try to parse the response if it's JSON
                try {
                    var response = JSON.parse(xhr.responseText);
                    showNotification(response.message || 'Error processing payment. Please contact support.', 'error');
                } catch (e) {
                    showNotification('Error processing payment. Please contact support.', 'error');
                }
                
                $('#checkoutBtn').prop('disabled', false);
            }
        });
    }

    // Remove item
    $('.remove-item').click(function() {
        if (confirm('Are you sure you want to remove this item?')) {
            const cartId = $(this).data('cart-id');
            
            $.ajax({
                url: 'remove_from_cart.php',
                method: 'POST',
                data: { cart_id: cartId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Item removed successfully', 'success');
                        // Use a short timeout before reload to allow the notification to be seen
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        showNotification(response.message || 'Error removing item', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error);
                    showNotification('Error removing item. Please try again.', 'error');
                    console.log('Response text:', xhr.responseText);
                }
            });
        }
    });

    // Fix for user dropdown
    $(document).ready(function() {
        // Force close any open dropdowns first
        $('.dropdown-menu').removeClass('show');
        
        // Direct click handler approach (works across Bootstrap versions)
        $(document).on('click', '.dropdown-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close all other dropdowns
            $('.dropdown-menu').not($(this).next('.dropdown-menu')).removeClass('show');
            
            // Toggle the current dropdown with a slight delay to ensure DOM updates
            setTimeout(() => {
                $(this).next('.dropdown-menu').toggleClass('show');
            }, 10);
            
            return false;
        });
        
        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        $(document).on('click', '.dropdown-menu', function(e) {
            e.stopPropagation();
        });
        
        // Log to console for verification
        console.log("Enhanced dropdown fix applied");
    });
});
    </script>

    <!-- custom js -->
    <script src="js/custom.js"></script>
    
    <!-- Notifications script -->
    <script src="js/notifications.js"></script>
    
    <!-- Additional jQuery plugins -->
    <script src="https://unpkg.com/isotope-layout@3.0.4/dist/isotope.pkgd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    
    <!-- Fix for user dropdown -->
    <script>
        $(document).ready(function() {
            // Force close any open dropdowns first
            $('.dropdown-menu').removeClass('show');
            
            // Direct click handler approach (works across Bootstrap versions)
            $(document).on('click', '.dropdown-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other dropdowns
                $('.dropdown-menu').not($(this).next('.dropdown-menu')).removeClass('show');
                
                // Toggle the current dropdown with a slight delay to ensure DOM updates
                setTimeout(() => {
                    $(this).next('.dropdown-menu').toggleClass('show');
                }, 10);
                
                return false;
            });
            
            // Close dropdowns when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').removeClass('show');
                }
            });
            
            // Prevent dropdown from closing when clicking inside it
            $(document).on('click', '.dropdown-menu', function(e) {
                e.stopPropagation();
            });
            
            // Log to console for verification
            console.log("Enhanced dropdown fix applied");
        });
    </script>

    <!-- Add this right after your existing script section -->
    <script>
        // Define the showNotification function
        window.showNotification = function(message, type) {
            const container = $('#notification-container');
            const notification = $(`
                <div class="notification ${type}">
                    <div class="icon">
                        ${type === 'success' ? '<i class="fa fa-check"></i>' : '<i class="fa fa-exclamation-circle"></i>'}
                    </div>
                    <div class="message">${message}</div>
                    <button class="close-btn">&times;</button>
                </div>
            `);
            
            container.append(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Close button handler
            notification.find('.close-btn').click(function() {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        };
    </script>
</body>
</html>
