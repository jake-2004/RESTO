<?php
session_start();
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified'])) {
    header("Location: forgot_password.php");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $email = $_SESSION['reset_email'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $updateSql = "UPDATE users SET password = '$hashed_password', reset_token = NULL, token_expiry = NULL 
                      WHERE email = '$email'";
        
        if ($conn->query($updateSql)) {
            // Clear all session variables
            session_unset();
            session_destroy();
            $success = "Password updated successfully. You can now login with your new password.";
        } else {
            $error = "Error updating password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Update Password - Resto</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    
    <style>
    .update_password_section {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/indexpic.jpeg');
        background-size: cover;
        background-position: center;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .update_password_container {
        background: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        width: 90%;
    }

    .update_password_heading {
        text-align: center;
        margin-bottom: 30px;
        color: #222831;
    }
    </style>
</head>

<body>
    <section class="update_password_section">
        <div class="update_password_container">
            <div class="update_password_heading">
                <h2>Update Password</h2>
                <p>Enter your new password</p>
            </div>

            <?php if(isset($error)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <?php if(isset($success)) { ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                    <p><a href="help.php">Click here to login</a></p>
                </div>
            <?php } else { ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" 
                               placeholder="New Password" required minlength="8">
                    </div>
                    <div class="form-group">
                        <input type="password" name="confirm_password" class="form-control" 
                               placeholder="Confirm Password" required minlength="8">
                    </div>
                    <button type="submit" class="submit_btn">Update Password</button>
                </form>
            <?php } ?>
        </div>
    </section>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
</body>
</html>
<?php $conn->close(); ?> 