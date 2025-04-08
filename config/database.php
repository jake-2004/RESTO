<?php
// Database configuration
$servername = "127.0.0.1"; // Try IP instead of hostname
$username = "root";
$password = "";
$dbname = "resto_db";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}
?>
