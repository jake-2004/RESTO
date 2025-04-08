<?php
require_once 'config/database.php';

try {
    // Check if database exists
    $check_db = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'resto_db'";
    $result = $conn->query($check_db);
    
    if ($result->num_rows == 0) {
        die("Database 'resto_db' does not exist. Please create it first.");
    }

    // Check if Reviews table exists and create it if it doesn't
    $create_reviews = "CREATE TABLE IF NOT EXISTS Reviews (
        review_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        review_text TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($create_reviews)) {
        echo "Reviews table verified/created successfully.\n";
    }

    // Check if there are any reviews
    $check_reviews = "SELECT COUNT(*) as count FROM Reviews";
    $result = $conn->query($check_reviews);
    $count = $result->fetch_assoc()['count'];
    echo "Number of reviews in database: " . $count . "\n";

    // Check if Users table exists and has records
    $check_users = "SELECT COUNT(*) as count FROM Users";
    $result = $conn->query($check_users);
    $users_count = $result->fetch_assoc()['count'];
    echo "Number of users in database: " . $users_count . "\n";

    // Display a sample review if any exist
    if ($count > 0) {
        $sample = "SELECT r.*, u.username FROM Reviews r JOIN Users u ON r.user_id = u.user_id LIMIT 1";
        $result = $conn->query($sample);
        $review = $result->fetch_assoc();
        echo "\nSample review:\n";
        echo "Username: " . $review['username'] . "\n";
        echo "Rating: " . $review['rating'] . "\n";
        echo "Text: " . $review['review_text'] . "\n";
        echo "Date: " . $review['created_at'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
