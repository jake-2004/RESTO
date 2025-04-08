<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "resto_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed']);
    exit();
}

// Get customer count
$sql = "SELECT COUNT(*) as customer_count FROM users WHERE role = 'customer'";
$result = $conn->query($sql);
$count = $result->fetch_assoc()['customer_count'];

echo json_encode(['customer_count' => $count]);

$conn->close();
?>
