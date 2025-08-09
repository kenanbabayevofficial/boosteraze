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
    
    // Get daily download statistics for the last 30 days
    $stmt = $pdo->query("
        SELECT DATE(download_date) as date, COUNT(*) as count
        FROM download_history 
        WHERE download_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(download_date)
        ORDER BY date
    ");
    $dailyStats = $stmt->fetchAll();
    
    // Get user registration statistics for the last 30 days
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $userStats = $stmt->fetchAll();
    
    // Get platform statistics
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN video_url LIKE '%tiktok%' OR video_url LIKE '%vm.tiktok%' OR video_url LIKE '%vt.tiktok%' THEN 'TikTok'
                WHEN video_url LIKE '%instagram%' OR video_url LIKE '%reels%' THEN 'Instagram'
                WHEN video_url LIKE '%facebook%' OR video_url LIKE '%fb.com%' THEN 'Facebook'
                WHEN video_url LIKE '%youtube%' OR video_url LIKE '%youtu.be%' THEN 'YouTube'
                ELSE 'Diğer'
            END as platform,
            COUNT(*) as count
        FROM download_history 
        WHERE download_status = 'success'
        GROUP BY platform
        ORDER BY count DESC
    ");
    $platformStats = $stmt->fetchAll();
    
    // Get top users by download count
    $stmt = $pdo->query("
        SELECT device_id, COUNT(*) as download_count
        FROM download_history 
        GROUP BY device_id 
        ORDER BY download_count DESC 
        LIMIT 10
    ");
    $topUsers = $stmt->fetchAll();
    
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bs-primary: #0d6efd;
            --bs-secondary: #6c757d;
            --bs-success: #198754;
            --bs-info: #0dcaf0;
            --bs-warning: #ffc107;
            --bs-danger: #dc3545;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0.5rem;
            margin: 0.25rem 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
        }
        
        .main-content {
            background-color: #f8f9fa;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .stat-card {
            background: white;
            border-radius: 1rem;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white fw-bold">
                            <i class="fas fa-video me-2"></i>
                            SnapTikPro
                        </h4>
                        <p class="text-white-50 small">Admin Paneli</p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Ana Sayfa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admob.php">
                                <i class="fas fa-ad"></i>
                                AdMob Ayarları
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="push.php">
                                <i class="fas fa-bell"></i>
                                Bildirimler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="stats.php">
                                <i class="fas fa-chart-bar"></i>
                                İstatistikler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i>
                                Kullanıcılar
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Top navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm rounded-3 my-3">
                    <div class="container-fluid">
                        <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <span class="navbar-brand mb-0 h1">İstatistikler</span>
                        
                        <div class="navbar-nav ms-auto">
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <span class="me-2"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="change_password.php">
                                        <i class="fas fa-key me-2"></i>Şifre Değiştir
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Çıkış Yap
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="container-fluid">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-xl-8 mb-4">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-chart-line me-2 text-primary"></i>
                                        Günlük İndirme İstatistikleri (Son 30 Gün)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="downloadChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 mb-4">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-chart-pie me-2 text-success"></i>
                                        Platform Dağılımı
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="platformChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Registration Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-user-plus me-2 text-info"></i>
                                        Kullanıcı Kayıt İstatistikleri (Son 30 Gün)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="userChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Users Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-trophy me-2 text-warning"></i>
                                        En Aktif Kullanıcılar
                                    </h5>
                                    <p class="text-muted mb-0 mt-2">
                                        En çok indirme yapan kullanıcılar.
                                    </p>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Cihaz ID</th>
                                                    <th>İndirme Sayısı</th>
                                                    <th>Son İndirme</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if (isset($topUsers) && !empty($topUsers)):
                                                    $rank = 1;
                                                    foreach ($topUsers as $user):
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($rank <= 3): ?>
                                                            <span class="badge bg-warning text-dark">
                                                                <i class="fas fa-medal me-1"></i><?php echo $rank; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?php echo $rank; ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <code class="text-primary"><?php echo htmlspecialchars(substr($user['device_id'], 0, 20)); ?>...</code>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <?php echo number_format($user['download_count']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php 
                                                            try {
                                                                $stmt = $pdo->prepare("SELECT MAX(download_date) as last_download FROM download_history WHERE device_id = ?");
                                                                $stmt->execute([$user['device_id']]);
                                                                $lastDownload = $stmt->fetch()['last_download'];
                                                                echo date('d.m.Y H:i', strtotime($lastDownload));
                                                            } catch (PDOException $e) {
                                                                echo 'Bilinmiyor';
                                                            }
                                                            ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                                <?php 
                                                        $rank++;
                                                    endforeach;
                                                else:
                                                ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-4">
                                                        <i class="fas fa-chart-bar fa-2x mb-3"></i>
                                                        <p>Henüz istatistik verisi bulunmuyor</p>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Download Chart
        const downloadCtx = document.getElementById('downloadChart').getContext('2d');
        new Chart(downloadCtx, {
            type: 'line',
            data: {
                labels: <?php echo !empty($dailyStats) ? json_encode(array_column($dailyStats, 'date')) : json_encode([]); ?>,
                datasets: [{
                    label: 'Günlük İndirmeler',
                    data: <?php echo !empty($dailyStats) ? json_encode(array_column($dailyStats, 'count')) : json_encode([]); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Platform Chart
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo !empty($platformStats) ? json_encode(array_column($platformStats, 'platform')) : json_encode(['Veri Yok']); ?>,
                datasets: [{
                    data: <?php echo !empty($platformStats) ? json_encode(array_column($platformStats, 'count')) : json_encode([1]); ?>,
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#ff6b6b', '#4ecdc4'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // User Registration Chart
        const userCtx = document.getElementById('userChart').getContext('2d');
        new Chart(userCtx, {
            type: 'bar',
            data: {
                labels: <?php echo !empty($userStats) ? json_encode(array_column($userStats, 'date')) : json_encode([]); ?>,
                datasets: [{
                    label: 'Yeni Kullanıcılar',
                    data: <?php echo !empty($userStats) ? json_encode(array_column($userStats, 'count')) : json_encode([]); ?>,
                    backgroundColor: '#0dcaf0',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>