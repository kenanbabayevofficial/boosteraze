<?php
session_start();
if (!isset($_SESSION['admin'])) header('Location: login.php');
require 'db.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    $tokens = $pdo->query("SELECT token FROM push_tokens")->fetchAll(PDO::FETCH_COLUMN);
    require 'fcm_send.php';
    $result = sendPush($tokens, $title, $body);
    $msg = "Bildirim gönderildi!";
}
?>
<form method="post">
    Başlık: <input name="title"><br>
    Mesaj: <input name="body"><br>
    <button type="submit">Gönder</button>
    <?php if (!empty($msg)) echo "<div>$msg</div>"; ?>
</form>
<a href="index.php">Geri</a>