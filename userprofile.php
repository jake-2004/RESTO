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

// Get user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    $update_sql = "UPDATE users SET fullname = ?, phone = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $fullname, $phone, $user_id);
    
    if ($update_stmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh user data
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = "Error updating profile: " . $conn->error;
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
        }

        .profile_info h3 {
            color: #ffbe33;
            margin-bottom: 20px;
        }

        .info_item {
            margin-bottom: 15px;
        }

        .info_label {
            font-weight: bold;
            color: #222831;
        }

        .edit_btn {
            background-color: #ffbe33;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .edit_btn:hover {
            background-color: #e69c00;
        }

        .welcome-text {
            color: #666;
            font-size: 1.2rem;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <section class="profile_section">
        <div class="profile_container">
            <div class="profile_heading">
                <h2>My Profile</h2>
                <p class="welcome-text">Welcome, <?php echo htmlspecialchars($user['fullname']); ?>!</p>
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
                        <label class="info_label">Full Name:</label>
                        <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user['fullname']); ?>">
                    </div>
                    <div class="info_item">
                        <label class="info_label">Email:</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    <div class="info_item">
                        <label class="info_label">Phone:</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    <button type="submit" class="edit_btn">Update Profile</button>
                </form>
            </div>

            <div class="text-center mt-4">
                <a href="index.html" class="btn btn-secondary">Back to Home</a>
                <a href="login.php" class="btn btn-danger ml-2">Logout</a>
            </div>
        </div>
    </section>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
</body>
</html>