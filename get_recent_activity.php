<?php
session_start();
require_once 'check_admin_session.php';
header('Content-Type: application/json');

try {
    require_once 'config/database.php';
    
    // Get activities from the last 24 hours
    $query = "SELECT 
        'delivery' as type,
        'fa-truck' as icon,
        CONCAT('New delivery order #', h.delivery_id) as description,
        h.created_at as time
        FROM homedeliveryorders h
        WHERE h.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        
        UNION ALL
        
        SELECT 
        'preorder' as type,
        'fa-clock-o' as icon,
        CONCAT('New preorder #', p.preorder_id) as description,
        p.created_at as time
        FROM preorders p
        WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        
        UNION ALL
        
        SELECT 
        'reservation' as type,
        'fa-calendar' as icon,
        CONCAT('New table booking for ', name) as description,
        created_at as time
        FROM TableBookings
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        
        ORDER BY time DESC
        LIMIT 10";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $row['time'] = date('M d, Y h:i A', strtotime($row['time']));
        $activities[] = $row;
    }
    
    echo json_encode($activities);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch activities: ' . $e->getMessage(),
        'success' => false
    ]);
}
?> 