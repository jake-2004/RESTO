<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    echo json_encode(['count' => (int)$result['count']]);
} catch (PDOException $e) {
    error_log("Error fetching cart count: " . $e->getMessage());
    echo json_encode(['count' => 0]);
} 