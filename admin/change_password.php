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

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } else {
        try {
            $pdo = getDB();
            
            // Get current user
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE id = ? AND is_active = 1");
            $stmt->execute([$_SESSION['admin_id']]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($current_password, $user['password_hash'])) {
                // Update password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$new_password_hash, $_SESSION['admin_id']]);
                
                $success = 'Password changed successfully!';
                
                // Log the password change
                error_log("Admin password changed for user: " . $user['username']);
                
            } else {
                $error = 'Current password is incorrect.';
            }
            
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - SnapTikPro Admin Panel</title>
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
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="admob.php" class="nav-link">
                    <i class="fas fa-ad nav-icon"></i>
                    AdMob Settings
                </a>
            </li>
            <li class="nav-item">
                <a href="push.php" class="nav-link">
                    <i class="fas fa-bell nav-icon"></i>
                    Push Notifications
                </a>
            </li>
            <li class="nav-item">
                <a href="stats.php" class="nav-link">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    Statistics
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <i class="fas fa-users nav-icon"></i>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a href="change_password.php" class="nav-link active">
                    <i class="fas fa-key nav-icon"></i>
                    Change Password
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="fade-in">
                <h1>Change Password</h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Update your admin account password securely.
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

            <div class="form-container fade-in">
                <form method="POST" action="" id="passwordForm">
                    <div class="form-group">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Current Password
                        </label>
                        <input type="password" id="current_password" name="current_password" class="form-input" 
                               placeholder="Enter your current password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-key"></i>
                            New Password
                        </label>
                        <input type="password" id="new_password" name="new_password" class="form-input" 
                               placeholder="Enter new password (min 6 characters)" required>
                        <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                            Password must be at least 6 characters long
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-check-circle"></i>
                            Confirm New Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                               placeholder="Confirm new password" required>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Change Password
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>

            <!-- Password Requirements -->
            <div class="card fade-in" style="margin-top: 2rem;">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i>
                    Password Security Tips
                </h3>
                <div style="margin-top: 1rem;">
                    <ul style="color: var(--text-secondary); line-height: 1.8;">
                        <li>✅ Use at least 6 characters</li>
                        <li>✅ Include uppercase and lowercase letters</li>
                        <li>✅ Add numbers and special characters</li>
                        <li>✅ Avoid common words and patterns</li>
                        <li>✅ Don't reuse passwords from other accounts</li>
                        <li>✅ Change your password regularly</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Password confirmation validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
        
        // Show password strength
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            // You can add visual feedback here
            if (password.length > 0) {
                console.log('Password strength:', strength);
            }
        });
        
        function calculatePasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            return strength;
        }
    </script>
</body>
</html>