<?php
require_once 'config/database.php';
require_once 'check_staff_session.php';

// Check if the request is POST and has the required parameters
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id']) && isset($_POST['status'])) {
    $review_id = intval($_POST['review_id']);
    $status = $_POST['status'];
    
    // Validate status
    $valid_statuses = ['pending', 'published', 'rejected'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        exit;
    }
    
    // Update the review status
    $query = "UPDATE reviews SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $status, $review_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();
?> 