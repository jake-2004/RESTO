<?php
require_once 'check_admin_session.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Password validation
    $uppercase = preg_match('@[A-Z]@', $new_password);
    $lowercase = preg_match('@[a-z]@', $new_password);
    $number    = preg_match('@[0-9]@', $new_password);
    $symbol    = preg_match('@[^\w]@', $new_password);
    
    if(!$uppercase || !$lowercase || !$number || !$symbol || strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters long and contain: uppercase letter, lowercase letter, number, and special character!";
        $messageType = "danger";
    } else if ($new_password !== $confirm_password) {
        $message = "New passwords do not match!";
        $messageType = "danger";
    } else {
        // Connect to database
        require_once 'config/database.php';
        
        // Get current user's data
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("
            SELECT u.password as user_password, l.password as login_password 
            FROM users u 
            JOIN login l ON u.user_id = l.user_id 
            WHERE u.user_id = ? AND u.role = 'admin'
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Add debug logging
        error_log("Verifying password for user_id: " . $user_id);
        error_log("Current password provided: " . $current_password);
        error_log("User password from users table: " . $user['user_password']);
        error_log("User password from login table: " . $user['login_password']);
        
        // Try to verify against both users and login table passwords
        $password_verified = false;
        
        // Check against users table password
        if (password_verify($current_password, $user['user_password']) || 
            $current_password === $user['user_password'] || 
            md5($current_password) === $user['user_password']) {
            $password_verified = true;
            error_log("Password verified against users table");
        }
        
        // Check against login table password if not yet verified
        if (!$password_verified && 
            (password_verify($current_password, $user['login_password']) || 
             $current_password === $user['login_password'] || 
             md5($current_password) === $user['login_password'])) {
            $password_verified = true;
            error_log("Password verified against login table");
        }
        
        if ($password_verified) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update users table
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                $update_stmt->execute();
                
                // Update login table
                $update_login_stmt = $conn->prepare("UPDATE login SET password = ? WHERE user_id = ?");
                $update_login_stmt->bind_param("si", $hashed_password, $user_id);
                $update_login_stmt->execute();
                
                // If both queries succeed, commit the transaction
                $conn->commit();
                $message = "Password successfully updated!";
                $messageType = "success";
            } catch (Exception $e) {
                // If any query fails, roll back the transaction
                $conn->rollback();
                $message = "Error updating password: " . $e->getMessage();
                $messageType = "danger";
                error_log("Error during password update: " . $e->getMessage());
            }
        } else {
            $message = "Current password is incorrect!";
            $messageType = "danger";
            error_log("Password verification failed - Current password: " . $current_password);
            error_log("Stored password hash in users table: " . $user['user_password']);
            error_log("Stored password hash in login table: " . $user['login_password']);
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password - Resto Admin</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .password-form-container {
            max-width: 500px;
            margin: 80px auto;
            padding: 35px;
            background: rgba(0, 0, 0, 0.75);
            border-radius: 15px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--admin-primary);
            color: white;
            box-shadow: 0 0 10px rgba(255, 99, 71, 0.2);
        }

        .btn-primary {
            background: var(--admin-primary);
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--admin-secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 99, 71, 0.3);
        }

        .alert {
            margin-bottom: 25px;
            border-radius: 8px;
            padding: 15px 20px;
        }

        label {
            color: white;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .password-requirements {
            color: #bdbdbd;
            font-size: 0.85em;
            margin-top: 8px;
            padding-left: 5px;
        }

        .hero_area {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
        }

        .bg-box {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .bg-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.3);
        }

        h2.text-center {
            margin-bottom: 30px;
            font-weight: 600;
            color: var(--admin-primary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Custom styling for success/danger alerts */
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border-color: rgba(40, 167, 69, 0.3);
            color: #98ff98;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
            color: #ffb3b3;
        }

        .header_section {
            background: #000000;
            backdrop-filter: blur(8px);
            position: relative;
            z-index: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-dropdown {
            background-color: rgba(0, 0, 0, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            min-width: 200px;
            padding: 8px;
        }

        .admin-header {
            color: white;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 8px;
        }

        .dropdown-item {
            color: white;
            padding: 8px 16px;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffbe33;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 38px;
            background: transparent;
            border: none;
            padding: 5px;
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.3s ease;
            z-index: 2;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 30px;
            width: 30px;
        }

        .password-toggle:hover {
            color: white;
        }

        .password-toggle:focus {
            outline: none;
            box-shadow: none;
        }

        .password-toggle i {
            font-size: 16px;
            line-height: 1;
        }

        input[type="password"],
        input[type="text"] {
            padding-right: 45px !important;
        }

        /* Enhanced Dropdown Styles */
        .user_option .dropdown-toggle::after {
            display: none;  /* Remove default dropdown arrow */
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
                            <li class="nav-item">
                                <a class="nav-link" href="adminlist.php">
                                    <i class="fa fa-users"></i> Users
                                </a>
                            </li>
                            <li class="nav-item active">
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
                                    <a class="dropdown-item" href="change_password.php">
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

    <div class="hero_area">
        <div class="bg-box">
            <img src="images/indexpic.jpeg" alt="Restaurant Background">
        </div>

        <div class="container">
            <div class="password-form-container">
                <h2 class="text-center text-white mb-4">Change Password</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="change_password.php">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="password-requirements">
                            Password must contain at least 8 characters, including uppercase, lowercase, number and special character
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const submitButton = document.querySelector('button[type="submit"]');
            
            // Create validation message containers
            const requirementsList = document.createElement('div');
            requirementsList.className = 'password-requirements mt-2';
            newPasswordInput.parentElement.appendChild(requirementsList);
            
            const matchMessage = document.createElement('div');
            matchMessage.className = 'password-requirements mt-2';
            confirmPasswordInput.parentElement.appendChild(matchMessage);
            
            function validatePassword() {
                const password = newPasswordInput.value;
                const requirements = [
                    { regex: /.{8,}/, text: '✓ At least 8 characters' },
                    { regex: /[A-Z]/, text: '✓ At least one uppercase letter' },
                    { regex: /[a-z]/, text: '✓ At least one lowercase letter' },
                    { regex: /[0-9]/, text: '✓ At least one number' },
                    { regex: /[^A-Za-z0-9]/, text: '✓ At least one special character' }
                ];
                
                let html = '';
                let allValid = true;
                
                requirements.forEach(req => {
                    const isValid = req.regex.test(password);
                    const color = isValid ? '#98ff98' : '#ffb3b3';
                    const symbol = isValid ? '✓' : '✗';
                    html += `<div style="color: ${color}">${symbol} ${req.text.substring(2)}</div>`;
                    if (!isValid) allValid = false;
                });
                
                requirementsList.innerHTML = html;
                return allValid;
            }
            
            function validateMatch() {
                const match = newPasswordInput.value === confirmPasswordInput.value;
                matchMessage.style.color = match ? '#98ff98' : '#ffb3b3';
                matchMessage.textContent = match ? '✓ Passwords match' : '✗ Passwords do not match';
                return match;
            }
            
            function updateSubmitButton() {
                const passwordValid = validatePassword();
                const passwordsMatch = validateMatch();
                submitButton.disabled = !(passwordValid && passwordsMatch);
                submitButton.style.opacity = submitButton.disabled ? '0.6' : '1';
            }
            
            // Add event listeners
            newPasswordInput.addEventListener('input', updateSubmitButton);
            confirmPasswordInput.addEventListener('input', updateSubmitButton);
            
            // Add password visibility toggles - SINGLE IMPLEMENTATION
            const fields = ['current_password', 'new_password', 'confirm_password'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                // Check if toggle button already exists
                if (!field.parentElement.querySelector('.password-toggle')) {
                    const toggleBtn = document.createElement('button');
                    toggleBtn.type = 'button';
                    toggleBtn.className = 'btn password-toggle';
                    toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
                    
                    field.parentElement.appendChild(toggleBtn);
                    
                    toggleBtn.addEventListener('click', () => {
                        const type = field.type === 'password' ? 'text' : 'password';
                        field.type = type;
                        toggleBtn.innerHTML = `<i class="fa fa-eye${type === 'password' ? '' : '-slash'}"></i>`;
                    });
                }
            });
        });
    </script>
</body>
</html>