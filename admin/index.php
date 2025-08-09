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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
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
        
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
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
                            <a class="nav-link active" href="index.php">
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
                            <a class="nav-link" href="stats.php">
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
                        
                        <span class="navbar-brand mb-0 h1">Ana Sayfa</span>
                        
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
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Welcome Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <h1 class="display-6 fw-bold text-primary mb-3">Hoş Geldiniz!</h1>
                                    <p class="lead text-muted">SnapTikPro Admin Paneli - Uygulama yönetimi ve analitikler</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase fw-bold mb-2">Toplam Kullanıcı</h6>
                                            <h2 class="fw-bold text-primary mb-1"><?php echo number_format($totalUsers); ?></h2>
                                            <p class="text-muted small mb-0">Kayıtlı cihaz</p>
                                        </div>
                                        <div class="stat-icon bg-primary">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase fw-bold mb-2">Aktif Kullanıcı</h6>
                                            <h2 class="fw-bold text-success mb-1"><?php echo number_format($activeUsers); ?></h2>
                                            <p class="text-muted small mb-0">Son 7 gün</p>
                                        </div>
                                        <div class="stat-icon bg-success">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase fw-bold mb-2">Toplam İndirme</h6>
                                            <h2 class="fw-bold text-info mb-1"><?php echo number_format($totalDownloads); ?></h2>
                                            <p class="text-muted small mb-0">Başarılı indirme</p>
                                        </div>
                                        <div class="stat-icon bg-info">
                                            <i class="fas fa-download"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase fw-bold mb-2">Bugünkü İndirme</h6>
                                            <h2 class="fw-bold text-warning mb-1"><?php echo number_format($todayDownloads); ?></h2>
                                            <p class="text-muted small mb-0">Bugün indirilen</p>
                                        </div>
                                        <div class="stat-icon bg-warning">
                                            <i class="fas fa-calendar-day"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-bolt me-2 text-primary"></i>
                                        Hızlı İşlemler
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-3 col-md-6">
                                            <a href="admob.php" class="btn btn-primary w-100">
                                                <i class="fas fa-ad me-2"></i>
                                                AdMob Yönet
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="push.php" class="btn btn-success w-100">
                                                <i class="fas fa-bell me-2"></i>
                                                Bildirim Gönder
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="stats.php" class="btn btn-warning w-100">
                                                <i class="fas fa-chart-line me-2"></i>
                                                Analizleri Görüntüle
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="users.php" class="btn btn-info w-100">
                                                <i class="fas fa-users me-2"></i>
                                                Kullanıcıları Yönet
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-clock me-2 text-primary"></i>
                                        Son Aktiviteler
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
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
                                                        $statusClass = $row['status'] === 'success' ? 'success' : 'danger';
                                                        echo "<tr>";
                                                        echo "<td><code>" . htmlspecialchars(substr($row['device_id'], 0, 20)) . "...</code></td>";
                                                        echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                                        echo "<td><span class='badge bg-$statusClass'>" . htmlspecialchars($row['status']) . "</span></td>";
                                                        echo "</tr>";
                                                    }
                                                } catch (PDOException $e) {
                                                    echo "<tr><td colspan='4' class='text-center text-muted'>Son aktivite bulunamadı</td></tr>";
                                                }
                                                ?>
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
</body>
</html>