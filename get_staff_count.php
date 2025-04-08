<?php
require_once 'check_admin_session.php';
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    // Query to count staff members (users with role = 'staff')
    $query = "SELECT COUNT(*) as count FROM users WHERE role = 'staff'";
    $result = $conn->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo json_encode(['count' => (int)$row['count']]);
    } else {
        throw new Exception("Failed to fetch staff count");
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?> 