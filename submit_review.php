<?php
session_start();
require_once 'config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $rating = isset($_POST['rating']) ? $_POST['rating'] : 5; // Get rating from form
    $review_text = $_POST['review'];
    $current_time = date('Y-m-d H:i:s'); // Get current timestamp

    try {
        $stmt = $conn->prepare("INSERT INTO Reviews (user_id, rating, review_text, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $rating, $review_text, $current_time);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    // If not POST request, redirect to home
    header("Location: user.php");
    exit();
}
?>
