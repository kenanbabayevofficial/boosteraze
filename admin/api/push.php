<?php
require '../db.php';
require '../config.php';
require '../fcm_send.php';
$title = $_POST['title'] ?? '';
$body = $_POST['body'] ?? '';
$tokens = $pdo->query("SELECT token FROM push_tokens")->fetchAll(PDO::FETCH_COLUMN);
echo sendPush($tokens, $title, $body);