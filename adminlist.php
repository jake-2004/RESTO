<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "resto_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle role update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];
    
    // Don't allow changing own role if admin
    if ($user_id == $_SESSION['user_id'] && $_SESSION['role'] == 'admin' && $new_role != 'admin') {
        $error_message = "Cannot change your own admin role!";
    } else {
        $update_sql = "UPDATE users SET role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $update_sql1 = "UPDATE login SET role = ? WHERE user_id = ?";
        $stmt1 = $conn->prepare($update_sql1);
        
        $stmt->bind_param("si", $new_role, $user_id);
        $stmt1->bind_param("si", $new_role, $user_id);
        
        if ($stmt->execute() && $stmt1->execute()) {
            $success_message = "Role updated successfully!";
        } else {
            $error_message = "Error updating role!";
        }
        $stmt->close();
        $stmt1->close();
    }
}

// Handle new staff registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_staff'])) {
    $staff_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $staff_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
    $staff_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
    $staff_password = $_POST['staff_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password
    $uppercase = preg_match('@[A-Z]@', $staff_password);
    $lowercase = preg_match('@[a-z]@', $staff_password);
    $number    = preg_match('@[0-9]@', $staff_password);
    $specialChars = preg_match('@[^\w]@', $staff_password);

    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($staff_password) < 8) {
        $error_message = "Password should be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character!";
    } else if ($staff_password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($staff_password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check_email = "SELECT * FROM users WHERE email = '$staff_email'";
        $result = $conn->query($check_email);

        if ($result->num_rows > 0) {
            $error_message = "Email already exists!";
        } else {
            // Insert into users table
            $insert_user_sql = "INSERT INTO users (user_name, email, phone, password, role) VALUES (?, ?, ?, ?, 'staff')";
            $stmt = $conn->prepare($insert_user_sql);
            $stmt->bind_param("ssss", $staff_name, $staff_email, $staff_phone, $hashed_password);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;

                // Insert into login table
                $insert_login_sql = "INSERT INTO login (user_id, email, password, role) VALUES (?, ?, ?, 'staff')";
                $stmt_login = $conn->prepare($insert_login_sql);
                $stmt_login->bind_param("iss", $user_id, $staff_email, $hashed_password);

                if ($stmt_login->execute()) {
                    $success_message = "Staff registered successfully!";
                } else {
                    $error_message = "Error registering staff in login table!";
                }
                $stmt_login->close();
            } else {
                $error_message = "Error registering staff in users table!";
            }
            $stmt->close();
        }
    }
}

// Fetch all users
$sql = "SELECT user_id, email, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

// Count total customers
$customer_count_sql = "SELECT COUNT(*) as customer_count FROM users WHERE role = 'customer'";
$customer_count_result = $conn->query($customer_count_sql);
$customer_count = $customer_count_result->fetch_assoc()['customer_count'];

