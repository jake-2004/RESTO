<?php
// Database connection test
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

// Test if login table exists
$table_check = $conn->query("SHOW TABLES LIKE 'login'");
if ($table_check->num_rows == 0) {
    die("Login table does not exist!");
}

// Display table structure
$structure = $conn->query("DESCRIBE login");
if ($structure) {
    echo "<h3>Login Table Structure:</h3>";
    while ($row = $structure->fetch_assoc()) {
        echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . " | Null: " . $row['Null'] . "<br>";
    }
} else {
    die("Could not get table structure: " . $conn->error);
}

// Check if any users exist
$users = $conn->query("SELECT COUNT(*) as count FROM login");
if ($users) {
    $count = $users->fetch_assoc()['count'];
    echo "<h3>Number of users in database: " . $count . "</h3>";
} else {
    die("Could not count users: " . $conn->error);
}

echo "<h3>Connection successful!</h3>";
?>
