<?php
require_once 'check_staff_session.php';
require_once 'config/database.php';

// Initialize variables
$preorders = [];
$message = '';
$message_type = '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Handle status updates for all customer preorders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['status'];
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        $update_query = "UPDATE preorders SET order_status = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_status, $user_id);
        
        if ($update_stmt->execute()) {
            // Create notification for the user
            $notification_message = "Your preorder has been updated to " . ucfirst($new_status);
            $is_read = 0; // 0 = unread
            
            $notification_query = "INSERT INTO notifications (user_id, message, created_at, is_read) 
                                  VALUES (?, ?, NOW(), ?)";
            $notification_stmt = $conn->prepare($notification_query);
            $notification_stmt->bind_param("isi", $user_id, $notification_message, $is_read);
            $notification_stmt->execute();
            
            $conn->commit();
            $message = "All preorders for customer #$user_id updated to " . ucfirst($new_status);
            $message_type = "success";
        } else {
            throw new Exception("Failed to update status");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Fetch preorders with filtering
try {
    $query = "SELECT 
        p.preorder_id, 
        u.user_id,
        u.user_name AS user_name, 
        u.email, 
        u.phone, 
        u.address, 
        m.menu_id,
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
    WHERE p.payment_status = 'paid'";
    
    // Add status filter if not 'all'
    if ($filter_status !== 'all') {
        $query .= " AND p.order_status = ?";
    }
    
    // Add search filter if provided
    if (!empty($search_term)) {
        $query .= " AND (u.user_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR p.preorder_id LIKE ?)";
    }
    
    $query .= " ORDER BY 
        CASE 
            WHEN p.order_status = 'pending' THEN 1
            WHEN p.order_status = 'confirmed' THEN 2
            WHEN p.order_status = 'preparing' THEN 3
            WHEN p.order_status = 'ready' THEN 4
            WHEN p.order_status = 'completed' THEN 5
            WHEN p.order_status = 'cancelled' THEN 6
            ELSE 7
        END,
        u.user_id,
        p.created_at DESC, 
        p.pickup_date ASC, 
        p.pickup_time ASC";
    
    $stmt = $conn->prepare($query);
    
    if ($filter_status !== 'all' && !empty($search_term)) {
        $search_param = "%$search_term%";
        $stmt->bind_param("sssss", $filter_status, $search_param, $search_param, $search_param, $search_param);
    } elseif ($filter_status !== 'all') {
        $stmt->bind_param("s", $filter_status);
    } elseif (!empty($search_term)) {
        $search_param = "%$search_term%";
        $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $all_preorders = $result->fetch_all(MYSQLI_ASSOC);
    
    // Group preorders by user and created_at (as a proxy for order group)
    $preorders_by_user = [];
    foreach ($all_preorders as $preorder) {
        $user_id = $preorder['user_id'];
        // Use combination of user_id, pickup_date, pickup_time as a proxy for order group
        $order_group_key = $user_id . '_' . $preorder['pickup_date'] . '_' . $preorder['pickup_time'] . '_' . substr($preorder['created_at'], 0, 16);
        
        if (!isset($preorders_by_user[$user_id])) {
            $preorders_by_user[$user_id] = [
                'user_info' => [
                    'user_id' => $user_id,
                    'user_name' => $preorder['user_name'],
                    'email' => $preorder['email'],
                    'phone' => $preorder['phone'],
                    'address' => $preorder['address']
                ],
                'order_groups' => []
            ];
        }
        
        if (!isset($preorders_by_user[$user_id]['order_groups'][$order_group_key])) {
            $preorders_by_user[$user_id]['order_groups'][$order_group_key] = [
                'pickup_date' => $preorder['pickup_date'],
                'pickup_time' => $preorder['pickup_time'],
                'order_status' => $preorder['order_status'],
                'created_at' => $preorder['created_at'],
                'items' => [],
                'total_amount' => 0,
                'preorder_ids' => [] // Store all preorder IDs in this group
            ];
        }
        
        $preorders_by_user[$user_id]['order_groups'][$order_group_key]['items'][] = [
            'preorder_id' => $preorder['preorder_id'],
            'menu_id' => $preorder['menu_id'],
            'item_name' => $preorder['item_name'],
            'quantity' => $preorder['quantity'],
            'total_amount' => $preorder['total_amount']
        ];
        
        $preorders_by_user[$user_id]['order_groups'][$order_group_key]['preorder_ids'][] = $preorder['preorder_id'];
        $preorders_by_user[$user_id]['order_groups'][$order_group_key]['total_amount'] += $preorder['total_amount'];
    }
    
    // Sort users by their most recent order
    uasort($preorders_by_user, function($a, $b) {
        $a_latest = max(array_map(function($group) {
            return strtotime($group['created_at']);
        }, $a['order_groups']));
        
        $b_latest = max(array_map(function($group) {
            return strtotime($group['created_at']);
        }, $b['order_groups']));
        
        return $b_latest <=> $a_latest; // Sort descending (newest first)
    });
    
    // Sort each user's order groups by created_at (newest first)
    foreach ($preorders_by_user as &$user_data) {
        uasort($user_data['order_groups'], function($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });
    }
    
} catch (Exception $e) {
    $message = "Error fetching preorders: " . $e->getMessage();
    $message_type = "danger";
}

// Handle status updates for specific order groups
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preorder_ids']) && isset($_POST['status'])) {
    $preorder_ids = explode(',', $_POST['preorder_ids']);
    $new_status = $_POST['status'];
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        $update_query = "UPDATE preorders SET order_status = ? WHERE preorder_id = ?";
        $update_stmt = $conn->prepare($update_query);
        
        // Get the user_id for notifications (from first preorder)
        $user_query = "SELECT user_id FROM preorders WHERE preorder_id = ? LIMIT 1";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("i", $preorder_ids[0]);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        $user_id = $user_data['user_id'];
        
        $success = true;
        foreach ($preorder_ids as $preorder_id) {
            $update_stmt->bind_param("si", $new_status, $preorder_id);
            if (!$update_stmt->execute()) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            // Create notification for the user
            $notification_message = "Your preorder has been updated to " . ucfirst($new_status);
            $is_read = 0; // 0 = unread
            
            $notification_query = "INSERT INTO notifications (user_id, message, created_at, is_read) 
                                  VALUES (?, ?, NOW(), ?)";
            $notification_stmt = $conn->prepare($notification_query);
            $notification_stmt->bind_param("isi", $user_id, $notification_message, $is_read);
            $notification_stmt->execute();
            
            $conn->commit();
            $message = "Order updated to " . ucfirst($new_status);
            $message_type = "success";
        } else {
            throw new Exception("Failed to update status");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Resto Staff Panel - Preorder Management">
    <meta name="author" content="Resto">
    
    <!-- Prevent caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon.png" type="">
    
    <!-- Page Title -->
    <title>Preorder Management - Resto Staff</title>

    <!-- Core CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">

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
        .main-content {
            flex: 1;
            padding: 40px 0;
            color: white;
        }

        /* Preorder card styling */
        .preorder-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .preorder-card:hover {
            background: rgba(76, 175, 80, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .preorder-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }

        .preorder-id {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--staff-primary);
        }

        .preorder-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-confirmed {
            background: rgba(33, 150, 243, 0.2);
            color: #2196F3;
        }

        .status-preparing {
            background: rgba(156, 39, 176, 0.2);
            color: #9c27b0;
        }

        .status-ready {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .status-completed {
            background: rgba(0, 150, 136, 0.2);
            color: #009688;
        }

        .status-cancelled {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
        }

        .preorder-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-group {
            margin-bottom: 10px;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #bdbdbd;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 1rem;
            color: #ffffff;
        }

        .preorder-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .preorder-actions select {
            background: rgba(0, 0, 0, 0.3);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .preorder-actions button {
            background: var(--staff-primary);
            border-color: var(--staff-primary);
        }

        .preorder-actions button:hover {
            background: var(--staff-secondary);
            border-color: var(--staff-secondary);
        }

        /* Filter controls */
        .filter-controls {
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .filter-title {
            color: var(--staff-primary);
            margin-bottom: 15px;
            font-weight: 600;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #bdbdbd;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #e0e0e0;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #bdbdbd;
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

        <!-- Main Content Section -->
        <div class="main-content">
            <div class="container">
                <h2 class="mb-4">Preorder Management</h2>
                
                <!-- Display messages -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Filter Controls -->
                <div class="filter-controls">
                    <h5 class="filter-title">Filter Preorders</h5>
                    <form method="GET" action="" class="row">
                        <div class="col-md-4 mb-3">
                            <label for="status" class="text-white">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="preparing" <?php echo $filter_status === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="ready" <?php echo $filter_status === 'ready' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="search" class="text-white">Search</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, email, phone or order ID" value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Preorders List -->
                <?php if (empty($preorders_by_user)): ?>
                    <div class="empty-state">
                        <i class="fa fa-calendar-o"></i>
                        <h4>No preorders found</h4>
                        <p>There are no preorders matching your current filters.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($preorders_by_user as $user_id => $user_data): ?>
                        <div class="customer-group mb-4">
                            <div class="customer-header bg-dark p-3 rounded-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0"><?php echo htmlspecialchars($user_data['user_info']['user_name']); ?></h4>
                                        <div class="customer-contact">
                                            <span><i class="fa fa-phone mr-2"></i><?php echo htmlspecialchars($user_data['user_info']['phone']); ?></span>
                                            <span class="ml-3"><i class="fa fa-envelope mr-2"></i><?php echo htmlspecialchars($user_data['user_info']['email']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php foreach ($user_data['order_groups'] as $order_group_key => $order_group): ?>
                                <div class="preorder-card mb-3" style="border-radius: 0;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Order #<?php echo substr(md5($order_group_key), 0, 8); ?></h5>
                                        <span class="preorder-status status-<?php echo strtolower($order_group['order_status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($order_group['order_status'])); ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Order Summary Section -->
                                    <div class="order-summary mb-4">
                                        <h5 class="mb-3">Order Items</h5>
                                        <div class="table-responsive">
                                            <table class="table table-dark table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>Quantity</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($order_group['items'] as $item): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                        <td>x<?php echo htmlspecialchars($item['quantity']); ?></td>
                                                        <td>Rs<?php echo number_format($item['total_amount'], 2); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <tr class="table-active">
                                                        <td colspan="2"><strong>Total</strong></td>
                                                        <td><strong>Rs<?php echo number_format($order_group['total_amount'], 2); ?></strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Order Details Section -->
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="detail-group">
                                                <div class="detail-label">Pickup Date & Time</div>
                                                <div class="detail-value">
                                                    <?php echo date('F j, Y', strtotime($order_group['pickup_date'])); ?> at
                                                    <?php echo date('g:i A', strtotime($order_group['pickup_time'])); ?>
                                                </div>
                                            </div>
                                            
                                            <div class="detail-group mt-2">
                                                <div class="detail-label">Order Placed</div>
                                                <div class="detail-value">
                                                    <?php echo date('F j, Y g:i A', strtotime($order_group['created_at'])); ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <small class="text-muted">
                                                    Item IDs: 
                                                    <?php echo implode(', ', array_column($order_group['items'], 'preorder_id')); ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="status-update-form">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="preorder_ids" value="<?php echo implode(',', $order_group['preorder_ids']); ?>">
                                                    <div class="form-group">
                                                        <label for="status-<?php echo substr(md5($order_group_key), 0, 8); ?>">Update Status</label>
                                                        <select name="status" id="status-<?php echo substr(md5($order_group_key), 0, 8); ?>" class="form-control">
                                                            <option value="pending" <?php echo $order_group['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="confirmed" <?php echo $order_group['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                            <option value="preparing" <?php echo $order_group['order_status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                            <option value="ready" <?php echo $order_group['order_status'] === 'ready' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                                            <option value="completed" <?php echo $order_group['order_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                            <option value="cancelled" <?php echo $order_group['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-success btn-block">Update Status</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/custom.js"></script>
    <script>
        // Auto-submit form when status filter changes
        document.getElementById('status').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html> 