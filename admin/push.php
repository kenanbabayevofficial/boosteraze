<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Handle push notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    
    if (empty($title) || empty($body)) {
        $error = 'Lütfen başlık ve mesaj alanlarını doldurunuz.';
    } else {
        try {
            $pdo = getDB();
            
            // Get all active push tokens
            $stmt = $pdo->query("SELECT fcm_token FROM push_tokens WHERE is_active = 1");
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tokens)) {
                $error = 'Aktif push token bulunamadı.';
            } else {
                // Include FCM send function
                require_once 'fcm_send.php';
                $result = sendPush($tokens, $title, $body);
                
                if ($result) {
                    $success = "Push bildirimi " . count($tokens) . " cihaza başarıyla gönderildi!";
                    
                    // Log the notification
                    $stmt = $pdo->prepare("INSERT INTO push_history (title, message, sent_by, target_devices, successful_sends) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $body, $_SESSION['admin_id'], count($tokens), count($tokens)]);
                } else {
                    $error = 'Push bildirimi gönderilemedi.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}

// Get notification statistics
try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) as total_tokens FROM push_tokens WHERE is_active = 1");
    $totalTokens = $stmt->fetch()['total_tokens'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_notifications FROM push_history");
    $totalNotifications = $stmt->fetch()['total_notifications'];
} catch (PDOException $e) {
    $error = 'Veritabanı hatası: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildirimler - SnapTikPro Admin Paneli</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-video"></i>
                </div>
                <span>SnapTikPro Admin</span>
            </div>
            <div class="user-menu">
                <span>Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <a href="logout.php" class="btn btn-secondary">Çıkış Yap</a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    Ana Sayfa
                </a>
            </li>
            <li class="nav-item">
                <a href="admob.php" class="nav-link">
                    <i class="fas fa-ad nav-icon"></i>
                    AdMob Ayarları
                </a>
            </li>
            <li class="nav-item">
                <a href="push.php" class="nav-link active">
                    <i class="fas fa-bell nav-icon"></i>
                    Bildirimler
                </a>
            </li>
            <li class="nav-item">
                <a href="stats.php" class="nav-link">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    İstatistikler
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <i class="fas fa-users nav-icon"></i>
                    Kullanıcılar
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="fade-in">
                <h1>Bildirimler</h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Tüm kayıtlı kullanıcılara push bildirimi gönderin.
                </p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success fade-in">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error fade-in">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="dashboard-grid fade-in">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aktif Token</h3>
                        <div class="card-icon notifications">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($totalTokens); ?></div>
                    <div class="card-label">Kayıtlı cihaz</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Toplam Gönderilen</h3>
                        <div class="card-icon notifications">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($totalNotifications); ?></div>
                    <div class="card-label">Gönderilen bildirim</div>
                </div>
            </div>

            <!-- Send Notification Form -->
            <div class="form-container fade-in">
                <h3 class="card-title">
                    <i class="fas fa-bell"></i>
                    Push Bildirimi Gönder
                </h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading"></i>
                            Bildirim Başlığı
                        </label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                               placeholder="Bildirim başlığını girin" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="body" class="form-label">
                            <i class="fas fa-comment"></i>
                            Bildirim Mesajı
                        </label>
                        <textarea id="body" name="body" class="form-textarea" 
                                  placeholder="Bildirim mesajını girin" required><?php echo htmlspecialchars($_POST['body'] ?? ''); ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Bildirimi Gönder
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Ana Sayfaya Dön
                        </a>
                    </div>
                </form>
            </div>

            <!-- Notification History -->
            <div class="card fade-in" style="margin-top: 2rem;">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Son Bildirimler
                </h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Başlık</th>
                                <th>Mesaj</th>
                                <th>Gönderim Tarihi</th>
                                <th>Hedef Cihaz</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT title, message, sent_at, target_devices 
                                    FROM push_history 
                                    ORDER BY sent_at DESC 
                                    LIMIT 10
                                ");
                                while ($row = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                    echo "<td>" . htmlspecialchars(substr($row['message'], 0, 50)) . "...</td>";
                                    echo "<td>" . htmlspecialchars($row['sent_at']) . "</td>";
                                    echo "<td><span class='badge badge-info'>" . number_format($row['target_devices']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='4'>Bildirim geçmişi bulunamadı</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>