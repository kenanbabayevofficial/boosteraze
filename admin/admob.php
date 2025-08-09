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
    $settings = [
        'admob_banner' => trim(filter_input(INPUT_POST, 'admob_banner', FILTER_SANITIZE_STRING)) ?? '',
        'admob_interstitial' => trim(filter_input(INPUT_POST, 'admob_interstitial', FILTER_SANITIZE_STRING)) ?? '',
        'admob_rewarded' => trim(filter_input(INPUT_POST, 'admob_rewarded', FILTER_SANITIZE_STRING)) ?? '',
        'admob_app_id' => trim(filter_input(INPUT_POST, 'admob_app_id', FILTER_SANITIZE_STRING)) ?? ''
    ];
    
    // Validate AdMob IDs format
    $validAdMobPattern = '/^ca-app-pub-[0-9]+\/[0-9]+$/';
    $validAppIdPattern = '/^ca-app-pub-[0-9]+~[0-9]+$/';
    
    $isValid = true;
    $errorMessage = '';
    
    if (!empty($settings['admob_banner']) && !preg_match($validAdMobPattern, $settings['admob_banner'])) {
        $isValid = false;
        $errorMessage = 'Geçersiz Banner AdMob ID formatı.';
    }
    
    if (!empty($settings['admob_interstitial']) && !preg_match($validAdMobPattern, $settings['admob_interstitial'])) {
        $isValid = false;
        $errorMessage = 'Geçersiz Interstitial AdMob ID formatı.';
    }
    
    if (!empty($settings['admob_rewarded']) && !preg_match($validAdMobPattern, $settings['admob_rewarded'])) {
        $isValid = false;
        $errorMessage = 'Geçersiz Rewarded AdMob ID formatı.';
    }
    
    if (!empty($settings['admob_app_id']) && !preg_match($validAppIdPattern, $settings['admob_app_id'])) {
        $isValid = false;
        $errorMessage = 'Geçersiz App ID formatı.';
    }
    
    if ($isValid) {
        try {
            $pdo = getDB();
            
            // Update settings using prepared statements
            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $value, $value]);
            }
            
            $success = 'AdMob ayarları başarıyla güncellendi.';
            
        } catch (PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    } else {
        $error = $errorMessage;
    }
}

// Get current settings
try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'admob_%'");
    $currentSettings = [];
    while ($row = $stmt->fetch()) {
        $currentSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $error = 'Veritabanı hatası: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdMob Ayarları - SnapTikPro Admin Paneli</title>
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
                            <a class="nav-link active" href="admob.php">
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
                        
                        <span class="navbar-brand mb-0 h1">AdMob Ayarları</span>
                        
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

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-ad me-2 text-primary"></i>
                                        AdMob Reklam Ayarları
                                    </h5>
                                    <p class="text-muted mb-0 mt-2">
                                        AdMob reklam birimi ID'lerinizi yönetin. Bu ayarlar Android uygulaması tarafından reklamları göstermek için kullanılacaktır.
                                    </p>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="admob_app_id" class="form-label fw-bold">
                                                    <i class="fas fa-mobile-alt me-2 text-primary"></i>
                                                    AdMob Uygulama ID
                                                </label>
                                                <input type="text" class="form-control" id="admob_app_id" name="admob_app_id" 
                                                       value="<?php echo htmlspecialchars($currentSettings['admob_app_id'] ?? ''); ?>" 
                                                       placeholder="ca-app-pub-xxxxxxxxxxxxxxxx~yyyyyyyyyy">
                                                <div class="form-text">
                                                    AdMob uygulama ID'niz (AdMob konsolunda bulunur)
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="admob_banner" class="form-label fw-bold">
                                                    <i class="fas fa-rectangle-ad me-2 text-success"></i>
                                                    Banner Reklam Birimi ID
                                                </label>
                                                <input type="text" class="form-control" id="admob_banner" name="admob_banner" 
                                                       value="<?php echo htmlspecialchars($currentSettings['admob_banner'] ?? ''); ?>" 
                                                       placeholder="ca-app-pub-xxxxxxxxxxxxxxxx/yyyyyyyyyy">
                                                <div class="form-text">
                                                    Banner reklamları göstermek için banner reklam birimi ID
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="admob_interstitial" class="form-label fw-bold">
                                                    <i class="fas fa-window-maximize me-2 text-warning"></i>
                                                    Tam Sayfa Reklam Birimi ID
                                                </label>
                                                <input type="text" class="form-control" id="admob_interstitial" name="admob_interstitial" 
                                                       value="<?php echo htmlspecialchars($currentSettings['admob_interstitial'] ?? ''); ?>" 
                                                       placeholder="ca-app-pub-xxxxxxxxxxxxxxxx/yyyyyyyyyy">
                                                <div class="form-text">
                                                    Tam sayfa reklamları için interstitial reklam birimi ID
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="admob_rewarded" class="form-label fw-bold">
                                                    <i class="fas fa-gift me-2 text-info"></i>
                                                    Ödüllü Reklam Birimi ID
                                                </label>
                                                <input type="text" class="form-control" id="admob_rewarded" name="admob_rewarded" 
                                                       value="<?php echo htmlspecialchars($currentSettings['admob_rewarded'] ?? ''); ?>" 
                                                       placeholder="ca-app-pub-xxxxxxxxxxxxxxxx/yyyyyyyyyy">
                                                <div class="form-text">
                                                    Ödül tabanlı reklamlar için ödüllü reklam birimi ID
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>
                                                Ayarları Kaydet
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

                    <!-- Test Ad IDs Info -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-info-circle me-2 text-info"></i>
                                        Test Reklam Birimi ID'leri
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">
                                        Geliştirme ve test için bu test reklam birimi ID'lerini kullanın:
                                    </p>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Reklam Tipi</th>
                                                    <th>Test Reklam Birimi ID</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Uygulama ID</strong></td>
                                                    <td><code>ca-app-pub-3940256099942544~3347511713</code></td>
                                                    <td>Android için test uygulama ID</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Banner</strong></td>
                                                    <td><code>ca-app-pub-3940256099942544/6300978111</code></td>
                                                    <td>Test banner reklamı</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tam Sayfa</strong></td>
                                                    <td><code>ca-app-pub-3940256099942544/1033173712</code></td>
                                                    <td>Test interstitial reklamı</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Ödüllü</strong></td>
                                                    <td><code>ca-app-pub-3940256099942544/5224354917</code></td>
                                                    <td>Test ödüllü reklamı</td>
                                                </tr>
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