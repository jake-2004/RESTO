<?php
session_start();

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$response = array('valid' => false);

// Check if all required session variables exist
if (isset($_SESSION['user_id']) && 
    isset($_SESSION['page_token']) &&
    isset($data['token']) && 
    isset($data['current_page'])) {
    
    // Verify the page token matches
    if ($_SESSION['page_token'] === $data['token']) {
        $response['valid'] = true;
    }
}

// If session is invalid, clear it
if (!$response['valid']) {
    session_unset();
    session_destroy();
    
    // Set cache control headers
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Clear-Site-Data: \"cache\", \"cookies\", \"storage\"");
}

header('Content-Type: application/json');
echo json_encode($response);
?>
