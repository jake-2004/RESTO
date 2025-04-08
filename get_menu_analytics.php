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
    // Get top selling items
    $top_items_query = "SELECT 
                            m.item_name,
                            m.category,
                            COUNT(*) as order_count,
                            SUM(od.quantity) as total_quantity,
                            SUM(od.quantity * od.price) as total_revenue
                        FROM menu_items m
                        JOIN order_details od ON m.id = od.item_id
                        JOIN (
                            SELECT id, order_date FROM home_delivery WHERE order_date BETWEEN ? AND ?
                            UNION ALL
                            SELECT id, order_date FROM preorder WHERE order_date BETWEEN ? AND ?
                        ) orders ON od.order_id = orders.id
                        GROUP BY m.id, m.item_name, m.category
                        ORDER BY total_quantity DESC
                        LIMIT 10";
    
    $stmt = $conn->prepare($top_items_query);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $top_items_result = $stmt->get_result();
    
    $top_items_data = [];
    while ($row = $top_items_result->fetch_assoc()) {
        $top_items_data[] = [
            'item_name' => $row['item_name'],
            'category' => $row['category'],
            'order_count' => (int)$row['order_count'],
            'total_quantity' => (int)$row['total_quantity'],
            'total_revenue' => (float)$row['total_revenue']
        ];
    }
    
    // Get category performance
    $category_query = "SELECT 
                            m.category,
                            COUNT(DISTINCT od.order_id) as order_count,
                            SUM(od.quantity) as total_quantity,
                            SUM(od.quantity * od.price) as total_revenue
                        FROM menu_items m
                        JOIN order_details od ON m.id = od.item_id
                        JOIN (
                            SELECT id, order_date FROM home_delivery WHERE order_date BETWEEN ? AND ?
                            UNION ALL
                            SELECT id, order_date FROM preorder WHERE order_date BETWEEN ? AND ?
                        ) orders ON od.order_id = orders.id
                        GROUP BY m.category
                        ORDER BY total_revenue DESC";
    
    $stmt = $conn->prepare($category_query);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $category_result = $stmt->get_result();
    
    $category_data = [];
    while ($row = $category_result->fetch_assoc()) {
        $category_data[] = [
            'category' => $row['category'],
            'order_count' => (int)$row['order_count'],
            'total_quantity' => (int)$row['total_quantity'],
            'total_revenue' => (float)$row['total_revenue']
        ];
    }
    
    // Get items with low sales
    $low_sales_query = "SELECT 
                            m.item_name,
                            m.category,
                            COUNT(*) as order_count,
                            SUM(od.quantity) as total_quantity,
                            SUM(od.quantity * od.price) as total_revenue
                        FROM menu_items m
                        LEFT JOIN order_details od ON m.id = od.item_id
                        LEFT JOIN (
                            SELECT id, order_date FROM home_delivery WHERE order_date BETWEEN ? AND ?
                            UNION ALL
                            SELECT id, order_date FROM preorder WHERE order_date BETWEEN ? AND ?
                        ) orders ON od.order_id = orders.id
                        GROUP BY m.id, m.item_name, m.category
                        HAVING order_count = 0 OR total_quantity < 10
                        ORDER BY total_quantity ASC
                        LIMIT 10";
    
    $stmt = $conn->prepare($low_sales_query);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $low_sales_result = $stmt->get_result();
    
    $low_sales_data = [];
    while ($row = $low_sales_result->fetch_assoc()) {
        $low_sales_data[] = [
            'item_name' => $row['item_name'],
            'category' => $row['category'],
            'order_count' => (int)$row['order_count'],
            'total_quantity' => (int)$row['total_quantity'],
            'total_revenue' => (float)$row['total_revenue']
        ];
    }
    
    // Return the data
    echo json_encode([
        'success' => true,
        'top_items' => $top_items_data,
        'category_performance' => $category_data,
        'low_sales_items' => $low_sales_data
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