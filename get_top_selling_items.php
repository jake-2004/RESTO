<?php
require_once 'check_admin_session.php';
require_once 'db_connection.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Get date range from query parameters or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Validate dates
if (!validateDate($start_date) || !validateDate($end_date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid date format. Please use YYYY-MM-DD format.'
    ]);
    exit;
}

try {
    // Get top selling items from home delivery orders
    $delivery_items_query = "SELECT 
                                m.item_name,
                                m.category,
                                SUM(od.quantity) as total_quantity,
                                SUM(od.quantity * od.price) as total_revenue
                            FROM order_details od
                            JOIN menu m ON od.item_id = m.id
                            JOIN home_delivery hd ON od.order_id = hd.id
                            WHERE hd.order_date BETWEEN ? AND ?
                            GROUP BY m.id, m.item_name, m.category
                            ORDER BY total_quantity DESC
                            LIMIT 10";
    
    $stmt = $conn->prepare($delivery_items_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $delivery_result = $stmt->get_result();
    
    $delivery_items = [];
    while ($row = $delivery_result->fetch_assoc()) {
        $delivery_items[] = [
            'item_name' => $row['item_name'],
            'category' => $row['category'],
            'quantity' => (int)$row['total_quantity'],
            'revenue' => (float)$row['total_revenue'],
            'order_type' => 'Delivery'
        ];
    }
    
    // Get top selling items from preorders
    $preorder_items_query = "SELECT 
                                m.item_name,
                                m.category,
                                SUM(po.quantity) as total_quantity,
                                SUM(po.quantity * po.price) as total_revenue
                            FROM preorder_details po
                            JOIN menu m ON po.item_id = m.id
                            JOIN preorder p ON po.preorder_id = p.id
                            WHERE p.order_date BETWEEN ? AND ?
                            GROUP BY m.id, m.item_name, m.category
                            ORDER BY total_quantity DESC
                            LIMIT 10";
    
    $stmt = $conn->prepare($preorder_items_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $preorder_result = $stmt->get_result();
    
    $preorder_items = [];
    while ($row = $preorder_result->fetch_assoc()) {
        $preorder_items[] = [
            'item_name' => $row['item_name'],
            'category' => $row['category'],
            'quantity' => (int)$row['total_quantity'],
            'revenue' => (float)$row['total_revenue'],
            'order_type' => 'Preorder'
        ];
    }
    
    // Combine and sort all items by quantity
    $all_items = array_merge($delivery_items, $preorder_items);
    
    // Group by item name and sum quantities and revenue
    $grouped_items = [];
    foreach ($all_items as $item) {
        $key = $item['item_name'] . '_' . $item['category'];
        if (!isset($grouped_items[$key])) {
            $grouped_items[$key] = [
                'item_name' => $item['item_name'],
                'category' => $item['category'],
                'quantity' => 0,
                'revenue' => 0,
                'order_types' => []
            ];
        }
        $grouped_items[$key]['quantity'] += $item['quantity'];
        $grouped_items[$key]['revenue'] += $item['revenue'];
        $grouped_items[$key]['order_types'][] = $item['order_type'];
    }
    
    // Convert to array and sort by quantity
    $top_items = array_values($grouped_items);
    usort($top_items, function($a, $b) {
        return $b['quantity'] - $a['quantity'];
    });
    
    // Take top 10 items
    $top_items = array_slice($top_items, 0, 10);
    
    // Format order types as comma-separated string
    foreach ($top_items as &$item) {
        $item['order_types'] = implode(', ', array_unique($item['order_types']));
    }
    
    // Return the data
    echo json_encode([
        'success' => true,
        'items' => $top_items
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Helper function to validate date format
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Close the database connection
$conn->close();
?> 