<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

function checkDatabaseConnection() {
    global $conn;
    if ($conn === false) {
        return "Database connection failed: " . mysqli_connect_error();
    }
    return "Database connection successful";
}

function checkReviewsTable() {
    global $conn;
    $sql = "SHOW TABLES LIKE 'Reviews'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return "Reviews table exists";
    }
    return "Reviews table does not exist";
}

function getTableStructure() {
    global $conn;
    $sql = "DESCRIBE Reviews";
    $result = $conn->query($sql);
    if ($result === false) {
        return "Could not get table structure: " . $conn->error;
    }
    $structure = [];
    while ($row = $result->fetch_assoc()) {
        $structure[] = $row;
    }
    return $structure;
}

function getReviewCount() {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM Reviews";
    $result = $conn->query($sql);
    if ($result === false) {
        return "Could not count reviews: " . $conn->error;
    }
    $row = $result->fetch_assoc();
    return $row['count'];
}

function getSampleReviews() {
    global $conn;
    $sql = "SELECT r.*, u.username 
            FROM Reviews r 
            JOIN Users u ON r.user_id = u.user_id 
            ORDER BY r.created_at DESC 
            LIMIT 3";
    $result = $conn->query($sql);
    if ($result === false) {
        return "Could not fetch reviews: " . $conn->error;
    }
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    return $reviews;
}

// Run diagnostics
echo "<pre>\n";
echo "=== Database Connection ===\n";
echo checkDatabaseConnection() . "\n\n";

echo "=== Reviews Table Status ===\n";
echo checkReviewsTable() . "\n\n";

echo "=== Table Structure ===\n";
$structure = getTableStructure();
if (is_array($structure)) {
    print_r($structure);
} else {
    echo $structure . "\n";
}

echo "\n=== Review Count ===\n";
echo "Total reviews: " . getReviewCount() . "\n\n";

echo "=== Sample Reviews ===\n";
$reviews = getSampleReviews();
if (is_array($reviews)) {
    foreach ($reviews as $review) {
        echo "Username: " . htmlspecialchars($review['username']) . "\n";
        echo "Rating: " . $review['rating'] . "\n";
        echo "Text: " . htmlspecialchars($review['review_text']) . "\n";
        echo "Date: " . $review['created_at'] . "\n";
        echo "-------------------\n";
    }
} else {
    echo $reviews . "\n";
}
echo "</pre>";
?>
