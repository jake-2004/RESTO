<?php
require_once 'check_staff_session.php';
require_once 'database.php';

$error = '';
$success = '';
$menu_item = null;

if (!isset($_GET['id'])) {
    header('Location: menu-items.php');
    exit;
}

$menu_id = intval($_GET['id']);

// Fetch menu item details
$sql = "SELECT * FROM MenuItems WHERE menu_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $menu_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: menu-items.php');
    exit;
}

$menu_item = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    $update_sql = "UPDATE MenuItems SET name = ?, description = ?, price = ?, category = ?";
    $params = array($name, $description, $price, $category);
    $types = "ssds";
    
    // Handle image upload if new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'images/menu/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'images/menu/' . $new_filename;
                $update_sql .= ", image_path = ?";
                $params[] = $image_path;
                $types .= "s";
                
                // Delete old image if it exists
                if (!empty($menu_item['image_path'])) {
                    $old_image_path = $menu_item['image_path'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }
    
    $update_sql .= " WHERE menu_id = ?";
    $params[] = $menu_id;
    $types .= "i";
    
    if (empty($error)) {
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $success = "Menu item updated successfully!";
            // Refresh menu item data
            $result = $conn->query("SELECT * FROM MenuItems WHERE menu_id = $menu_id");
            $menu_item = $result->fetch_assoc();
            
            // Redirect to menu-items.php after successful update
            header('Location: menu-items.php');
            exit;
        } else {
            $error = "Error updating menu item: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item - Staff Panel</title>
    
    <!-- Core CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    
    <style>
        .menu-form {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            color: #fff;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .current-image {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 8px;
        }
        
        .image-preview {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="hero_area">
        <div class="bg-box">
            <img src="images/indexpic.jpeg" alt="Restaurant Background">
        </div>
        
        <?php include 'staff-header.php'; ?>
        
        <div class="container mt-5">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-white mb-4">Edit Menu Item</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div class="menu-form">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name" class="text-white">Item Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($menu_item['name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="category" class="text-white">Category</label>
                                        <select class="form-control" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="Appetizers" <?php echo $menu_item['category'] == 'Appetizers' ? 'selected' : ''; ?>>Appetizers</option>
                                            <option value="Main Course" <?php echo $menu_item['category'] == 'Main Course' ? 'selected' : ''; ?>>Main Course</option>
                                            <option value="Desserts" <?php echo $menu_item['category'] == 'Desserts' ? 'selected' : ''; ?>>Desserts</option>
                                            <option value="Beverages" <?php echo $menu_item['category'] == 'Beverages' ? 'selected' : ''; ?>>Beverages</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="description" class="text-white">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($menu_item['description']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="price" class="text-white">Price</label>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" 
                                               value="<?php echo htmlspecialchars($menu_item['price']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="image" class="text-white">Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <small class="text-white-50">Leave empty to keep current image</small>
                                        <?php if (!empty($menu_item['image_path'])): ?>
                                            <div class="mt-2">
                                                <label class="text-white">Current Image:</label>
                                                <img src="<?php echo htmlspecialchars($menu_item['image_path']); ?>" 
                                                     alt="Current Image" class="current-image">
                                            </div>
                                        <?php endif; ?>
                                        <img id="preview" class="image-preview">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Update Menu Item</button>
                                <a href="menu-items.php" class="btn btn-secondary ml-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/custom.js"></script>
    
    <script>
        // Image preview
        document.getElementById('image').onchange = function(e) {
            const preview = document.getElementById('preview');
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            if (file) {
                reader.readAsDataURL(file);
            }
        };
    </script>
</body>
</html>
