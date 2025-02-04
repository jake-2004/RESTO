<?php
session_start();
if (!isset($_SESSION['reset_email'])) {
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
    if (isset($_POST['otp'])) {
        $otp = mysqli_real_escape_string($conn, $_POST['otp']);
        $email = $_SESSION['reset_email'];
        
        $sql = "SELECT * FROM users WHERE email = '$email' AND reset_token = '$otp' AND token_expiry > NOW()";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $_SESSION['otp_verified'] = true;
            header("Location: update_password.php");
            exit();
        } else {
            $error = "Invalid or expired OTP.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Verify OTP - Resto</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    
    <style>
    .verify_otp_section {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/indexpic.jpeg');
        background-size: cover;
        background-position: center;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .verify_otp_container {
        background: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        width: 90%;
    }

    .verify_otp_heading {
        text-align: center;
        margin-bottom: 30px;
        color: #222831;
    }

    .otp-input {
        letter-spacing: 20px;
        text-align: center;
        font-size: 24px;
    }
    </style>
</head>

<body>
    <section class="verify_otp_section">
        <div class="verify_otp_container">
            <div class="verify_otp_heading">
                <h2>Verify OTP</h2>
                <p>Enter the OTP sent to your email</p>
            </div>

            <?php if(isset($error)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <input type="text" name="otp" class="form-control otp-input" 
                           maxlength="6" placeholder="Enter OTP" required 
                           pattern="[0-9]{6}" title="Please enter 6 digits">
                </div>
                <button type="submit" class="submit_btn">Verify OTP</button>
            </form>

            <div class="back_link">
                <a href="forgot_password.php">Resend OTP</a>
            </div>
        </div>
    </section>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
</body>
</html>
<?php $conn->close(); ?> 