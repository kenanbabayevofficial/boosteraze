<?php
session_start();
if (!isset($_SESSION['admin'])) header('Location: login.php');
?>
<h2>SnapTikPro Admin Panel</h2>
<ul>
    <li><a href="admob.php">AdMob Kodları</a></li>
    <li><a href="push.php">Push Bildirim Gönder</a></li>
    <li><a href="stats.php">Kullanıcı/İndirme İstatistikleri</a></li>
    <li><a href="logout.php">Çıkış</a></li>
</ul>