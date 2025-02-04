<?php
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

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            session_start();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            header("Location: user.html");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Basic -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    
    <title>Login - Resto</title>

    <!-- bootstrap core css -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <!-- font awesome style -->
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <!-- Custom styles -->
    <link href="css/style.css" rel="stylesheet" />
    
    <style>
    .login_section {
      background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/indexpic.jpeg');
      background-size: cover;
      background-position: center;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login_container {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
      max-width: 400px;
      width: 90%;
    }

    .login_heading {
      text-align: center;
      margin-bottom: 30px;
      color: #222831;
    }

    .login_form input {
      margin-bottom: 20px;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
    }

    .login_btn {
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

    .login_btn:hover {
      background-color: #e69c00;
    }

    .social_login {
      margin-top: 20px;
      text-align: center;
    }

    .social_login a {
      display: inline-block;
      width: 40px;
      height: 40px;
      line-height: 40px;
      border-radius: 50%;
      background: #222831;
      color: white;
      margin: 0 5px;
      transition: all 0.3s ease;
    }

    .social_login a:hover {
      background: #ffbe33;
      transform: translateY(-3px);
    }

    .register_link {
      text-align: center;
      margin-top: 20px;
      color: #666;
    }

    .register_link a {
      color: #ffbe33;
      text-decoration: none;
      font-weight: 600;
    }

    .register_link a:hover {
      color: #e69c00;
    }

    .forgot-password-link {
        display: inline-block;
        margin-bottom: 10px;
        font-size: 0.9em;
    }

    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: -15px;
        margin-bottom: 15px;
        display: none;
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .form-control.is-valid {
        border-color: #28a745;
    }
    </style>
</head>

<body>
    <section class="login_section">
        <div class="login_container">
            <div class="login_heading">
                <h2>Welcome to Resto</h2>
                <p>Sign in to continue</p>
            </div>
            
            <?php if(isset($error)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php } ?>
            
            <form class="login_form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm" novalidate>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required id="email">
                    <div class="error-message" id="emailError"></div>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required id="password">
                    <div class="error-message" id="passwordError"></div>
                </div>
                <button type="submit" class="login_btn" id="submitBtn">Login</button>
            </form>

            <div class="register_link">
                <p>Don't have an account? <a href="registration.php">Register Now</a></p>
                <p><a href="forgot_password.php" class="forgot-password-link">Forgot Password?</a></p>
                <a href="index.html">Back to Home</a>
            </div>
        </div>
    </section>

    <!-- jQery -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <!-- bootstrap js -->
    <script src="js/bootstrap.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');

            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            function validatePassword(password) {
                return password.length >= 6;
            }

            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Reset previous error states
                email.classList.remove('is-invalid', 'is-valid');
                password.classList.remove('is-invalid', 'is-valid');
                emailError.style.display = 'none';
                passwordError.style.display = 'none';

                // Validate email
                if (!validateEmail(email.value)) {
                    email.classList.add('is-invalid');
                    emailError.style.display = 'block';
                    emailError.textContent = 'Please enter a valid email address';
                    isValid = false;
                }

                // Validate password
                if (!validatePassword(password.value)) {
                    password.classList.add('is-invalid');
                    passwordError.style.display = 'block';
                    passwordError.textContent = 'Password must be at least 6 characters long';
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>