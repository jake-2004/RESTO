<?php
require_once 'check_admin_session.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Set a random token in session to verify page state
if (!isset($_SESSION['page_token'])) {
    $_SESSION['page_token'] = bin2hex(random_bytes(32));
}
$pageToken = $_SESSION['page_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Resto Admin Panel - Change Password">
    
    <link rel="shortcut icon" href="images/favicon.png" type="">
    <title>Change Password - <?php echo htmlspecialchars($_SESSION['email']); ?></title>

    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    
    <style>
        /* Reuse existing admin styles */
        .hero_area {
            height: 100vh;
            display: flex;
            flex-direction: column;
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
            filter: brightness(0.4);
        }

        .header_section {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            position: relative;
            z-index: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-content {
            flex: 1;
            padding: 40px;
            color: white;
        }

        /* Password change form specific styles */
        .password-form-container {
            max-width: 500px;
            margin: 40px auto;
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .password-form-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: #ffffff;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px;
            border-radius: 8px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #2196F3;
            color: white;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
        }

        .btn-change-password {
            background: #2196F3;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-change-password:hover {
            background: #1976D2;
            transform: translateY(-2px);
        }

        .password-requirements {
            color: #bdbdbd;
            font-size: 0.9em;
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #bdbdbd;
        }

        /* Add header title styles */
        .page-title {
            text-align: center;
            padding: 20px 0;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Arial', sans-serif;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
        }

        .page-subtitle {
            text-align: center;
            color: #cccccc;
            font-size: 1.2rem;
            margin-bottom: 30px;
            font-family: 'Arial', sans-serif;
        }
    </style>
</head>
<body>
    <div class="hero_area">
        <div class="bg-box">
            <img src="images/indexpic.jpeg" alt="Restaurant Background">
        </div>

        <!-- Include your existing header here -->
        <?php include 'adminheader.php'; ?>

        <div class="admin-content">
            <h1 class="page-title">Admin Settings</h1>
            <p class="page-subtitle">Manage Your Account Security</p>
            
            <div class="password-form-container">
                <h2>Change Password</h2>
                
                <div id="alertMessage" class="alert" style="display: none;"></div>

                <form id="passwordChangeForm">
                    <input type="hidden" name="page_token" value="<?php echo $pageToken; ?>">
                    
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <div class="password-toggle">
                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                            <i class="fa fa-eye-slash toggle-password"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <div class="password-toggle">
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                            <i class="fa fa-eye-slash toggle-password"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <div class="password-toggle">
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            <i class="fa fa-eye-slash toggle-password"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-change-password">Update Password</button>

                    <div class="password-requirements">
                        <strong>Password Requirements:</strong>
                        <ul class="mt-2 mb-0">
                            <li>At least 8 characters long</li>
                            <li>Must contain at least one uppercase letter</li>
                            <li>Must contain at least one lowercase letter</li>
                            <li>Must contain at least one number</li>
                            <li>Must contain at least one special character</li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
    
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('.toggle-password').click(function() {
                const input = $(this).siblings('input');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                } else {
                    input.attr('type', 'password');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                }
            });

            // Handle form submission
            $('#passwordChangeForm').on('submit', function(e) {
                e.preventDefault();

                const currentPassword = $('#currentPassword').val();
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();

                // Password validation
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

                if (!passwordRegex.test(newPassword)) {
                    showAlert('Please ensure your new password meets all requirements.', 'danger');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    showAlert('New passwords do not match.', 'danger');
                    return;
                }

                // Send AJAX request
                $.ajax({
                    url: 'update_password.php',
                    method: 'POST',
                    data: {
                        current_password: currentPassword,
                        new_password: newPassword,
                        page_token: $('input[name="page_token"]').val()
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                showAlert('Password updated successfully!', 'success');
                                $('#passwordChangeForm')[0].reset();
                            } else {
                                showAlert(data.message || 'Error updating password.', 'danger');
                            }
                        } catch (error) {
                            showAlert('An error occurred. Please try again.', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Server error. Please try again later.', 'danger');
                    }
                });
            });

            function showAlert(message, type) {
                const alertDiv = $('#alertMessage');
                alertDiv.removeClass('alert-success alert-danger')
                       .addClass('alert-' + type)
                       .html(message)
                       .fadeIn();

                if (type === 'success') {
                    setTimeout(() => alertDiv.fadeOut(), 3000);
                }
            }
        });
    </script>
</body>
</html>