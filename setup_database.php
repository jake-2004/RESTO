<?php
require_once 'database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Start transaction
    $conn->begin_transaction();

    // Create MenuItems table
    $create_menu_items = "CREATE TABLE IF NOT EXISTS MenuItems (
        menu_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(50) NOT NULL,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_menu_items)) {
        throw new Exception("Error creating MenuItems table: " . $conn->error);
    }

    // Create Cart table with CASCADE delete
    $create_cart = "CREATE TABLE IF NOT EXISTS Cart (
        cart_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        menu_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (menu_id) REFERENCES MenuItems(menu_id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($create_cart)) {
        throw new Exception("Error creating Cart table: " . $conn->error);
    }

    // Create Preorders table
    $sql = "CREATE TABLE IF NOT EXISTS Preorders (
        preorder_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        menu_id INT NOT NULL,
        quantity INT NOT NULL,
        pickup_date DATE NOT NULL,
        pickup_time TIME NOT NULL,
        order_status ENUM('pending', 'confirmed', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
        total_amount DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (menu_id) REFERENCES MenuItems(menu_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating Preorders table: " . $conn->error);
    }

    // Create HomeDeliveryOrders table
    $sql = "CREATE TABLE IF NOT EXISTS HomeDeliveryOrders (
        delivery_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        menu_id INT NOT NULL,
        quantity INT NOT NULL,
        delivery_time DATETIME NOT NULL,
        address TEXT NOT NULL,
        order_status ENUM('pending', 'confirmed', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
        total_amount DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (menu_id) REFERENCES MenuItems(menu_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating HomeDeliveryOrders table: " . $conn->error);
    }

    // Create TableBookings table
    $sql = "CREATE TABLE IF NOT EXISTS TableBookings (
        booking_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        num_persons INT NOT NULL,
        booking_date DATE NOT NULL,
        booking_time TIME NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating TableBookings table: " . $conn->error);
    }

    // Commit the transaction
    $conn->commit();
    echo "Database setup completed successfully.";

} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    die("Error setting up database: " . $e->getMessage());
}
?>
