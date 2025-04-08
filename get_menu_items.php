<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/database.php';

// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Create table if it doesn't exist
if (!tableExists($conn, 'MenuItems')) {
    $sql = "CREATE TABLE MenuItems (
        menu_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(50) NOT NULL,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }
    
    // Insert some sample data
    $sample_data = "INSERT INTO MenuItems (name, description, price, category) VALUES 
        ('Margherita Pizza', 'Classic tomato and mozzarella pizza', 12.99, 'Pizza'),
        ('Caesar Salad', 'Fresh romaine lettuce with caesar dressing', 8.99, 'Salad'),
        ('Chocolate Cake', 'Rich chocolate layer cake', 6.99, 'Dessert')";
    
    if (!$conn->query($sample_data)) {
        die("Error inserting sample data: " . $conn->error);
    }
}

function getMenuItems() {
    global $conn;
    
    try {
        // Check connection
        if (!$conn->ping()) {
            throw new Exception("Database connection lost");
        }
        
        $sql = "SELECT * FROM MenuItems ORDER BY category, name";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $items = array();
        while ($row = $result->fetch_assoc()) {
            // Ensure image path is properly formatted
            if (!empty($row['image_path'])) {
                $row['image_path'] = str_replace('\\', '/', $row['image_path']);
            } else {
                $row['image_path'] = 'images/default-menu-item.jpg';
            }
            $items[] = $row;
        }
        
        $result->free();
        return $items;
        
    } catch (Exception $e) {
        error_log("Error in getMenuItems: " . $e->getMessage());
        return array();
    }
}

function getCategories() {
    global $conn;
    
    try {
        // Check connection
        if (!$conn->ping()) {
            throw new Exception("Database connection lost");
        }
        
        $sql = "SELECT DISTINCT category FROM MenuItems ORDER BY category";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $categories = array();
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        
        $result->free();
        return $categories;
        
    } catch (Exception $e) {
        error_log("Error in getCategories: " . $e->getMessage());
        return array();
    }
}

// Debug: Check table contents
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM MenuItems");
    $row = $result->fetch_assoc();
    error_log("Number of menu items in database: " . $row['count']);
    
    $result = $conn->query("SELECT * FROM MenuItems");
    while ($row = $result->fetch_assoc()) {
        error_log("Menu item found: " . $row['name'] . " (ID: " . $row['menu_id'] . ")");
    }
} catch (Exception $e) {
    error_log("Debug query error: " . $e->getMessage());
}

// Close connection when script ends
register_shutdown_function(function() {
    global $conn;
    $conn->close();
});
?>
