<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "resto_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user detailsjust count 
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update and password change
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        
        $update_sql = "UPDATE users SET user_name = ?, phone = ?, address = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $user_name, $phone, $address, $user_id);
        
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh user data
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Password validation
        $uppercase = preg_match('@[A-Z]@', $new_password);
        $lowercase = preg_match('@[a-z]@', $new_password);
        $number    = preg_match('@[0-9]@', $new_password);
        $symbol    = preg_match('@[^\w]@', $new_password);
        
        if(!$uppercase || !$lowercase || !$number || !$symbol || strlen($new_password) < 8) {
            $error = "Password must be at least 8 characters long and contain: uppercase letter, lowercase letter, number, and special character!";
        } else {
            // Verify current password
            $check_pwd_sql = "SELECT password FROM users WHERE user_id = ?";
            $check_stmt = $conn->prepare($check_pwd_sql);
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $pwd_result = $check_stmt->get_result();
            $pwd_row = $pwd_result->fetch_assoc();
            
            if (password_verify($current_password, $pwd_row['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Start transaction
                    $conn->begin_transaction();
                    
                    try {
                        // Update users table
                        $update_pwd_sql = "UPDATE users SET password = ? WHERE user_id = ?";
                        $update_pwd_stmt = $conn->prepare($update_pwd_sql);
                        $update_pwd_stmt->bind_param("si", $hashed_password, $user_id);
                        $update_pwd_stmt->execute();
                        
                        // Update login table
                        $update_login_sql = "UPDATE login SET password = ? WHERE email = (SELECT email FROM users WHERE user_id = ?)";
                        $update_login_stmt = $conn->prepare($update_login_sql);
                        $update_login_stmt->bind_param("si", $hashed_password, $user_id);
                        $update_login_stmt->execute();
                        
                        // If both queries succeed, commit the transaction
                        $conn->commit();
                        $success = "Password changed successfully!";
                    } catch (Exception $e) {
                        // If any query fails, roll back the transaction
                        $conn->rollback();
                        $error = "Error changing password: " . $e->getMessage();
                    }
                } else {
                    $error = "New passwords do not match!";
                }
            } else {
                $error = "Current password is incorrect!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>My Profile - Resto</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    
    <style>
        .profile_section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/indexpic.jpeg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            padding: 50px 0;
        }

        .profile_container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            margin: 0 auto;
        }

        .profile_heading {
            text-align: center;
            margin-bottom: 30px;
            color: #222831;
        }

        .profile_info {
            margin-bottom: 30px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .profile_info h3 {
            color: #ffbe33;
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 2px solid #ffbe33;
            padding-bottom: 10px;
        }

        .info_item {
            margin-bottom: 20px;
        }

        .info_label {
            font-weight: bold;
            color: #222831;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border: 2px solid #eee;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #ffbe33;
            box-shadow: 0 0 0 0.2rem rgba(255, 190, 51, 0.25);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .edit_btn {
            background-color: #ffbe33;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .edit_btn:hover {
            background-color: #e69c00;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 190, 51, 0.3);
        }

        .welcome-text {
            color: #666;
            font-size: 1.2rem;
            margin-top: 10px;
        }

        .form-text {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px 20px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .requirement {
            display: flex;
            align-items: center;
            margin: 8px 0;
            color: #666;
            transition: color 0.3s;
        }
        .requirement.valid {
            color: #28a745;
        }
        .requirement.invalid {
            color: #dc3545;
        }
        .requirement-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #ccc;
            margin-right: 10px;
            position: relative;
            transition: all 0.3s;
        }
        .requirement.valid .requirement-icon {
            border-color: #28a745;
            background: #28a745;
        }
        .requirement.invalid .requirement-icon {
            border-color: #dc3545;
            background: #dc3545;
        }
        .requirement.valid .requirement-icon::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
        }
        .requirement.invalid .requirement-icon::after {
            content: '✕';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <section class="profile_section">
        <div class="profile_container">
            <div class="profile_heading">
                <h2>My Profile</h2>
                <p class="welcome-text">Welcome, <?php echo htmlspecialchars($user['user_name']); ?>!</p>
            </div>

            <?php if(isset($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="profile_info">
                <h3>Personal Information</h3>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="info_item">
                        <label class="info_label">Fullname:</label>
                        <input type="text" name="user_name" class="form-control" value="<?php echo htmlspecialchars($user['user_name']); ?>" required>
                    </div>
                    <div class="info_item">
                        <label class="info_label">Email:</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    <div class="info_item">
                        <label class="info_label">Phone:</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    <div class="info_item">
                        <label class="info_label">Delivery Address:</label>
                        <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                        <small class="form-text text-muted">Please provide your complete delivery address including street, building number, and any additional instructions.</small>
                    </div>
                    <button type="submit" name="update_profile" class="edit_btn">Update Profile</button>
                </form>
            </div>

            <div class="profile_info">
                <h3>Change Password</h3>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="passwordForm">
                    <div class="info_item">
                        <label class="info_label">Current Password:</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="info_item">
                        <label class="info_label">New Password:</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required>
                        <small class="form-text text-muted">Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.</small>
                    </div>
                    <div class="info_item">
                        <label class="info_label">Confirm New Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        <div class="requirement" id="match" style="display: none;">
                            <div class="requirement-icon"></div>
                            <span>Passwords match</span>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="edit_btn" id="submitBtn" disabled>Change Password</button>
                </form>
            </div>

            <div class="text-center mt-4">
                <a href="user.php" class="btn btn-secondary">Back to Home</a>
                <a href="logout.php" class="btn btn-danger ml-2">Logout</a>
            </div>
        </div>
    </section>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            const submitBtn = document.getElementById('submitBtn');
            const matchRequirement = document.getElementById('match');

            confirmPassword.addEventListener('focus', function() {
                matchRequirement.style.display = 'flex';
            });

            function updateMatchRequirement(valid) {
                if (valid) {
                    matchRequirement.classList.add('valid');
                    matchRequirement.classList.remove('invalid');
                } else {
                    matchRequirement.classList.add('invalid');
                    matchRequirement.classList.remove('valid');
                }
            }

            function validatePassword() {
                const password = newPassword.value;
                const confirm = confirmPassword.value;

                // Internal password validation (not shown to user)
                const isValidPassword = 
                    password.length >= 8 &&
                    /[A-Z]/.test(password) &&
                    /[a-z]/.test(password) &&
                    /[0-9]/.test(password) &&
                    /[^A-Za-z0-9]/.test(password);

                // Show only password match validation
                if (confirm.length > 0) {
                    updateMatchRequirement(password === confirm);
                }

                // Enable submit only if all requirements are met
                submitBtn.disabled = !(isValidPassword && password === confirm);
            }

            newPassword.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);

            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                const password = newPassword.value;
                if (!(/[A-Z]/.test(password) && 
                    /[a-z]/.test(password) && 
                    /[0-9]/.test(password) && 
                    /[^A-Za-z0-9]/.test(password) && 
                    password.length >= 8)) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.');
                }
            });
        });
    </script>
</body>
</html>