<?php
require_once 'check_staff_session.php';
require_once 'database.php';

// Create menu images directory if it doesn't exist
$menu_image_dir = 'images/menu';
if (!file_exists($menu_image_dir)) {
    mkdir($menu_image_dir, 0777, true);
}

// Check and add image_path column if it doesn't exist
$check_column = "SHOW COLUMNS FROM MenuItems LIKE 'image_path'";
$column_exists = $conn->query($check_column);
if ($column_exists->num_rows === 0) {
    $add_column = "ALTER TABLE MenuItems ADD COLUMN image_path VARCHAR(255) AFTER price";
    if (!$conn->query($add_column)) {
        die("Error adding image_path column: " . $conn->error);
    }
}

// Initialize error and success messages
$error = '';
$success = '';

// Check for session messages
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'images/menu/' . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Insert into database
                $image_path = 'images/menu/' . $new_filename;
                $sql = "INSERT INTO MenuItems (name, description, price, category, image_path) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdss", $name, $description, $price, $category, $image_path);

                if ($stmt->execute()) {
                    $success = "Menu item added successfully!";
                } else {
                    $error = "Error adding menu item: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    } else {
        $error = "Please select an image file.";
    }
}

// Fetch existing menu items
$menu_items = [];
$sql = "SELECT menu_id, name, description, price, category, image_path, created_at FROM MenuItems ORDER BY category, name";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Items Management - Staff Panel</title>
    
    <!-- Core CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    
    <style>
        .menu-form {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .menu-table {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .menu-table th {
            background: rgba(0, 0, 0, 0.1);
            color: #000;
            font-weight: 500;
        }
        
        .menu-table td {
            color: #000;
            vertical-align: middle;
        }
        
        .menu-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #000;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 1);
            border-color: rgba(255, 255, 255, 0.3);
            color: #000;
        }
        
        .form-control::placeholder {
            color: rgba(0, 0, 0, 0.6);
        }
        
        .image-preview {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }
        
        .text-white {
            color: #000 !important;
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
                    <h2 class="text-white mb-4">Menu Items Management</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <!-- Add Menu Item Form -->
                    <div class="menu-form">
                        <h4 class="text-white mb-4">Add New Menu Item</h4>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name" class="text-white">Item Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="category" class="text-white">Category</label>
                                        <select class="form-control" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="Appetizers">Appetizers</option>
                                            <option value="Main Course">Main Course</option>
                                            <option value="Desserts">Desserts</option>
                                            <option value="Beverages">Beverages</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="description" class="text-white">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="price" class="text-white">Price</label>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="image" class="text-white">Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                        <img id="preview" class="image-preview">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Menu Item</button>
                        </form>
                    </div>
                    
                    <!-- Menu Items Table -->
                    <div class="menu-table">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menu_items as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="menu-image">
                                    </td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                    <td>Rs<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <a href="edit_menu_item.php?id=<?php echo htmlspecialchars($item['menu_id']); ?>" class="btn btn-sm btn-warning">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="delete_menu_item.php?id=<?php echo htmlspecialchars($item['menu_id']); ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this item?');">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
