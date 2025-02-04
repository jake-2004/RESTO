<?php
session_start();
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Check if email exists
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            // Generate OTP
            $otp = sprintf("%06d", mt_rand(0, 999999));
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Store OTP in database
            $updateSql = "UPDATE users SET reset_token = '$otp', token_expiry = '$expiry' WHERE email = '$email'";
            if ($conn->query($updateSql)) {
                // Send OTP email using PHP mailer configuration
                $to = $email;
                $subject = "Password Reset OTP";
                $message = "Hello,\n\nYour OTP for password reset is: $otp\n\nThis OTP will expire in 15 minutes.\n\nIf you didn't request this, please ignore this email.";
                $headers = "From: your-configured-email@domain.com\r\n"; // Update this with your configured email
                $headers .= "Reply-To: your-configured-email@domain.com\r\n"; // Add reply-to header
                $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n"; // Add mailer header
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                try {
                    // Remove @ symbol to see errors during development
                    if(mail($to, $subject, $message, $headers)) {
                        $_SESSION['reset_email'] = $email;
                        header("Location: verify_otp.php");
                        exit();
                    } else {
                        // More detailed error logging
                        $mail_error = error_get_last()['message'];
                        error_log("Failed to send email to: " . $email . ". Error: " . $mail_error);
                        $error = "Error sending email. Please try again later.";
                        
                        // Rollback the OTP update since email failed
                        $rollbackSql = "UPDATE users SET reset_token = NULL, token_expiry = NULL WHERE email = '$email'";
                        $conn->query($rollbackSql);
                    }
                } catch (Exception $e) {
                    error_log("Mail exception: " . $e->getMessage());
                    $error = "System error while sending email. Please try again later.";
                    
                    // Rollback the OTP update
                    $rollbackSql = "UPDATE users SET reset_token = NULL, token_expiry = NULL WHERE email = '$email'";
                    $conn->query($rollbackSql);
                }
            } else {
                $error = "Error processing request. Please try again.";
            }
        } else {
            $error = "Email address not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Forgot Password - Resto</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    
    <style>
    .forgot_password_section {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/indexpic.jpeg');
        background-size: cover;
        background-position: center;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .forgot_password_container {
        background: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        width: 90%;
    }

    .forgot_password_heading {
        text-align: center;
        margin-bottom: 30px;
        color: #222831;
    }

    .form-control {
        height: 45px;
        margin-bottom: 20px;
    }

    .submit_btn {
        background-color: #ffbe33;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        width: 100%;
        font-weight: 600;
        text-transform: uppercase;
        transition: all 0.3s ease;
    }

    .submit_btn:hover {
        background-color: #e69c00;
    }

    .back_link {
        text-align: center;
        margin-top: 20px;
    }

    .back_link a {
        color: #ffbe33;
        text-decoration: none;
    }

    .back_link a:hover {
        color: #e69c00;
    }
    </style>
</head>

<body>
    <section class="forgot_password_section">
        <div class="forgot_password_container">
            <div class="forgot_password_heading">
                <h2>Forgot Password</h2>
                <p>Enter your email to receive OTP</p>
            </div>

            <?php if(isset($error)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <button type="submit" class="submit_btn">Send OTP</button>
            </form>

            <div class="back_link">
                <a href="help.php">Back to Login</a>
            </div>
        </div>
    </section>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
</body>
</html>
<?php $conn->close(); ?> 