<?php
require_once 'check_admin_session.php';
require_once 'db_connection.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Get date range parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // Default to first day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Default to last day of current month

// Validate date format
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

if (!validateDate($start_date) || !validateDate($end_date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid date format'
    ]);
    exit;
}

try {
    // Get customer order frequency
    $frequency_query = "
        SELECT 
            CASE 
                WHEN order_count = 1 THEN '1'
                WHEN order_count BETWEEN 2 AND 3 THEN '2-3'
                WHEN order_count BETWEEN 4 AND 5 THEN '4-5'
                ELSE '6+'
            END as frequency_group,
            COUNT(*) as customer_count
        FROM (
            SELECT user_id, COUNT(*) as order_count
            FROM orders
            WHERE order_date BETWEEN ? AND ?
            GROUP BY user_id
        ) as customer_orders
        GROUP BY frequency_group
        ORDER BY 
            CASE frequency_group
                WHEN '1' THEN 1
                WHEN '2-3' THEN 2
                WHEN '4-5' THEN 3
                ELSE 4
            END
    ";
    
    $stmt = $conn->prepare($frequency_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $frequency_result = $stmt->get_result();
    
    $frequency_data = [
        '1' => 0,
        '2-3' => 0,
        '4-5' => 0,
        '6+' => 0
    ];
    
    while ($row = $frequency_result->fetch_assoc()) {
        $frequency_data[$row['frequency_group']] = (int)$row['customer_count'];
    }

    // Calculate customer retention rate
    $retention_query = "
        SELECT 
            COUNT(DISTINCT CASE 
                WHEN order_date BETWEEN ? AND ? 
                THEN user_id 
            END) as returning_customers,
            COUNT(DISTINCT user_id) as total_customers
        FROM orders
        WHERE order_date <= ?
    ";
    
    $stmt = $conn->prepare($retention_query);
    $stmt->bind_param("sss", $start_date, $end_date, $end_date);
    $stmt->execute();
    $retention_result = $stmt->get_result();
    $retention_data = $retention_result->fetch_assoc();
    
    $retention_rate = $retention_data['total_customers'] > 0 
        ? $retention_data['returning_customers'] / $retention_data['total_customers'] 
        : 0;

    // Get average order value by customer segment
    $segment_query = "
        SELECT 
            CASE 
                WHEN AVG(order_total) < 50 THEN 'Low Value'
                WHEN AVG(order_total) < 100 THEN 'Medium Value'
                ELSE 'High Value'
            END as customer_segment,
            COUNT(DISTINCT user_id) as customer_count,
            AVG(order_total) as avg_order_value
        FROM orders
        WHERE order_date BETWEEN ? AND ?
        GROUP BY 
            CASE 
                WHEN AVG(order_total) < 50 THEN 'Low Value'
                WHEN AVG(order_total) < 100 THEN 'Medium Value'
                ELSE 'High Value'
            END
        ORDER BY 
            CASE customer_segment
                WHEN 'Low Value' THEN 1
                WHEN 'Medium Value' THEN 2
                ELSE 3
            END
    ";
    
    $stmt = $conn->prepare($segment_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $segment_result = $stmt->get_result();
    
    $segment_data = [];
    while ($row = $segment_result->fetch_assoc()) {
        $segment_data[] = [
            'segment' => $row['customer_segment'],
            'customer_count' => (int)$row['customer_count'],
            'avg_order_value' => (float)$row['avg_order_value']
        ];
    }

    // Return the data
    echo json_encode([
        'success' => true,
        'frequency_data' => $frequency_data,
        'retention_rate' => $retention_rate,
        'segment_data' => $segment_data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching customer insights: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 