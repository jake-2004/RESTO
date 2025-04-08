<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

try {
    // Get count of all bookings from TableBookings table
    $sql = "SELECT COUNT(*) as count FROM TableBookings";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        echo json_encode(['count' => $row['count']]);
    } else {
        throw new Exception("Error: " . $conn->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
