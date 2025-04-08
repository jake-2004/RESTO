<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'check_staff_session.php';
require_once 'database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid menu item ID.";
    header('Location: menu-items.php');
    exit;
}

$menu_id = intval($_GET['id']);

try {
    // Start transaction
    $conn->begin_transaction();

    // First, modify the Cart table to add ON DELETE CASCADE
    $drop_fk = "ALTER TABLE Cart DROP FOREIGN KEY cart_ibfk_2";
    $conn->query($drop_fk);

    $add_fk = "ALTER TABLE Cart
               ADD CONSTRAINT cart_ibfk_2 
               FOREIGN KEY (menu_id) 
               REFERENCES MenuItems(menu_id) 
               ON DELETE CASCADE";
    $conn->query($add_fk);

    // Get the image path
    $sql = "SELECT image_path FROM MenuItems WHERE menu_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $menu_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $menu_item = $result->fetch_assoc();
        
        // Delete the image file if it exists
        if (!empty($menu_item['image_path'])) {
            $image_path = '../' . $menu_item['image_path'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Delete the menu item (Cart items will be deleted automatically due to CASCADE)
        $delete_sql = "DELETE FROM MenuItems WHERE menu_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        
        if (!$delete_stmt) {
            throw new Exception("Prepare delete failed: " . $conn->error);
        }
        
        $delete_stmt->bind_param("i", $menu_id);
        
        if (!$delete_stmt->execute()) {
            throw new Exception("Delete failed: " . $delete_stmt->error);
        }
        
        if ($delete_stmt->affected_rows > 0) {
            $conn->commit();
            $_SESSION['success_message'] = "Menu item deleted successfully.";
        } else {
            throw new Exception("No menu item was deleted.");
        }
        
        $delete_stmt->close();
    } else {
        throw new Exception("Menu item not found.");
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    error_log("Error in delete_menu_item.php: " . $e->getMessage());
    $_SESSION['error_message'] = "Error deleting menu item: " . $e->getMessage();
}

// Redirect back to menu items page
header('Location: menu-items.php');
exit;
?>
