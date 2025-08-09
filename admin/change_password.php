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
    // Sanitize and validate input (PHP 8.1+ compatible)
    $currentPassword = trim(htmlspecialchars($_POST['current_password'] ?? '', ENT_QUOTES, 'UTF-8'));
    $newPassword = trim(htmlspecialchars($_POST['new_password'] ?? '', ENT_QUOTES, 'UTF-8'));
    $confirmPassword = trim(htmlspecialchars($_POST['confirm_password'] ?? '', ENT_QUOTES, 'UTF-8'));
    
    // Validate input
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Lütfen tüm alanları doldurunuz.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Yeni şifre en az 8 karakter olmalıdır.';
    } elseif (strlen($newPassword) > 255) {
        $error = 'Yeni şifre çok uzun.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Yeni şifreler eşleşmiyor.';
    } elseif ($currentPassword === $newPassword) {
        $error = 'Yeni şifre mevcut şifre ile aynı olamaz.';
    } else {
        try {
            $pdo = getDB();
            
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$_SESSION['admin_user_id']]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($currentPassword, $user['password_hash'])) {
                // Hash new password
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password using prepared statement
                $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$newPasswordHash, $_SESSION['admin_user_id']]);
                
                $success = 'Şifreniz başarıyla güncellendi.';
                
                // Clear form data
                $currentPassword = $newPassword = $confirmPassword = '';
                
            } else {
                $error = 'Mevcut şifre yanlış.';
            }
            
        } catch (PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir - SnapTikPro Admin Paneli</title>
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
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #198754; }
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
                        
                        <span class="navbar-brand mb-0 h1">Şifre Değiştir</span>
                        
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
                    <div class="row justify-content-center">
                        <div class="col-lg-6 col-md-8">
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

                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-key me-2 text-primary"></i>
                                        Şifre Değiştir
                                    </h5>
                                    <p class="text-muted mb-0 mt-2">
                                        Admin paneli şifrenizi güvenli bir şekilde değiştirin.
                                    </p>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="" id="passwordForm">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label fw-bold">
                                                <i class="fas fa-lock me-2 text-secondary"></i>
                                                Mevcut Şifre
                                            </label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" 
                                                   placeholder="Mevcut şifrenizi girin" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label fw-bold">
                                                <i class="fas fa-key me-2 text-success"></i>
                                                Yeni Şifre
                                            </label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                                   placeholder="Yeni şifrenizi girin" required>
                                            <div class="password-strength" id="passwordStrength"></div>
                                            <div class="form-text">
                                                Şifre en az 6 karakter olmalıdır.
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="confirm_password" class="form-label fw-bold">
                                                <i class="fas fa-check me-2 text-info"></i>
                                                Yeni Şifre (Tekrar)
                                            </label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   placeholder="Yeni şifrenizi tekrar girin" required>
                                            <div class="form-text" id="passwordMatch"></div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>
                                                Şifreyi Güncelle
                                            </button>
                                            <a href="index.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left me-2"></i>
                                                Ana Sayfaya Dön
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Security Tips -->
                            <div class="card mt-4">
                                <div class="card-header bg-white">
                                    <h6 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-shield-alt me-2 text-warning"></i>
                                        Güvenlik İpuçları
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            En az 8 karakter kullanın
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Büyük ve küçük harfler kullanın
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Sayılar ve özel karakterler ekleyin
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Kişisel bilgilerinizi kullanmayın
                                        </li>
                                    </ul>
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
        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 3) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });
        
        // Password confirmation checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchText.textContent = '';
                matchText.className = 'form-text';
            } else if (newPassword === confirmPassword) {
                matchText.textContent = 'Şifreler eşleşiyor ✓';
                matchText.className = 'form-text text-success';
            } else {
                matchText.textContent = 'Şifreler eşleşmiyor ✗';
                matchText.className = 'form-text text-danger';
            }
        });
    </script>
</body>
</html>