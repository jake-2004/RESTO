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
$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Customer';

// Check if required parameters are provided
if (!isset($_GET['type']) || !isset($_GET['time'])) {
    die("Missing required parameters");
}

$order_type = $_GET['type'];
$created_at = $_GET['time'];

// Get order details based on type and timestamp
try {
    if ($order_type === 'preorder') {
        // Get preorder details
        $query = "SELECT p.*, m.name, m.price, m.image_path, u.user_name as customer_name, u.email, u.phone
                 FROM Preorders p 
                 JOIN MenuItems m ON p.menu_id = m.menu_id 
                 JOIN Users u ON p.user_id = u.user_id
                 WHERE p.user_id = ? AND p.created_at = ?
                 ORDER BY p.preorder_id ASC";
    } else if ($order_type === 'delivery') {
        // Get delivery order details
        $query = "SELECT d.*, m.name, m.price, m.image_path, u.user_name as customer_name, u.email, u.phone
                 FROM HomeDeliveryOrders d 
                 JOIN MenuItems m ON d.menu_id = m.menu_id 
                 JOIN Users u ON d.user_id = u.user_id
                 WHERE d.user_id = ? AND d.created_at = ?
                 ORDER BY d.delivery_id ASC";
    } else {
        die("Invalid order type");
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $created_at);
    $stmt->execute();
    $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($order_items)) {
        die("No order found with the provided details");
    }

    // Get order status
    if ($order_type === 'preorder') {
        $order_status = $order_items[0]['order_status'];
    } else {
        $order_status = $order_items[0]['food_status'];
    }
    
    // Check if order status allows for invoice generation
    $allowed_statuses = ['delivered', 'completed'];
    if (!in_array(strtolower($order_status), $allowed_statuses)) {
        // Order is not completed or delivered yet
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Invoice Not Available</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
            <style>
                @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");
                body {
                    font-family: "Poppins", sans-serif;
                    background-color: #f8f9fa;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    padding: 20px;
                }
                .message-container {
                    max-width: 500px;
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                    text-align: center;
                }
                .icon {
                    font-size: 60px;
                    color: #ffc107;
                    margin-bottom: 20px;
                }
                h1 {
                    color: #333;
                    font-size: 24px;
                    margin-bottom: 15px;
                }
                p {
                    color: #666;
                    line-height: 1.6;
                    margin-bottom: 25px;
                }
                .btn {
                    display: inline-block;
                    background: linear-gradient(135deg, #ffbe33, #ff9900);
                    color: white;
                    padding: 10px 20px;
                    border-radius: 5px;
                    text-decoration: none;
                    font-weight: 500;
                    transition: all 0.3s ease;
                }
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 15px rgba(255, 190, 51, 0.3);
                }
                .status {
                    display: inline-block;
                    padding: 5px 15px;
                    border-radius: 20px;
                    font-size: 14px;
                    font-weight: 500;
                    color: white;
                    margin: 10px 0 20px;
                }
                .status-pending { background: #ffc107; color: #000; }
                .status-confirmed { background: #17a2b8; color: white; }
                .status-preparing { background: #6f42c1; color: white; }
                .status-out_for_delivery { background: #6f42c1; color: white; }
                .status-ready { background: #28a745; color: white; }
                .status-cancelled { background: #dc3545; color: white; }
            </style>
        </head>
        <body>
            <div class="message-container">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <h1>Invoice Not Available Yet</h1>
                <p>This order is currently in <span class="status status-' . strtolower($order_status) . '">' . ucfirst($order_status) . '</span> status. An invoice will be available once the order is completed or delivered.</p>
                <a href="orders.php" class="btn"><i class="fas fa-arrow-left"></i> Return to Orders</a>
            </div>
        </body>
        </html>';
        exit;
    }

    // Calculate order totals
    $subtotal = 0;
    foreach ($order_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    // Total is the same as subtotal (removed tax and delivery fee)
    $total = $subtotal;

    // Get customer info from first item
    $customer_name = $order_items[0]['customer_name'];
    $customer_email = $order_items[0]['email'];
    $customer_mobile = $order_items[0]['phone'];
    
    // Get address for delivery orders
    $address = ($order_type === 'delivery') ? $order_items[0]['address'] : '';
    
    // Get pickup details for preorders
    $pickup_date = ($order_type === 'preorder') ? $order_items[0]['pickup_date'] : '';
    $pickup_time = ($order_type === 'preorder') ? $order_items[0]['pickup_time'] : '';
    
    // Generate unique invoice number
    $invoice_number = 'INV-' . date('Ymd', strtotime($created_at)) . '-' . substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);

} catch (Exception $e) {
    die("Error retrieving order details: " . $e->getMessage());
}

// Format date for display
$invoice_date = date('F j, Y', strtotime($created_at));
$invoice_time = date('h:i A', strtotime($created_at));

// Set order type text for display
$order_type_text = ($order_type === 'preorder') ? 'Pre-Order' : 'Home Delivery';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $invoice_number; ?> - Feane Restaurant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #ffbe33, #ff9900);
            color: #fff;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .invoice-header h1 {
            font-size: 28px;
            font-weight: 600;
        }
        
        .invoice-header .logo {
            font-size: 24px;
            font-weight: 700;
        }
        
        .invoice-body {
            padding: 30px;
        }
        
        .info-sections {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .info-section {
            flex: 1;
            min-width: 200px;
        }
        
        .info-section h4 {
            color: #ffbe33;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .info-section p {
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }
        
        .info-section strong {
            color: #333;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            color: #555;
            text-align: left;
            padding: 12px;
            font-weight: 500;
            border-bottom: 2px solid #ffbe33;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            color: #333;
        }
        
        .items-table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .items-table .item-image {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .item-name-cell {
            display: flex;
            align-items: center;
        }
        
        .total-section {
            margin-top: 20px;
            margin-left: auto;
            width: 300px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .total-row.final {
            border-bottom: none;
            font-weight: 600;
            font-size: 18px;
            color: #ffbe33;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #ffbe33;
        }
        
        .invoice-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        
        .invoice-footer p {
            color: #777;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            color: white;
        }
        
        .status-pending { background: #ffc107; color: #000; }
        .status-confirmed { background: #17a2b8; color: white; }
        .status-completed, .status-delivered { background: #28a745; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
        .status-out_for_delivery { background: #6f42c1; color: white; }
        
        .print-btn {
            background: #ffbe33;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            display: block;
            margin: 20px auto;
            transition: background 0.3s ease;
        }
        
        .print-btn:hover {
            background: #ff9900;
        }
        
        .text-right {
            text-align: right;
        }
        
        .thank-you {
            text-align: center;
            margin-top: 30px;
            font-size: 20px;
            color: #ffbe33;
            font-weight: 600;
        }
        
        @media print {
            body {
                background: none;
                padding: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                max-width: 100%;
            }
            
            .print-btn {
                display: none;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div>
                <h1>INVOICE</h1>
                <p style="opacity: 0.8; margin-top: 5px;">#<?php echo $invoice_number; ?></p>
            </div>
            <div class="logo">
                <span>Feane</span>
                <span style="color: #ffbe33;">.</span>
            </div>
        </div>
        
        <div class="invoice-body">
            <div class="info-sections">
                <div class="info-section">
                    <h4>From</h4>
                    <p><strong>Feane Restaurant</strong></p>
                    <p>123 Food Street</p>
                    <p>Colombo, Sri Lanka</p>
                    <p>Phone: +94 123 456 789</p>
                    <p>Email: info@feanerestaurant.com</p>
                </div>
                
                <div class="info-section">
                    <h4>To</h4>
                    <p><strong><?php echo htmlspecialchars($customer_name); ?></strong></p>
                    <?php if ($order_type === 'delivery'): ?>
                        <p><?php echo htmlspecialchars($address); ?></p>
                    <?php endif; ?>
                    <p>Phone: <?php echo htmlspecialchars($customer_mobile); ?></p>
                    <p>Email: <?php echo htmlspecialchars($customer_email); ?></p>
                </div>
                
                <div class="info-section">
                    <h4>Details</h4>
                    <p><strong>Invoice Date:</strong> <?php echo $invoice_date; ?></p>
                    <p><strong>Invoice Time:</strong> <?php echo $invoice_time; ?></p>
                    <p><strong>Order Type:</strong> <?php echo $order_type_text; ?></p>
                    <p>
                        <strong>Status:</strong> 
                        <span class="status-badge status-<?php echo strtolower($order_status); ?>">
                            <?php echo ucfirst($order_status); ?>
                        </span>
                    </p>
                    <?php if ($order_type === 'preorder'): ?>
                        <p><strong>Pickup Date:</strong> <?php echo date('F j, Y', strtotime($pickup_date)); ?></p>
                        <p><strong>Pickup Time:</strong> <?php echo date('h:i A', strtotime($pickup_time)); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td class="item-name-cell">
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td>Rs <?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td class="text-right">Rs <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total-section">
                <div class="total-row final">
                    <span>Total</span>
                    <span>Rs <?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            
            <div class="thank-you">
                Thank you for your order!
            </div>
        </div>
        
        <div class="invoice-footer">
            <p>&copy; <?php echo date('Y'); ?> Feane Restaurant. All rights reserved.</p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
    
    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Invoice</button>
</body>
</html> 