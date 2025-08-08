<?php
require '../db.php';
$device = $_POST['device_id'] ?? '';
$token = $_POST['token'] ?? '';
if ($device && $token) {
    $stmt = $pdo->prepare("INSERT INTO push_tokens (device_id, token) VALUES (?, ?) ON DUPLICATE KEY UPDATE token=VALUES(token)");
    $stmt->execute([$device, $token]);
    echo 'OK';
} else {
    http_response_code(400);
    echo 'device_id and token required';
}