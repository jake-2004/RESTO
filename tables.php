<?php
// Connect to the database
include 'db_connection.php'; // Use the correct relative path

// Fetch user details along with booked tables
$query = "SELECT BookedTables.*, Users.name, Users.email FROM BookedTables 
          JOIN Users ON BookedTables.user_id = Users.id"; // Adjust the table and column names as necessary
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Booked Tables</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <h1>User Details for Pre-Booked Tables</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Table ID</th>
                <th>Customer Name</th>
                <th>Booking Date</th>
                <th>Time</th>
                <th>Number of Guests</th>
                <th>User Name</th>
                <th>User Email</th>
                <!-- Add more columns as needed -->
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['table_id'] . "</td>";
                    echo "<td>" . $row['customer_name'] . "</td>";
                    echo "<td>" . $row['booking_date'] . "</td>";
                    echo "<td>" . $row['time'] . "</td>";
                    echo "<td>" . $row['number_of_guests'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>"; // User name
                    echo "<td>" . $row['email'] . "</td>"; // User email
                    // Add more columns as needed
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No pre-booked tables found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?> 