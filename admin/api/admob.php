<?php
require '../db.php';
header('Content-Type: application/json');
$row = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
echo json_encode([
    'banner' => $row['admob_banner'] ?? '',
    'interstitial' => $row['admob_interstitial'] ?? '',
    'rewarded' => $row['admob_rewarded'] ?? ''
]);