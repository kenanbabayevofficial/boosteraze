<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = "Hatalı giriş!";
    }
}
?>
<form method="post">
    <input name="username" placeholder="Kullanıcı Adı">
    <input name="password" type="password" placeholder="Şifre">
    <button type="submit">Giriş</button>
    <?php if (!empty($error)) echo "<div>$error</div>"; ?>
</form>