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
    // Get daily sales data
    $daily_sales_query = "SELECT 
                            DATE(order_date) as date,
                            SUM(total_amount) as total_sales,
                            COUNT(*) as order_count
                        FROM (
                            SELECT order_date, total_amount FROM home_delivery WHERE order_date BETWEEN ? AND ?
                            UNION ALL
                            SELECT order_date, total_amount FROM preorder WHERE order_date BETWEEN ? AND ?
                        ) combined_orders
                        GROUP BY DATE(order_date)
                        ORDER BY date";
    
    $stmt = $conn->prepare($daily_sales_query);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $daily_sales_result = $stmt->get_result();
    
    $daily_sales_data = [];
    while ($row = $daily_sales_result->fetch_assoc()) {
        $daily_sales_data[] = [
            'date' => $row['date'],
            'total_sales' => (float)$row['total_sales'],
            'order_count' => (int)$row['order_count']
        ];
    }
    
    // Get sales by order type
    $order_type_query = "SELECT 
                            order_type,
                            COUNT(*) as order_count,
                            SUM(total_amount) as total_sales
                        FROM (
                            SELECT 'Home Delivery' as order_type, total_amount FROM home_delivery WHERE order_date BETWEEN ? AND ?
                            UNION ALL
                            SELECT 'Preorder' as order_type, total_amount FROM preorder WHERE order_date BETWEEN ? AND ?
                        ) combined_orders
                        GROUP BY order_type";
    
    $stmt = $conn->prepare($order_type_query);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $order_type_result = $stmt->get_result();
    
    $order_type_data = [];
    while ($row = $order_type_result->fetch_assoc()) {
        $order_type_data[] = [
            'order_type' => $row['order_type'],
            'order_count' => (int)$row['order_count'],
            'total_sales' => (float)$row['total_sales']
        ];
    }
    
    // Get payment method distribution
    $payment_method_query = "SELECT 
                                payment_method,
                                COUNT(*) as payment_count,
                                SUM(total_amount) as total_amount
                            FROM (
                                SELECT payment_method, total_amount FROM home_delivery WHERE order_date BETWEEN ? AND ?
                                UNION ALL
                                SELECT payment_method, total_amount FROM preorder WHERE order_date BETWEEN ? AND ?
                            ) combined_orders
                            GROUP BY payment_method";
    
    $stmt = $conn->prepare($payment_method_query);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $payment_method_result = $stmt->get_result();
    
    $payment_method_data = [];
    while ($row = $payment_method_result->fetch_assoc()) {
        $payment_method_data[] = [
            'payment_method' => $row['payment_method'],
            'payment_count' => (int)$row['payment_count'],
            'total_amount' => (float)$row['total_amount']
        ];
    }
    
    // Calculate summary statistics
    $summary_query = "SELECT 
                        COUNT(*) as total_orders,
                        SUM(total_amount) as total_revenue,
                        AVG(total_amount) as average_order_value
                    FROM (
                        SELECT total_amount FROM home_delivery WHERE order_date BETWEEN ? AND ?
                        UNION ALL
                        SELECT total_amount FROM preorder WHERE order_date BETWEEN ? AND ?
                    ) combined_orders";
    
    $stmt = $conn->prepare($summary_query);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $summary_result = $stmt->get_result();
    $summary_data = $summary_result->fetch_assoc();
    
    // Return the data
    echo json_encode([
        'success' => true,
        'daily_sales' => $daily_sales_data,
        'order_types' => $order_type_data,
        'payment_methods' => $payment_method_data,
        'summary' => [
            'total_orders' => (int)$summary_data['total_orders'],
            'total_revenue' => (float)$summary_data['total_revenue'],
            'average_order_value' => (float)$summary_data['average_order_value']
        ]
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