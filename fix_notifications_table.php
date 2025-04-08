<?php
require_once 'config/database.php';

try {
    // First check if Notifications table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'Notifications'");
    
    if ($tableCheck->num_rows == 0) {
        // Create the Notifications table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS Notifications (
            notification_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
        )";
        
        if ($conn->query($createTable)) {
            echo "Notifications table created successfully.<br>";
        } else {
            throw new Exception("Error creating Notifications table: " . $conn->error);
        }
    } else {
        echo "Notifications table already exists.<br>";
    }
    
    // Now check if the table has the correct structure
    $result = $conn->query("DESCRIBE Notifications");
    $hasTypeColumn = false;
    
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'type') {
            $hasTypeColumn = true;
            break;
        }
    }
    
    if (!$hasTypeColumn) {
        // Add the type column if it doesn't exist
        $addTypeColumn = "ALTER TABLE Notifications ADD COLUMN type VARCHAR(50) DEFAULT 'general' AFTER message";
        
        if ($conn->query($addTypeColumn)) {
            echo "Added 'type' column to Notifications table.<br>";
        } else {
            throw new Exception("Error adding type column: " . $conn->error);
        }
    } else {
        echo "Type column already exists in Notifications table.<br>";
    }
    
    echo "Notifications table structure is now correct.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 