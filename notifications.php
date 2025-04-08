<?php
require_once 'config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS Notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating Notifications table: " . $conn->error);
}

echo "Notifications table created successfully";
?> 