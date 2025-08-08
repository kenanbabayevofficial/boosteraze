<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';

try {
    $pdo = getDB();
    
    // Get overall statistics
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $stmt->fetch()['total'];
    
    // Active users (last 7 days)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['active_users_7d'] = $stmt->fetch()['total'];
    
    // Active users (last 30 days)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['active_users_30d'] = $stmt->fetch()['total'];
    
    // Total downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success'");
    $stats['total_downloads'] = $stmt->fetch()['total'];
    
    // Today's downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success' AND DATE(download_date) = CURDATE()");
    $stats['today_downloads'] = $stmt->fetch()['total'];
    
    // Yesterday's downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success' AND DATE(download_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
    $stats['yesterday_downloads'] = $stmt->fetch()['total'];
    
    // This week's downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success' AND YEARWEEK(download_date) = YEARWEEK(NOW())");
    $stats['week_downloads'] = $stmt->fetch()['total'];
    
    // This month's downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success' AND MONTH(download_date) = MONTH(NOW()) AND YEAR(download_date) = YEAR(NOW())");
    $stats['month_downloads'] = $stmt->fetch()['total'];
    
    // Daily downloads for last 7 days
    $stmt = $pdo->query("
        SELECT DATE(download_date) as date, COUNT(*) as count 
        FROM download_history 
        WHERE download_status = 'success' 
        AND download_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(download_date) 
        ORDER BY date
    ");
    $daily_downloads = $stmt->fetchAll();
    
    // Top users by downloads
    $stmt = $pdo->query("
        SELECT u.device_id, COUNT(dh.id) as download_count, u.last_seen
        FROM users u
        LEFT JOIN download_history dh ON u.device_id = dh.device_id AND dh.download_status = 'success'
        GROUP BY u.device_id
        ORDER BY download_count DESC
        LIMIT 10
    ");
    $top_users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Veritabanı hatası: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İstatistikler - SnapTikPro Admin Paneli</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <div class="user-menu-dropdown">
                    <button class="user-menu-trigger" onclick="toggleUserMenu()">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-menu-dropdown-content" id="userDropdown">
                        <a href="change_password.php">
                            <i class="fas fa-key"></i>
                            Şifreyi Değiştir
                        </a>
                        <div class="divider"></div>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            Çıkış Yap
                        </a>
                    </div>
                </div>
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
                <a href="push.php" class="nav-link">
                    <i class="fas fa-bell nav-icon"></i>
                    Bildirimler
                </a>
            </li>
            <li class="nav-item">
                <a href="stats.php" class="nav-link active">
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
                <h1>İstatistikler ve Analizler</h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Uygulama kullanımı hakkında detaylı analizler ve içgörüler.
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error fade-in">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Overview Cards -->
            <div class="dashboard-grid fade-in">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Toplam Kullanıcı</h3>
                        <div class="card-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="card-label">Kayıtlı cihaz</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aktif Kullanıcı (7g)</h3>
                        <div class="card-icon users">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($stats['active_users_7d']); ?></div>
                    <div class="card-label">Son 7 gün</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Toplam İndirme</h3>
                        <div class="card-icon downloads">
                            <i class="fas fa-download"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($stats['total_downloads']); ?></div>
                    <div class="card-label">Tüm zamanlar</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bugünkü İndirme</h3>
                        <div class="card-icon downloads">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($stats['today_downloads']); ?></div>
                    <div class="card-label">Bugün indirilen</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="card fade-in">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Günlük İndirmeler (Son 7 Gün)
                </h3>
                <canvas id="downloadsChart" width="400" height="200"></canvas>
            </div>

            <!-- Detailed Stats -->
            <div class="dashboard-grid fade-in">
                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-week"></i>
                        Haftalık İstatistikler
                    </h3>
                    <div style="margin-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Bu Hafta:</span>
                            <strong><?php echo number_format($stats['week_downloads']); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Dün:</span>
                            <strong><?php echo number_format($stats['yesterday_downloads']); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Aktif Kullanıcı (30g):</span>
                            <strong><?php echo number_format($stats['active_users_30d']); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt"></i>
                        Aylık İstatistikler
                    </h3>
                    <div style="margin-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Bu Ay:</span>
                            <strong><?php echo number_format($stats['month_downloads']); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Günlük Ortalama:</span>
                            <strong><?php echo number_format(round($stats['month_downloads'] / date('d'))); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Toplam Kullanıcı:</span>
                            <strong><?php echo number_format($stats['total_users']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Users -->
            <div class="card fade-in">
                <h3 class="card-title">
                    <i class="fas fa-trophy"></i>
                    İndirme Sayısına Göre En İyi Kullanıcılar
                </h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sıra</th>
                                <th>Cihaz ID</th>
                                <th>İndirme</th>
                                <th>Son Görülme</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($top_users)): ?>
                                <?php foreach ($top_users as $index => $user): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index < 3): ?>
                                                <span style="color: #FFD700; font-weight: bold;">🥇</span>
                                            <?php else: ?>
                                                <span class="badge badge-info"><?php echo $index + 1; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars(substr($user['device_id'], 0, 20)); ?>...</code>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">
                                                <?php echo number_format($user['download_count']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['last_seen']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-secondary);">
                                        Veri bulunamadı
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Chart data
        const chartData = {
            labels: <?php echo json_encode(array_map(function($item) { return date('d/m', strtotime($item['date'])); }, $daily_downloads)); ?>,
            datasets: [{
                label: 'Günlük İndirmeler',
                data: <?php echo json_encode(array_map(function($item) { return $item['count']; }, $daily_downloads)); ?>,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                tension: 0.4
            }]
        };

        // Create chart
        const ctx = document.getElementById('downloadsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>

    <script>
        // User dropdown menu
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.user-menu-trigger') && !event.target.matches('.user-menu-trigger *')) {
                const dropdowns = document.getElementsByClassName('user-menu-dropdown-content');
                for (let dropdown of dropdowns) {
                    if (dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>
</html>