<?php
require_once 'config/database.php';

// Check if the status column exists
$check_column = "SHOW COLUMNS FROM reviews LIKE 'status'";
$result = $conn->query($check_column);

if ($result->num_rows == 0) {
    // Add the status column if it doesn't exist
    $add_column = "ALTER TABLE reviews 
                   ADD COLUMN status ENUM('pending', 'published', 'rejected') DEFAULT 'pending' NOT NULL,
                   ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    
    if ($conn->query($add_column)) {
        echo "Status column added successfully to reviews table.";
    } else {
        echo "Error adding status column: " . $conn->error;
    }
} else {
    echo "Status column already exists in reviews table.";
}

$conn->close();
?> 