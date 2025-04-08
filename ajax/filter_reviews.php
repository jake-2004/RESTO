<?php
require_once '../check_admin_session.php';
require_once '../config/database.php';

$rating = isset($_POST['rating']) ? $_POST['rating'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';
$search = isset($_POST['search']) ? $_POST['search'] : '';

$query = "SELECT r.*, c.name as customer_name 
          FROM reviews r 
          LEFT JOIN customers c ON r.customer_id = c.id 
          WHERE 1=1";

if ($rating) {
    $query .= " AND r.rating = " . intval($rating);
}

if ($status) {
    $query .= " AND r.status = '" . mysqli_real_escape_string($conn, $status) . "'";
}

if ($search) {
    $search = mysqli_real_escape_string($conn, $search);
    $query .= " AND (r.review_text LIKE '%$search%' OR c.name LIKE '%$search%')";
}

$query .= " ORDER BY r.created_at DESC";

$result = mysqli_query($conn, $query);

// Output filtered reviews HTML
while ($review = mysqli_fetch_assoc($result)) {
    // Similar HTML structure as in the main page
    include '../templates/review_card.php';
} 