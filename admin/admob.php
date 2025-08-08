<?php
session_start();
if (!isset($_SESSION['admin'])) header('Location: login.php');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $banner = $_POST['banner'] ?? '';
    $interstitial = $_POST['interstitial'] ?? '';
    $rewarded = $_POST['rewarded'] ?? '';
    $pdo->query("DELETE FROM settings");
    $stmt = $pdo->prepare("INSERT INTO settings (admob_banner, admob_interstitial, admob_rewarded) VALUES (?, ?, ?)");
    $stmt->execute([$banner, $interstitial, $rewarded]);
    $msg = "Kaydedildi!";
}
$row = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
?>
<form method="post">
    Banner: <input name="banner" value="<?= htmlspecialchars($row['admob_banner'] ?? '') ?>"><br>
    Interstitial: <input name="interstitial" value="<?= htmlspecialchars($row['admob_interstitial'] ?? '') ?>"><br>
    Rewarded: <input name="rewarded" value="<?= htmlspecialchars($row['admob_rewarded'] ?? '') ?>"><br>
    <button type="submit">Kaydet</button>
    <?php if (!empty($msg)) echo "<div>$msg</div>"; ?>
</form>
<a href="index.php">Geri</a>