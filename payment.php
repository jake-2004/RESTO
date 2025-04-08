<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Razorpay credentials
$key_id = 'rzp_test_7JZmKUBcWxl6xQ';
$key_secret = 'DTfkKiBVoQuBxkfAzLuitRdq';

// Get order details from session or database
$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
$order_type = isset($_GET['type']) ? $_GET['type'] : null;

// Initialize variables
$amount = 0;
$items = [];
$order_details = [];

try {
    if ($order_type == 'delivery') {
        // For delivery orders
        $query = "SELECT d.*, m.name, m.price, m.image_path 
                  FROM HomeDeliveryOrders d 
                  JOIN MenuItems m ON d.menu_id = m.menu_id 
                  WHERE d.user_id = ? AND d.delivery_time = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $order_id); // order_id is actually delivery_time
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        
        if (!empty($items)) {
            // Calculate total amount
            foreach ($items as $item) {
                $amount += $item['price'] * $item['quantity'];
            }
            
            $order_details = [
                'type' => 'delivery',
                'delivery_time' => $order_id,
                'address' => $items[0]['address']
            ];
        }
    } else if ($order_type == 'preorder') {
        // First get the reference preorder's details
        $reference_query = "SELECT created_at, pickup_date, pickup_time 
                          FROM Preorders 
                          WHERE preorder_id = ? AND user_id = ?";
        
        $stmt = $conn->prepare($reference_query);
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $reference_result = $stmt->get_result();
        $reference_order = $reference_result->fetch_assoc();
        
        if ($reference_order) {
            // Get all preorders from the same batch (same created_at timestamp)
            $query = "SELECT p.*, m.name, m.price, m.image_path 
                      FROM Preorders p 
                      JOIN MenuItems m ON p.menu_id = m.menu_id 
                      WHERE p.user_id = ? 
                      AND DATE(p.created_at) = DATE(?) 
                      AND TIME(p.created_at) = TIME(?)
                      AND p.pickup_date = ? 
                      AND p.pickup_time = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issss", $user_id, $reference_order['created_at'], 
                             $reference_order['created_at'], 
                             $reference_order['pickup_date'], 
                             $reference_order['pickup_time']);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = $result->fetch_all(MYSQLI_ASSOC);
            
            if (!empty($items)) {
                // Calculate total amount
                foreach ($items as $item) {
                    $amount += $item['price'] * $item['quantity'];
                }
                
                $order_details = [
                    'type' => 'preorder',
                    'preorder_id' => $order_id, // Keep the reference ID
                    'pickup_date' => $items[0]['pickup_date'],
                    'pickup_time' => $items[0]['pickup_time'],
                    'created_at' => $items[0]['created_at']
                ];
            }
        }
    }
    
    // If no items found
    if (empty($items)) {
        $_SESSION['message'] = "Order not found";
        $_SESSION['message_type'] = "danger";
        header("Location: orders.php");
        exit();
    }
    
    // Store order details in session for verification after payment
    $_SESSION['payment_order'] = [
        'amount' => $amount * 100, // Razorpay requires amount in paise
        'order_details' => $order_details,
        'items' => $items
    ];
    
} catch (Exception $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    header("Location: orders.php");
    exit();
}

// Get user details for prefilling payment form
$user_query = "SELECT * FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <?php include 'includes/header.php'; ?>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: rgba(15, 15, 15, 0.6);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 190, 51, 0.1);
            color: white;
        }
        
        .order-summary {
            margin-bottom: 30px;
        }
        
        .order-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .item-details {
            flex-grow: 1;
        }
        
        .item-name {
            font-weight: bold;
            font-size: 1.1em;
            color: #ffbe33;
        }
        
        .total-section {
            display: flex;
            justify-content: space-between;
            font-size: 1.2em;
            padding-top: 15px;
            margin-top: 15px;
            border-top: 2px solid rgba(255, 190, 51, 0.3);
        }
        
        .total-amount {
            font-weight: bold;
            color: #ffbe33;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(45deg, #ffbe33, #ff9900);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            margin: 20px 0;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            border: none;
            box-shadow: 0 4px 15px rgba(255, 190, 51, 0.3);
        }
        
        .back-btn i {
            margin-right: 8px;
            font-size: 1.1em;
        }
        
        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 190, 51, 0.5);
            color: white;
            text-decoration: none;
        }
        
        .pay-btn {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1.1em;
            cursor: pointer;
            display: block;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }
        
        .pay-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.5);
        }
        
        .order-details {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .order-details p {
            margin: 5px 0;
        }
        
        .section-title {
            color: #ffbe33;
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 190, 51, 0.2);
        }
    </style>
</head>

<body class="sub_page">
    <div class="hero_area">
        <div class="bg-box">
            <img src="images/indexpic.jpeg" alt="">
        </div>
    </div>

    <section class="food_section layout_padding">
        <div class="container">
            <a href="orders.php" class="back-btn">
                <i class="fa fa-arrow-left"></i> Back to Orders
            </a>
            
            <div class="heading_container heading_center">
                <h2>Payment</h2>
            </div>
            
            <div class="payment-container">
                <h3 class="section-title">Order Summary</h3>
                
                <div class="order-details">
                    <?php if ($order_type == 'delivery'): ?>
                        <p><strong>Order Type:</strong> Home Delivery</p>
                        <p><strong>Delivery Time:</strong> <?php echo date('M d, Y h:i A', strtotime($order_details['delivery_time'])); ?></p>
                        <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($order_details['address']); ?></p>
                    <?php else: ?>
                        <p><strong>Order Type:</strong> Pre-order</p>
                        <p><strong>Pickup Date:</strong> <?php echo date('M d, Y', strtotime($order_details['pickup_date'])); ?></p>
                        <p><strong>Pickup Time:</strong> <?php echo date('h:i A', strtotime($order_details['pickup_time'])); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="order-summary">
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="item-image" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                <div class="item-price">Price: Rs<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                            <div class="item-total">
                                Rs<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="total-section">
                        <span>Total Amount:</span>
                        <span class="total-amount">Rs<?php echo number_format($amount, 2); ?></span>
                    </div>
                </div>
                
                <button id="rzp-button" class="pay-btn">Pay Now</button>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        var options = {
            "key": "<?php echo $key_id; ?>",
            "amount": "<?php echo $amount * 100; ?>", // Razorpay accepts amount in paise
            "currency": "INR",
            "name": "RESTO",
            "description": "Order Payment",
            "image": "images/logo.png", // Replace with your logo URL
            "prefill": {
                "name": "<?php echo $user['first_name'] . ' ' . $user['last_name']; ?>",
                "email": "<?php echo $user['email']; ?>",
                "contact": "<?php echo $user['phone'] ?? ''; ?>"
            },
            "theme": {
                "color": "#ffbe33"
            },
            "handler": function (response) {
                // Handle successful payment
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('payment-form').submit();
            }
        };
        
        var rzp = new Razorpay(options);
        document.getElementById('rzp-button').onclick = function(e){
            rzp.open();
            e.preventDefault();
        }
    </script>
    
    <!-- Hidden form to submit payment details to server -->
    <form id="payment-form" action="process_payment.php" method="post" style="display: none;">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="order_type" value="<?php echo $order_type; ?>">
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
    </form>
</body>
</html> 