<?php
require_once 'config/database.php';

try {
    // Check if type column exists
    $result = $conn->query("SHOW COLUMNS FROM Notifications LIKE 'type'");
    $typeExists = ($result->num_rows > 0);
    
    if (!$typeExists) {
        // Add the type column if it doesn't exist
        $sql = "ALTER TABLE Notifications ADD COLUMN type VARCHAR(50) DEFAULT 'general' AFTER message";
        
        if ($conn->query($sql)) {
            echo "Successfully added 'type' column to Notifications table.";
        } else {
            echo "Error adding column: " . $conn->error;
        }
    } else {
        echo "The 'type' column already exists in the Notifications table.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 