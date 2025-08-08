<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get statistics
try {
    $pdo = getDB();
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $totalUsers = $stmt->fetch()['total_users'];
    
    // Active users (last 7 days)
    $stmt = $pdo->query("SELECT COUNT(*) as active_users FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $activeUsers = $stmt->fetch()['active_users'];
    
    // Total downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total_downloads FROM download_history WHERE download_status = 'success'");
    $totalDownloads = $stmt->fetch()['total_downloads'];
    
    // Today's downloads
    $stmt = $pdo->query("SELECT COUNT(*) as today_downloads FROM download_history WHERE download_status = 'success' AND DATE(download_date) = CURDATE()");
    $todayDownloads = $stmt->fetch()['today_downloads'];
    
} catch (PDOException $e) {
    $error = "Veritabanı hatası: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapTikPro Admin Paneli</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="sidebar-logo">
                    <i class="fas fa-video"></i>
                    <span>SnapTikPro</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Ana Sayfa
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admob.php" class="nav-link">
                        <i class="fas fa-ad"></i>
                        AdMob Ayarları
                    </a>
                </div>
                <div class="nav-item">
                    <a href="push.php" class="nav-link">
                        <i class="fas fa-bell"></i>
                        Bildirimler
                    </a>
                </div>
                <div class="nav-item">
                    <a href="stats.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        İstatistikler
                    </a>
                </div>
                <div class="nav-item">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Kullanıcılar
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-title">Ana Sayfa</div>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                        <div class="user-role">Admin</div>
                    </div>
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <a href="logout.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                        Çıkış
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Hoş Geldiniz!</h1>
                    <p class="page-subtitle">SnapTikPro Admin Paneli - Uygulama yönetimi ve analitikler</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Dashboard Stats -->
                <div class="dashboard-grid">
                    <div class="stat-card users">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Toplam Kullanıcı</div>
                                <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
                                <div class="stat-label">Kayıtlı cihaz</div>
                            </div>
                            <div class="stat-icon users">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card downloads">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Aktif Kullanıcı</div>
                                <div class="stat-value"><?php echo number_format($activeUsers); ?></div>
                                <div class="stat-label">Son 7 gün</div>
                            </div>
                            <div class="stat-icon downloads">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card notifications">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Toplam İndirme</div>
                                <div class="stat-value"><?php echo number_format($totalDownloads); ?></div>
                                <div class="stat-label">Başarılı indirme</div>
                            </div>
                            <div class="stat-icon notifications">
                                <i class="fas fa-download"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card revenue">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Bugünkü İndirme</div>
                                <div class="stat-value"><?php echo number_format($todayDownloads); ?></div>
                                <div class="stat-label">Bugün indirilen</div>
                            </div>
                            <div class="stat-icon revenue">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Hızlı İşlemler</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <a href="admob.php" class="btn btn-primary">
                                <i class="fas fa-ad"></i>
                                AdMob Yönet
                            </a>
                            <a href="push.php" class="btn btn-success">
                                <i class="fas fa-bell"></i>
                                Bildirim Gönder
                            </a>
                            <a href="stats.php" class="btn btn-warning">
                                <i class="fas fa-chart-line"></i>
                                Analizleri Görüntüle
                            </a>
                            <a href="users.php" class="btn btn-info">
                                <i class="fas fa-users"></i>
                                Kullanıcıları Yönet
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Son Aktiviteler</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cihaz ID</th>
                                        <th>İşlem</th>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("
                                            SELECT device_id, 'İndirme' as action, download_date as date, download_status as status
                                            FROM download_history 
                                            ORDER BY download_date DESC 
                                            LIMIT 10
                                        ");
                                        while ($row = $stmt->fetch()) {
                                            $statusClass = $row['status'] === 'success' ? 'badge-success' : 'badge-error';
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars(substr($row['device_id'], 0, 20)) . "...</td>";
                                            echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                            echo "<td><span class='badge $statusClass'>" . htmlspecialchars($row['status']) . "</span></td>";
                                            echo "</tr>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='4'>Son aktivite bulunamadı</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.fade-in');
            cards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
            });
        });
    </script>
</body>
</html>