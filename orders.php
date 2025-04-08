<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Set current page for navigation highlighting
$current_page = 'orders.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get preorders
    $preorder_query = "SELECT p.*, m.name, m.price, m.image_path 
                       FROM Preorders p 
                       JOIN MenuItems m ON p.menu_id = m.menu_id 
                       WHERE p.user_id = ? 
                       ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($preorder_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $preorders_raw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Group preorders by created_at timestamp
    $preorders_grouped = [];
    foreach ($preorders_raw as $order) {
        $created_at = $order['created_at'];
        if (!isset($preorders_grouped[$created_at])) {
            $preorders_grouped[$created_at] = [
                'items' => [],
                'total' => 0,
                'pickup_date' => $order['pickup_date'],
                'pickup_time' => $order['pickup_time'],
                'order_status' => $order['order_status'],
                'user_id' => $order['user_id'],
                'created_at' => $order['created_at']
            ];
        }
        
        $preorders_grouped[$created_at]['items'][] = $order;
        $preorders_grouped[$created_at]['total'] += $order['price'] * $order['quantity'];
    }
    
    // Convert to indexed array for easier iteration in template
    $preorders = array_values($preorders_grouped);

    // Get delivery orders
    $delivery_query = "SELECT d.*, m.name, m.price, m.image_path 
                      FROM HomeDeliveryOrders d 
                      JOIN MenuItems m ON d.menu_id = m.menu_id 
                      WHERE d.user_id = ? 
                      ORDER BY d.food_status ASC, d.created_at DESC";
    $stmt = $conn->prepare($delivery_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $delivery_orders_raw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Group delivery orders by created_at timestamp
    $delivery_orders_grouped = [];
    foreach ($delivery_orders_raw as $order) {
        $created_at = $order['created_at'];
        if (!isset($delivery_orders_grouped[$created_at])) {
            $delivery_orders_grouped[$created_at] = [
                'items' => [],
                'total' => 0,
                'address' => $order['address'],
                'food_status' => $order['food_status'],
                'delivery_time' => $order['delivery_time'],
                'user_id' => $order['user_id'],
                'created_at' => $order['created_at']
            ];
        }
        
        $delivery_orders_grouped[$created_at]['items'][] = $order;
        $delivery_orders_grouped[$created_at]['total'] += $order['price'] * $order['quantity'];
    }
    
    // Convert to indexed array for easier iteration in template
    $delivery_orders = array_values($delivery_orders_grouped);

} catch (Exception $e) {
    error_log("Error in orders.php: " . $e->getMessage());
    $error_message = $e->getMessage();
    // Initialize empty arrays if queries fail
    $preorders = [];
    $delivery_orders = [];
}

// When updating order status
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $user_id = $_POST['user_id'];
    $delivery_time = $_POST['delivery_time'];
    $new_status = $_POST['new_status'];
    $order_type = $_POST['order_type'];
    
    // Only process delivery orders (removed preorder handling)
    if ($order_type === 'delivery') {
        try {
            // Update all items in the order with the same delivery time and user
            $update_query = "UPDATE HomeDeliveryOrders 
                            SET food_status = ? 
                            WHERE user_id = ? 
                            AND delivery_time = ?";
            
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sis", $new_status, $user_id, $delivery_time);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Order status updated successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Failed to update order status: " . $conn->error;
                $_SESSION['message_type'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }
    
    // Redirect back to the orders page
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        .order-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .order-section {
            background: rgba(15, 15, 15, 0.6);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 190, 51, 0.1);
        }
        .section-title {
            color: #ffbe33;
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 190, 51, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i {
            font-size: 1.2em;
        }
        .order-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .order-type {
            background: #ffbe33;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            background: #ffbe33;
            color: #000;
        }
        .status-pending { 
            background: #ffc107; 
            color: #000;
        }
        .status-confirmed { 
            background: #17a2b8; 
            color: white;
        }
        .status-completed, .status-delivered { 
            background: #28a745; 
            color: white;
        }
        .status-cancelled { 
            background: #dc3545; 
            color: white;
        }
        .status-out_for_delivery {
            background: #6f42c1;
            color: white;
        }
        .order-details {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .order-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 190, 51, 0.3);
        }
        .order-info {
            flex-grow: 1;
        }
        .order-name {
            font-size: 1.2em;
            font-weight: 600;
            color: #ffbe33;
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .order-meta {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95em;
            line-height: 1.6;
        }
        .order-price {
            font-weight: 600;
            color: #ffbe33;
            font-size: 1.2em;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .no-orders {
            text-align: center;
            padding: 30px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            color: white;
            border: 1px solid rgba(255, 190, 51, 0.1);
        }
        .no-orders h4 {
            color: #ffbe33;
            margin-bottom: 10px;
        }
        .btn-browse {
            background: linear-gradient(45deg, #ffbe33, #ff9900);
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(255, 190, 51, 0.3);
        }
        .btn-browse:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 190, 51, 0.4);
            color: white;
            text-decoration: none;
        }
        .empty-section-message {
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
        /* Invoice button styles */
        .btn-invoice {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(45deg, #4a90e2, #5e35b1);
            color: white;
            padding: 8px 18px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
        }
        
        .btn-invoice:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
            color: white;
            text-decoration: none;
        }
        /* Back button styles - enhanced */
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
    </style>
</head>

<body class="sub_page">
    <div class="hero_area">
        <div class="bg-box">
            <img src="images/indexpic.jpeg" alt="">
        </div>
        <?php include 'includes/user_header.php'; ?>
    </div>

    <section class="food_section layout_padding">
        <div class="container">
            <!-- Back to Home button with enhanced styling -->
            <a href="user.php" class="back-btn">
                <i class="fa fa-arrow-left"></i> Back to Home
            </a>
            
            <div class="heading_container heading_center">
                <h2>Your Orders</h2>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($preorders) && empty($delivery_orders)): ?>
                <div class="no-orders">
                    <h4>No orders found</h4>
                    <p>You haven't placed any orders yet.</p>
                    <a href="menu.php" class="btn-browse">Browse Menu</a>
                </div>
            <?php else: ?>
                <div class="order-sections">
                    <!-- Preorders Section -->
                    <div class="order-section">
                        <div class="section-title">
                            <i class="fa fa-clock-o"></i>
                            Pre-orders
                        </div>
                        <?php if (empty($preorders)): ?>
                            <div class="empty-section-message">No pre-orders found</div>
                        <?php else: ?>
                            <?php foreach ($preorders as $order_group): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <span class="order-type">Pre-order</span>
                                        <span class="order-status status-<?php echo strtolower($order_group['order_status'] ?? 'pending'); ?>">
                                            <?php echo ucfirst($order_group['order_status'] ?? 'pending'); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="order-meta" style="margin-bottom: 15px;">
                                        <strong>Order placed:</strong> <?php echo date('M d, Y h:i A', strtotime($order_group['created_at'])); ?><br>
                                        <strong>Pickup:</strong> 
                                        <?php echo date('M d, Y', strtotime($order_group['pickup_date'])); ?> 
                                        at <?php echo date('h:i A', strtotime($order_group['pickup_time'])); ?>
                                    </div>
                                    
                                    <!-- Order items -->
                                    <div class="bill-items">
                                        <h4 style="color: #ffbe33; margin-bottom: 10px;">Order Items</h4>
                                        <table class="bill-table" style="width: 100%; margin-bottom: 15px;">
                                            <thead>
                                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left;">
                                                    <th style="padding: 8px;">Item</th>
                                                    <th style="padding: 8px; text-align: center;">Qty</th>
                                                    <th style="padding: 8px; text-align: right;">Price</th>
                                                    <th style="padding: 8px; text-align: right;">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order_group['items'] as $item): ?>
                                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                                        <td style="padding: 8px;">
                                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                                                    alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                                                <?php echo htmlspecialchars($item['name']); ?>
                                                            </div>
                                                        </td>
                                                        <td style="padding: 8px; text-align: center;"><?php echo $item['quantity']; ?></td>
                                                        <td style="padding: 8px; text-align: right;">Rs<?php echo number_format($item['price'], 2); ?></td>
                                                        <td style="padding: 8px; text-align: right;">Rs<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr style="font-weight: bold; border-top: 2px solid rgba(255,190,51,0.3);">
                                                    <td colspan="3" style="padding: 10px; text-align: right;">Total:</td>
                                                    <td style="padding: 10px; text-align: right; color: #ffbe33; font-size: 1.1em;">Rs<?php echo number_format($order_group['total'], 2); ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    
                                    <!-- Add Invoice Button -->
                                    <div class="invoice-action" style="text-align: right; margin-top: 15px;">
                                        <a href="generate_invoice.php?type=preorder&time=<?php echo urlencode($order_group['created_at']); ?>" 
                                           class="btn-invoice" target="_blank">
                                            <i class="fa fa-file-text-o"></i> View Invoice
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Home Delivery Section -->
                    <div class="order-section">
                        <div class="section-title">
                            <i class="fa fa-truck"></i>
                            Home Delivery
                        </div>
                        <?php if (empty($delivery_orders)): ?>
                            <div class="empty-section-message">No delivery orders found</div>
                        <?php else: ?>
                            <?php foreach ($delivery_orders as $order_group): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <span class="order-type">Home Delivery</span>
                                        <span class="order-status status-<?php echo strtolower($order_group['food_status'] ?? 'pending'); ?>">
                                            <?php echo ucfirst($order_group['food_status'] ?? 'pending'); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="order-meta" style="margin-bottom: 15px;">
                                        <strong>Order placed:</strong> <?php echo date('M d, Y h:i A', strtotime($order_group['created_at'])); ?><br>
                                        <strong>Delivery:</strong> <?php echo date('M d, Y h:i A', strtotime($order_group['delivery_time'])); ?><br>
                                        <strong>Address:</strong> <?php echo htmlspecialchars($order_group['address']); ?>
                                    </div>
                                    
                                    <!-- Order items -->
                                    <div class="bill-items">
                                        <h4 style="color: #ffbe33; margin-bottom: 10px;">Order Items</h4>
                                        <table class="bill-table" style="width: 100%; margin-bottom: 15px;">
                                            <thead>
                                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left;">
                                                    <th style="padding: 8px;">Item</th>
                                                    <th style="padding: 8px; text-align: center;">Qty</th>
                                                    <th style="padding: 8px; text-align: right;">Price</th>
                                                    <th style="padding: 8px; text-align: right;">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order_group['items'] as $item): ?>
                                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                                        <td style="padding: 8px;">
                                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                                                    alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                                                <?php echo htmlspecialchars($item['name']); ?>
                                                            </div>
                                                        </td>
                                                        <td style="padding: 8px; text-align: center;"><?php echo $item['quantity']; ?></td>
                                                        <td style="padding: 8px; text-align: right;">Rs<?php echo number_format($item['price'], 2); ?></td>
                                                        <td style="padding: 8px; text-align: right;">Rs<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr style="font-weight: bold; border-top: 2px solid rgba(255,190,51,0.3);">
                                                    <td colspan="3" style="padding: 10px; text-align: right;">Total:</td>
                                                    <td style="padding: 10px; text-align: right; color: #ffbe33; font-size: 1.1em;">Rs<?php echo number_format($order_group['total'], 2); ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    
                                    <!-- Add Invoice Button -->
                                    <div class="invoice-action" style="text-align: right; margin-top: 15px;">
                                        <a href="generate_invoice.php?type=delivery&time=<?php echo urlencode($order_group['created_at']); ?>" 
                                           class="btn-invoice" target="_blank">
                                            <i class="fa fa-file-text-o"></i> View Invoice
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
