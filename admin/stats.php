<?php
session_start();
if (!isset($_SESSION['admin'])) header('Location: login.php');
require 'db.php';

$total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$today = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$thisMonth = $pdo->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
?>
<h3>İstatistikler</h3>
Toplam: <?= $total ?><br>
Bugün: <?= $today ?><br>
Bu ay: <?= $thisMonth ?><br>
<a href="index.php">Geri</a>