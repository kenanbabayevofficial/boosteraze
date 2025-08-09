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

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $pdo = getDB();
    
    // Get total count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    $totalPages = ceil($totalUsers / $limit);
    
    // Get users with pagination
    $stmt = $pdo->prepare("
        SELECT 
            u.device_id, 
            u.fcm_token, 
            u.last_seen, 
            u.created_at,
            COALESCE(dh.download_count, 0) as download_count
        FROM users u
        LEFT JOIN (
            SELECT device_id, COUNT(*) as download_count
            FROM download_history 
            WHERE download_status = 'success'
            GROUP BY device_id
        ) dh ON u.device_id = dh.device_id
        ORDER BY u.last_seen DESC 
        LIMIT " . (int)$offset . ", " . (int)$limit
    );
    $stmt->execute();
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Veritabanı hatası: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcılar - SnapTikPro Admin Paneli</title>
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
        
        .pagination {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .page-link {
            border: none;
            color: #667eea;
            font-weight: 500;
        }
        
        .page-link:hover {
            background-color: #667eea;
            color: white;
        }
        
        .page-item.active .page-link {
            background-color: #667eea;
            border-color: #667eea;
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
                            <a class="nav-link" href="stats.php">
                                <i class="fas fa-chart-bar"></i>
                                İstatistikler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="users.php">
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
                        
                        <span class="navbar-brand mb-0 h1">Kullanıcılar</span>
                        
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

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-4 col-md-6 mb-4">
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

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase fw-bold mb-2">Aktif Kullanıcı</h6>
                                            <h2 class="fw-bold text-success mb-1">
                                                <?php 
                                                try {
                                                    $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                                                    echo number_format($stmt->fetch()['active']);
                                                } catch (PDOException $e) {
                                                    echo '0';
                                                }
                                                ?>
                                            </h2>
                                            <p class="text-muted small mb-0">Son 7 gün</p>
                                        </div>
                                        <div class="stat-icon bg-success">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase fw-bold mb-2">FCM Token</h6>
                                            <h2 class="fw-bold text-info mb-1">
                                                <?php 
                                                try {
                                                    $stmt = $pdo->query("
                                                        SELECT COUNT(DISTINCT fcm_token) as fcm 
                                                        FROM (
                                                            SELECT fcm_token FROM users WHERE fcm_token IS NOT NULL AND fcm_token != ''
                                                            UNION
                                                            SELECT fcm_token FROM push_tokens WHERE fcm_token IS NOT NULL AND fcm_token != '' AND is_active = 1
                                                        ) as all_tokens
                                                    ");
                                                    echo number_format($stmt->fetch()['fcm']);
                                                } catch (PDOException $e) {
                                                    echo '0';
                                                }
                                                ?>
                                            </h2>
                                            <p class="text-muted small mb-0">Bildirim alabilir</p>
                                        </div>
                                        <div class="stat-icon bg-info">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-users me-2 text-primary"></i>
                                        Kullanıcı Listesi
                                    </h5>
                                    <p class="text-muted mb-0 mt-2">
                                        Kayıtlı kullanıcıları görüntüleyin ve yönetin.
                                    </p>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Cihaz ID</th>
                                                    <th>FCM Token</th>
                                                    <th>Son Görülme</th>
                                                    <th>Kayıt Tarihi</th>
                                                    <th>İndirme Sayısı</th>
                                                    <th>Durum</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if (isset($users) && !empty($users)):
                                                    $counter = $offset + 1;
                                                    foreach ($users as $user):
                                                        $isActive = strtotime($user['last_seen']) > strtotime('-7 days');
                                                        $hasFCM = !empty($user['fcm_token']);
                                                        $statusClass = $isActive ? 'success' : 'secondary';
                                                        $statusText = $isActive ? 'Aktif' : 'Pasif';
                                                ?>
                                                <tr>
                                                    <td><?php echo $counter++; ?></td>
                                                    <td>
                                                        <code class="text-primary"><?php echo htmlspecialchars(substr($user['device_id'], 0, 20)); ?>...</code>
                                                    </td>
                                                    <td>
                                                        <?php if ($hasFCM): ?>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check me-1"></i>Mevcut
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-times me-1"></i>Yok
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo date('d.m.Y H:i', strtotime($user['last_seen'])); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <?php echo number_format($user['download_count']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    endforeach;
                                                else:
                                                ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        <i class="fas fa-users fa-2x mb-3"></i>
                                                        <p>Henüz kullanıcı bulunmuyor</p>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <?php if ($totalPages > 1): ?>
                                    <nav aria-label="Kullanıcı sayfaları" class="mt-4">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                    <?php endif; ?>
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