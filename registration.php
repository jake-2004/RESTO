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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password strength
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);
    
    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $error = "Password should be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character!";
    }
    // Validate passwords match
    else if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if email already exists
        $check_email = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($check_email);
        
        if ($result->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert user into the database
            $sql = "INSERT INTO users (user_name, email, phone, password) VALUES ('$user_name', '$email', '$phone', '$hashed_password')";
        
            if ($conn->query($sql) === TRUE) {
                // Get the user_id of the newly inserted user
                $user_id = $conn->insert_id;
        
                // Insert into the login table with the user_id
                $sql2 = "INSERT INTO login (user_id, email, password) VALUES ('$user_id', '$email', '$hashed_password')";
        
                if ($conn->query($sql2) === TRUE) {
                    header("Location: login.php?registration=success");
                    exit();
                } else {
                    $error = "Error: " . $sql2 . "<br>" . $conn->error;
                }
        
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        
}}
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Basic -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    
    <title>Register - Resto</title>

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
                <h2>Create Account</h2>
                <p>Join Resto today</p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form class="login_form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="registrationForm">
                <div class="form-group">
                    <input type="text" name="user_name" class="form-control" placeholder="Username" required>
                    <div class="error-message" id="user_nameError">Please enter a valid username (minimum 2 characters)</div>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                    <div class="error-message" id="emailError">Please enter a valid email address</div>
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
                    <div class="error-message" id="phoneError">Please enter a valid phone number</div>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="error-message" id="passwordError">Password must be at least 8 characters long and include uppercase, lowercase, number, and special character</div>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                    <div class="error-message" id="confirmPasswordError">Passwords do not match</div>
                </div>
                <button type="submit" class="login_btn" id="submitBtn">Register</button>
            </form>

            <div class="register_link">
                <p>Already have an account? <a href="login.php">Login</a></p>
                <a href="index.html">Back to Home</a>
            </div>
        </div>
    </section>

    <!-- jQery -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <!-- bootstrap js -->
    <script src="js/bootstrap.js"></script>

    <!-- Add this before the closing body tag -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registrationForm');
        const inputs = form.querySelectorAll('input');
        const submitBtn = document.getElementById('submitBtn');
        
        const validators = {
            user_name: (value) => value.length >= 2,
            email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            phone: (value) => /^[6-9]\d{9}$/.test(value.replace(/[^0-9]/g, '')),
            password: (value) => {
                const hasUpperCase = /[A-Z]/.test(value);
                const hasLowerCase = /[a-z]/.test(value);
                const hasNumbers = /\d/.test(value);
                const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(value);
                return value.length >= 8 && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar;
            },
            confirm_password: (value) => value === form.querySelector('input[name="password"]').value
        };

        submitBtn.disabled = false;

        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const errorElement = document.getElementById(`${input.name}Error`);
                const isInputValid = validators[input.name](input.value);

                if (isInputValid) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                    errorElement.style.display = 'none';
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                    errorElement.style.display = 'block';
                }
            });
        });

        form.addEventListener('submit', function(e) {
            let isValid = Array.from(inputs).every(input => validators[input.name](input.value));
            
            if (!isValid) {
                e.preventDefault();
                inputs.forEach(input => {
                    const errorElement = document.getElementById(`${input.name}Error`);
                    const isInputValid = validators[input.name](input.value);
                    
                    if (!isInputValid) {
                        input.classList.add('is-invalid');
                        errorElement.style.display = 'block';
                    }
                });
                return false;
            }
        });
    });
    </script>
</body>
</html>