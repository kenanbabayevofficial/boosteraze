<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Check session security
if (!checkSessionSecurity()) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING)) ?? '';
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)) ?? '';
    
    // Validate input
    if (empty($title) || empty($message)) {
        $error = 'Lütfen başlık ve mesaj alanlarını doldurunuz.';
    } elseif (strlen($title) > 255 || strlen($message) > 1000) {
        $error = 'Başlık veya mesaj çok uzun.';
    } else {
        try {
            $pdo = getDB();
            
            // Get all FCM tokens (from both users table and push_tokens table)
            $stmt = $pdo->query("
                SELECT DISTINCT fcm_token 
                FROM (
                    SELECT fcm_token FROM users WHERE fcm_token IS NOT NULL AND fcm_token != ''
                    UNION
                    SELECT fcm_token FROM push_tokens WHERE fcm_token IS NOT NULL AND fcm_token != '' AND is_active = 1
                ) as all_tokens
                WHERE fcm_token IS NOT NULL AND fcm_token != ''
            ");
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tokens)) {
                $error = 'Gönderilecek FCM token bulunamadı.';
            } else {
                // Send notification using FCM
                $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
                $fcmData = [
                    'registration_ids' => $tokens,
                    'notification' => [
                        'title' => $title,
                        'body' => $message,
                        'icon' => 'ic_notification',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                    ],
                    'data' => [
                        'title' => $title,
                        'message' => $message
                    ]
                ];
                
                $headers = [
                    'Authorization: key=' . FCM_SERVER_KEY,
                    'Content-Type: application/json'
                ];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $fcmUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmData));
                
                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    $response = json_decode($result, true);
                    if ($response && isset($response['success']) && $response['success'] > 0) {
                        $success = "Bildirim başarıyla gönderildi! {$response['success']} cihaza ulaştırıldı.";
                        
                        // Log the notification using prepared statement
                        $stmt = $pdo->prepare("INSERT INTO notifications (title, message, sent_at, success_count, total_count) VALUES (?, ?, NOW(), ?, ?)");
                        $stmt->execute([$title, $message, $response['success'], count($tokens)]);
                    } else {
                        $error = 'Bildirim gönderilemedi. FCM yanıtı: ' . substr($result, 0, 100);
                    }
                } else {
                    $error = 'FCM sunucusuna bağlanılamadı. HTTP Kodu: ' . $httpCode;
                }
            }
        } catch (PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}

// Get statistics
try {
    $pdo = getDB();
    
    // Active tokens count (from both tables)
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT fcm_token) as active_tokens 
        FROM (
            SELECT fcm_token FROM users WHERE fcm_token IS NOT NULL AND fcm_token != ''
            UNION
            SELECT fcm_token FROM push_tokens WHERE fcm_token IS NOT NULL AND fcm_token != '' AND is_active = 1
        ) as all_tokens
    ");
    $activeTokens = $stmt->fetch()['active_tokens'];
    
    // Total notifications sent (from both tables)
    $stmt = $pdo->query("
        SELECT 
            COALESCE(n.count, 0) + COALESCE(ph.count, 0) as total_notifications
        FROM 
            (SELECT COUNT(*) as count FROM notifications) n,
            (SELECT COUNT(*) as count FROM push_history) ph
    ");
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
        
        .form-control {
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
                            <a class="nav-link active" href="push.php">
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
                        
                        <span class="navbar-brand mb-0 h1">Bildirimler</span>
                        
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
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase fw-bold mb-2">Aktif Token</h6>
                                            <h2 class="fw-bold text-primary mb-1"><?php echo number_format($activeTokens); ?></h2>
                                            <p class="text-muted small mb-0">Bildirim alabilir cihaz</p>
                                        </div>
                                        <div class="stat-icon bg-primary">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase fw-bold mb-2">Toplam Bildirim</h6>
                                            <h2 class="fw-bold text-success mb-1"><?php echo number_format($totalNotifications); ?></h2>
                                            <p class="text-muted small mb-0">Gönderilen bildirim</p>
                                        </div>
                                        <div class="stat-icon bg-success">
                                            <i class="fas fa-paper-plane"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Send Notification Form -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-paper-plane me-2 text-primary"></i>
                                        Bildirim Gönder
                                    </h5>
                                    <p class="text-muted mb-0 mt-2">
                                        Tüm kayıtlı kullanıcılara push notification gönderin.
                                    </p>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="title" class="form-label fw-bold">
                                                    <i class="fas fa-heading me-2 text-primary"></i>
                                                    Bildirim Başlığı
                                                </label>
                                                <input type="text" class="form-control" id="title" name="title" 
                                                       placeholder="Bildirim başlığını girin" required>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="message" class="form-label fw-bold">
                                                    <i class="fas fa-comment me-2 text-success"></i>
                                                    Bildirim Mesajı
                                                </label>
                                                <textarea class="form-control" id="message" name="message" rows="3" 
                                                          placeholder="Bildirim mesajını girin" required></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-2"></i>
                                                Bildirim Gönder
                                            </button>
                                            <a href="index.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left me-2"></i>
                                                Ana Sayfaya Dön
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Notifications -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-history me-2 text-primary"></i>
                                        Son Gönderilen Bildirimler
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Başlık</th>
                                                    <th>Mesaj</th>
                                                    <th>Gönderim Tarihi</th>
                                                    <th>Başarılı</th>
                                                    <th>Toplam</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                try {
                                                    $stmt = $pdo->query("
                                                        SELECT title, message, sent_at, success_count, total_count
                                                        FROM notifications 
                                                        ORDER BY sent_at DESC 
                                                        LIMIT 10
                                                    ");
                                                    while ($row = $stmt->fetch()) {
                                                        $successRate = $row['total_count'] > 0 ? round(($row['success_count'] / $row['total_count']) * 100) : 0;
                                                        $statusClass = $successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger');
                                                        
                                                        echo "<tr>";
                                                        echo "<td><strong>" . htmlspecialchars($row['title']) . "</strong></td>";
                                                        echo "<td>" . htmlspecialchars($row['message']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['sent_at']) . "</td>";
                                                        echo "<td><span class='badge bg-success'>" . $row['success_count'] . "</span></td>";
                                                        echo "<td><span class='badge bg-$statusClass'>" . $row['total_count'] . " (" . $successRate . "%)</span></td>";
                                                        echo "</tr>";
                                                    }
                                                } catch (PDOException $e) {
                                                    echo "<tr><td colspan='5' class='text-center text-muted'>Son bildirim bulunamadı</td></tr>";
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