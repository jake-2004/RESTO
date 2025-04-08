<?php
session_start();

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['redirect'])) {
    $_SESSION['redirect_after_login'] = $data['redirect'];
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No redirect specified']);
}
?>
