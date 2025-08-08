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
$success = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $device_id = $_POST['device_id'] ?? '';
    
    try {
        $pdo = getDB();
        
        switch ($action) {
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM users WHERE device_id = ?");
                $stmt->execute([$device_id]);
                $success = 'Kullanıcı başarıyla silindi!';
                break;
                
            case 'deactivate':
                $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE device_id = ?");
                $stmt->execute([$device_id]);
                $success = 'Kullanıcı başarıyla devre dışı bırakıldı!';
                break;
                
            case 'activate':
                $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE device_id = ?");
                $stmt->execute([$device_id]);
                $success = 'Kullanıcı başarıyla etkinleştirildi!';
                break;
        }
    } catch (PDOException $e) {
        $error = 'Veritabanı hatası: ' . $e->getMessage();
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $pdo = getDB();
    
    // Get total count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    $totalPages = ceil($totalUsers / $limit);
    
    // Get users
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(dh.id) as download_count,
               MAX(dh.download_date) as last_download
        FROM users u 
        LEFT JOIN download_history dh ON u.device_id = dh.device_id
        GROUP BY u.device_id 
        ORDER BY u.last_seen DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
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
                <a href="stats.php" class="nav-link">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    İstatistikler
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link active">
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
                <h1>Kullanıcı Yönetimi</h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Kayıtlı kullanıcıları ve aktivitelerini yönetin.
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

            <!-- Users Table -->
            <div class="card fade-in">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i>
                        Kayıtlı Kullanıcılar (<?php echo number_format($totalUsers); ?>)
                    </h3>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cihaz ID</th>
                                <th>Cihaz Modeli</th>
                                <th>Android Sürümü</th>
                                <th>İndirme</th>
                                <th>Son Görülme</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <code><?php echo htmlspecialchars(substr($user['device_id'], 0, 20)); ?>...</code>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['device_model'] ?? 'Bilinmiyor'); ?></td>
                                        <td><?php echo htmlspecialchars($user['android_version'] ?? 'Bilinmiyor'); ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo number_format($user['download_count']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['last_seen']); ?></td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-error">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="device_id" value="<?php echo htmlspecialchars($user['device_id']); ?>">
                                                <?php if ($user['is_active']): ?>
                                                    <button type="submit" name="action" value="deactivate" class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="activate" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="submit" name="action" value="delete" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-secondary);">
                                        Kullanıcı bulunamadı
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary">
                                <i class="fas fa-chevron-left"></i>
                                Önceki
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary">
                                Sonraki
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

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