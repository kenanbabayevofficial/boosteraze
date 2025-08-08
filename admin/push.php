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

// Handle push notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    
    if (empty($title) || empty($body)) {
        $error = 'Please fill in both title and message.';
    } else {
        try {
            $pdo = getDB();
            
            // Get all active push tokens
            $stmt = $pdo->query("SELECT fcm_token FROM push_tokens WHERE is_active = 1");
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tokens)) {
                $error = 'No active push tokens found.';
            } else {
                // Include FCM send function
                require_once 'fcm_send.php';
                $result = sendPush($tokens, $title, $body);
                
                if ($result) {
                    $success = "Push notification sent successfully to " . count($tokens) . " devices!";
                    
                    // Log the notification
                    $stmt = $pdo->prepare("INSERT INTO push_history (title, message, sent_by, target_devices, successful_sends) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $body, $_SESSION['admin_id'], count($tokens), count($tokens)]);
                } else {
                    $error = 'Failed to send push notification.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get notification statistics
try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) as total_tokens FROM push_tokens WHERE is_active = 1");
    $totalTokens = $stmt->fetch()['total_tokens'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_notifications FROM push_history");
    $totalNotifications = $stmt->fetch()['total_notifications'];
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Push Notifications - SnapTikPro Admin Panel</title>
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
                <a href="push.php" class="nav-link active">
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
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="fade-in">
                <h1>Push Notifications</h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Send push notifications to all registered users.
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

            <!-- Statistics Cards -->
            <div class="dashboard-grid fade-in">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Active Tokens</h3>
                        <div class="card-icon notifications">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($totalTokens); ?></div>
                    <div class="card-label">Registered devices</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Sent</h3>
                        <div class="card-icon notifications">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($totalNotifications); ?></div>
                    <div class="card-label">Notifications sent</div>
                </div>
            </div>

            <!-- Send Notification Form -->
            <div class="form-container fade-in">
                <h3 class="card-title">
                    <i class="fas fa-bell"></i>
                    Send Push Notification
                </h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading"></i>
                            Notification Title
                        </label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                               placeholder="Enter notification title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="body" class="form-label">
                            <i class="fas fa-comment"></i>
                            Notification Message
                        </label>
                        <textarea id="body" name="body" class="form-textarea" 
                                  placeholder="Enter notification message" required><?php echo htmlspecialchars($_POST['body'] ?? ''); ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Send Notification
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>

            <!-- Notification History -->
            <div class="card fade-in" style="margin-top: 2rem;">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Recent Notifications
                </h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Sent Date</th>
                                <th>Target Devices</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT title, message, sent_at, target_devices 
                                    FROM push_history 
                                    ORDER BY sent_at DESC 
                                    LIMIT 10
                                ");
                                while ($row = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                    echo "<td>" . htmlspecialchars(substr($row['message'], 0, 50)) . "...</td>";
                                    echo "<td>" . htmlspecialchars($row['sent_at']) . "</td>";
                                    echo "<td><span class='badge badge-info'>" . number_format($row['target_devices']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='4'>No notification history available</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>