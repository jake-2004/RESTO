<?php
require_once 'config/database.php';

try {
    // Query to select all records from TableBookings
    $query = "SELECT 
        booking_id,
        user_id,
        name,
        phone,
        email,
        num_persons,
        booking_date,
        booking_time,
        status,
        approval_status,
        created_at
        FROM TableBookings 
        ORDER BY created_at DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }
    
    echo "<style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-pending { color: orange; }
        .status-confirmed { color: green; }
        .status-cancelled { color: red; }
    </style>";
    
    echo "<h2>Table Bookings Records</h2>";
    
    if ($result->num_rows > 0) {
        echo "<table>
            <tr>
                <th>Booking ID</th>
                <th>User ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Guests</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Approval Status</th>
                <th>Created At</th>
            </tr>";
        
        while($row = $result->fetch_assoc()) {
            $statusClass = 'status-' . strtolower($row['status']);
            echo "<tr>
                <td>" . htmlspecialchars($row['booking_id']) . "</td>
                <td>" . htmlspecialchars($row['user_id']) . "</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['phone']) . "</td>
                <td>" . htmlspecialchars($row['email']) . "</td>
                <td>" . htmlspecialchars($row['num_persons']) . "</td>
                <td>" . htmlspecialchars($row['booking_date']) . "</td>
                <td>" . htmlspecialchars($row['booking_time']) . "</td>
                <td class='" . $statusClass . "'>" . htmlspecialchars($row['status']) . "</td>
                <td>" . htmlspecialchars($row['approval_status']) . "</td>
                <td>" . htmlspecialchars($row['created_at']) . "</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No bookings found in the database.</p>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 