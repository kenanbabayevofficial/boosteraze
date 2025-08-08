<?php
require '../db.php';
$device = $_POST['device_id'] ?? '';
if ($device) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (device_id) VALUES (?)");
    $stmt->execute([$device]);
    echo 'OK';
} else {
    http_response_code(400);
    echo 'device_id required';
}