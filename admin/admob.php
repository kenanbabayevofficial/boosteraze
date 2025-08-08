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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDB();
        
        $settings = [
            'admob_banner' => $_POST['admob_banner'] ?? '',
            'admob_interstitial' => $_POST['admob_interstitial'] ?? '',
            'admob_rewarded' => $_POST['admob_rewarded'] ?? '',
            'admob_app_id' => $_POST['admob_app_id'] ?? ''
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        
        $success = 'AdMob settings updated successfully!';
        
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
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
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdMob Settings - SnapTikPro Admin Panel</title>
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
                <a href="admob.php" class="nav-link active">
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
                <a href="change_password.php" class="nav-link">
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
                <h1>AdMob Settings</h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Manage your AdMob ad unit IDs. These settings will be used by the Android app to display ads.
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
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="admob_app_id" class="form-label">
                            <i class="fas fa-mobile-alt"></i>
                            AdMob App ID
                        </label>
                        <input type="text" id="admob_app_id" name="admob_app_id" class="form-input" 
                               value="<?php echo htmlspecialchars($currentSettings['admob_app_id'] ?? ''); ?>" 
                               placeholder="ca-app-pub-xxxxxxxxxxxxxxxx~yyyyyyyyyy">
                        <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                            Your AdMob app ID (found in AdMob console)
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="admob_banner" class="form-label">
                            <i class="fas fa-rectangle-ad"></i>
                            Banner Ad Unit ID
                        </label>
                        <input type="text" id="admob_banner" name="admob_banner" class="form-input" 
                               value="<?php echo htmlspecialchars($currentSettings['admob_banner'] ?? ''); ?>" 
                               placeholder="ca-app-pub-xxxxxxxxxxxxxxxx/yyyyyyyyyy">
                        <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                            Banner ad unit ID for displaying banner ads
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="admob_interstitial" class="form-label">
                            <i class="fas fa-window-maximize"></i>
                            Interstitial Ad Unit ID
                        </label>
                        <input type="text" id="admob_interstitial" name="admob_interstitial" class="form-input" 
                               value="<?php echo htmlspecialchars($currentSettings['admob_interstitial'] ?? ''); ?>" 
                               placeholder="ca-app-pub-xxxxxxxxxxxxxxxx/yyyyyyyyyy">
                        <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                            Interstitial ad unit ID for full-screen ads
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="admob_rewarded" class="form-label">
                            <i class="fas fa-gift"></i>
                            Rewarded Ad Unit ID
                        </label>
                        <input type="text" id="admob_rewarded" name="admob_rewarded" class="form-input" 
                               value="<?php echo htmlspecialchars($currentSettings['admob_rewarded'] ?? ''); ?>" 
                               placeholder="ca-app-pub-xxxxxxxxxxxxxxxx/yyyyyyyyyy">
                        <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                            Rewarded ad unit ID for reward-based ads
                        </small>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Save Settings
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>

            <!-- Test Ad IDs Info -->
            <div class="card fade-in" style="margin-top: 2rem;">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Test Ad Unit IDs
                </h3>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    Use these test ad unit IDs for development and testing:
                </p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ad Type</th>
                                <th>Test Ad Unit ID</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>App ID</strong></td>
                                <td><code>ca-app-pub-3940256099942544~3347511713</code></td>
                                <td>Test app ID for Android</td>
                            </tr>
                            <tr>
                                <td><strong>Banner</strong></td>
                                <td><code>ca-app-pub-3940256099942544/6300978111</code></td>
                                <td>Test banner ad</td>
                            </tr>
                            <tr>
                                <td><strong>Interstitial</strong></td>
                                <td><code>ca-app-pub-3940256099942544/1033173712</code></td>
                                <td>Test interstitial ad</td>
                            </tr>
                            <tr>
                                <td><strong>Rewarded</strong></td>
                                <td><code>ca-app-pub-3940256099942544/5224354917</code></td>
                                <td>Test rewarded ad</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>