// Store customer count in session for use in admin.html
$_SESSION['customer_count'] = $customer_count;

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Admin Dashboard - Resto</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    
    <style>
    .admin_section {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/indexpic.jpeg');
        background-size: cover;
        background-position: center;
        min-height: 100vh;
        padding: 50px 0;
        padding-top: 120px; /* Add padding to account for fixed header */
    }

    .header_section {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 999;
        background: rgba(0, 0, 0, 0.8);
        padding: 10px 0;
    }

    .custom_nav-container {
        padding: 0;
    }

    .navbar-brand {
        color: #ffbe33;
        font-weight: bold;
        font-size: 24px;
        margin-right: 30px;
    }

    .navbar-brand:hover {
        color: #ffbe33;
    }

    .nav-link {
        color: white !important;
        padding: 8px 15px !important;
        margin: 0 5px;
    }

    .nav-link:hover {
        color: #ffbe33 !important;
    }

    .nav-item.active .nav-link {
        color: #ffbe33 !important;
    }

    .admin_container {
        background: rgba(255, 255, 255, 0.95);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        margin: 0 auto;
        max-width: 1200px;
    }

    .admin_heading {
        text-align: center;
        margin-bottom: 30px;
        color: #222831;
    }

    .users_table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 20px;
    }

    .table th {
        background-color: #222831;
        color: white;
    }

    .role_select {
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .update_btn, .delete_btn {
        padding: 5px 15px;
        border-radius: 4px;
        cursor: pointer;
        margin: 0 5px;
    }

    .update_btn {
        background-color: #ffbe33;
        color: white;
        border: none;
    }

    .update_btn:hover {
        background-color: #e69c00;
    }

    .logout_btn {
        position: absolute;
        top: 20px;
        right: 20px;
        background-color: #dc3545;
        color: white;
        padding: 8px 20px;
        border-radius: 5px;
        text-decoration: none;
    }

    .logout_btn:hover {
        background-color: #c82333;
        color: white;
    }

    .alert {
        margin-bottom: 20px;
    }

    /* Enhanced Dropdown Styles */
    .user_option .dropdown-toggle::after {
        display: none;
    }

    .user_option .user_link {
        color: #fff;
        font-size: 1.2em;
        padding: 8px;
        border-radius: 50%;
        background: rgba(33, 150, 243, 0.1);
        transition: all 0.3s ease;
        display: inline-block;
    }

    .user_option .user_link:hover {
        background: rgba(33, 150, 243, 0.2);
        transform: translateY(-2px);
    }

    .admin-dropdown {
        background: rgba(25, 28, 36, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        margin-top: 10px;
        min-width: 220px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        padding: 0;
        overflow: hidden;
    }

    .admin-header {
        padding: 16px;
        background: rgba(33, 150, 243, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .admin-header i {
        color: var(--admin-primary);
        font-size: 1.2em;
    }

    .admin-header span {
        color: #fff;
        font-size: 0.9em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-dropdown .dropdown-item {
        color: #fff;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s ease;
    }

    .admin-dropdown .dropdown-item i {
        width: 20px;
        text-align: center;
        font-size: 1.1em;
    }

    .admin-dropdown .dropdown-item:hover {
        background: rgba(33, 150, 243, 0.1);
        color: var(--admin-primary);
    }

    .admin-dropdown .dropdown-divider {
        border-color: rgba(255, 255, 255, 0.1);
        margin: 0;
    }

    .admin-dropdown .text-danger:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    </style>
</head>

<body>
    <section class="admin_section">
        <header class="header_section">
            <div class="container">
                <nav class="navbar navbar-expand-lg custom_nav-container">
                    <a class="navbar-brand" href="admin.php">
                        <span>Resto Admin</span>
                    </a>

                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
                        <span class=""></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav mx-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php">
                                    <i class="fa fa-tachometer"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link" href="adminlist.php">
                                    <i class="fa fa-users"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="change_password.php">
                                    <i class="fa fa-cog"></i> Settings
                                </a>
                            </li>
                        </ul>
                        
                        <div class="user_option">
                            <div class="dropdown">
                                <a href="#" class="user_link dropdown-toggle" id="adminDropdown" 
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-user-secret"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right admin-dropdown">
                                    <div class="admin-header">
                                        <i class="fa fa-user-secret"></i>
                                        <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                                    </div>
                                    <a class="dropdown-item" href="admin/settings.php">
                                        <i class="fa fa-cog"></i> Settings
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <i class="fa fa-sign-out"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
        
        <div class="admin_container">
            <div class="admin_heading">
                <h2>Admin Dashboard</h2>
                <p>Manage Users</p>
            </div>

            <?php if(isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="users_table">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></td>
                            <td>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                    <?php if($row['role'] == 'admin'): ?>
                                        <span>Admin</span>
                                    <?php else: ?>
                                        <select name="new_role" class="role_select">
                                            <option value="customer" <?php echo $row['role'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                                            <option value="staff" <?php echo $row['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                                        </select>
                                        <button type="submit" name="update_role" class="update_btn">Update</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Staff Registration Form -->
            <div class="admin_heading">
                <h2>Register New Staff</h2>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="staff_name">Name:</label>
                    <input type="text" class="form-control" id="staff_name" name="staff_name" required>
                </div>
                <div class="form-group">
                    <label for="staff_email">Email:</label>
                    <input type="email" class="form-control" id="staff_email" name="staff_email" required oninput="validateEmail()">
                    <small id="emailHelp" class="form-text text-muted"></small>
                </div>
                <div class="form-group">
                    <label for="staff_phone">Phone:</label>
                    <input type="tel" class="form-control" id="staff_phone" name="staff_phone" required>
                </div>
                <div class="form-group">
                    <label for="staff_password">Password:</label>
                    <input type="password" class="form-control" id="staff_password" name="staff_password" required oninput="validatePassword()">
                    <small id="passwordHelp" class="form-text text-muted"></small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="register_staff" class="btn btn-primary">Register Staff</button>
            </form>
        </div>
    </section>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/custom.js"></script>

    <script>
    function validateEmail() {
        const email = document.getElementById('staff_email').value;
        const emailHelp = document.getElementById('emailHelp');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailPattern.test(email)) {
            emailHelp.textContent = 'Valid email address.';
            emailHelp.style.color = 'green';
        } else {
            emailHelp.textContent = 'Invalid email address.';
            emailHelp.style.color = 'red';
        }
    }

    function validatePassword() {
        const password = document.getElementById('staff_password').value;
        const passwordHelp = document.getElementById('passwordHelp');
        const uppercase = /[A-Z]/.test(password);
        const lowercase = /[a-z]/.test(password);
        const number = /[0-9]/.test(password);
        const specialChars = /[^\w]/.test(password);
        if (uppercase && lowercase && number && specialChars && password.length >= 8) {
            passwordHelp.textContent = 'Strong password.';
            passwordHelp.style.color = 'green';
        } else {
            passwordHelp.textContent = 'Password should be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.';
            passwordHelp.style.color = 'red';
        }
    }
    </script>
</body>
</html>

<?php
// Close the database connection only once at the end
if (isset($conn)) {
    $conn->close();
}
?>