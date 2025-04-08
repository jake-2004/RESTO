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
    // Get total revenue
    $revenue_query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue 
                     FROM home_delivery 
                     WHERE order_date BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($revenue_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $revenue_result = $stmt->get_result();
    $revenue_row = $revenue_result->fetch_assoc();
    $delivery_revenue = $revenue_row['total_revenue'];
    
    // Get preorder revenue
    $preorder_revenue_query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue 
                              FROM preorder 
                              WHERE order_date BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($preorder_revenue_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $preorder_result = $stmt->get_result();
    $preorder_row = $preorder_result->fetch_assoc();
    $preorder_revenue = $preorder_row['total_revenue'];
    
    $total_revenue = $delivery_revenue + $preorder_revenue;
    
    // Get total orders
    $delivery_orders_query = "SELECT COUNT(*) as count 
                             FROM home_delivery 
                             WHERE order_date BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($delivery_orders_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $delivery_result = $stmt->get_result();
    $delivery_row = $delivery_result->fetch_assoc();
    $delivery_orders = $delivery_row['count'];
    
    // Get preorder count
    $preorder_count_query = "SELECT COUNT(*) as count 
                            FROM preorder 
                            WHERE order_date BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($preorder_count_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $preorder_result = $stmt->get_result();
    $preorder_row = $preorder_result->fetch_assoc();
    $preorder_orders = $preorder_row['count'];
    
    $total_orders = $delivery_orders + $preorder_orders;
    
    // Get total bookings
    $bookings_query = "SELECT COUNT(*) as count 
                      FROM booking_details 
                      WHERE booking_date BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($bookings_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
    $bookings_row = $bookings_result->fetch_assoc();
    $total_bookings = $bookings_row['count'];
    
    // Get total customers (unique users who placed orders or made bookings)
    $customers_query = "SELECT COUNT(DISTINCT user_id) as count 
                       FROM (
                           SELECT user_id FROM home_delivery WHERE order_date BETWEEN ? AND ?
                           UNION
                           SELECT user_id FROM preorder WHERE order_date BETWEEN ? AND ?
                           UNION
                           SELECT user_id FROM booking_details WHERE booking_date BETWEEN ? AND ?
                       ) as combined_users";
    
    $stmt = $conn->prepare($customers_query);
    $stmt->bind_param("ssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $customers_result = $stmt->get_result();
    $customers_row = $customers_result->fetch_assoc();
    $total_customers = $customers_row['count'];
    
    // Get revenue data over time (daily)
    $revenue_data_query = "SELECT 
                            DATE(order_date) as date,
                            SUM(total_amount) as daily_revenue
                          FROM (
                              SELECT order_date, total_amount FROM home_delivery WHERE order_date BETWEEN ? AND ?
                              UNION ALL
                              SELECT order_date, total_amount FROM preorder WHERE order_date BETWEEN ? AND ?
                          ) as combined_orders
                          GROUP BY DATE(order_date)
                          ORDER BY date";
    
    $stmt = $conn->prepare($revenue_data_query);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $revenue_data_result = $stmt->get_result();
    
    $revenue_data = [
        'labels' => [],
        'values' => []
    ];
    
    while ($row = $revenue_data_result->fetch_assoc()) {
        $revenue_data['labels'][] = date('M j', strtotime($row['date']));
        $revenue_data['values'][] = (float)$row['daily_revenue'];
    }
    
    // Get orders by type
    $orders_by_type = [
        'delivery' => $delivery_orders,
        'preorder' => $preorder_orders,
        'dine_in' => 0 // This would need to be implemented if dine-in orders are tracked
    ];
    
    // Return the data
    echo json_encode([
        'success' => true,
        'total_revenue' => $total_revenue,
        'total_orders' => $total_orders,
        'total_bookings' => $total_bookings,
        'total_customers' => $total_customers,
        'revenue_data' => $revenue_data,
        'orders_by_type' => $orders_by_type
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