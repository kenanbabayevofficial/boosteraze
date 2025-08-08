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
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapTikPro Admin Panel</title>
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
                <a href="index.php" class="nav-link active">
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

        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <h1 class="fade-in">Dashboard</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Cards -->
            <div class="dashboard-grid fade-in">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Users</h3>
                        <div class="card-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($totalUsers); ?></div>
                    <div class="card-label">Registered devices</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Active Users</h3>
                        <div class="card-icon users">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($activeUsers); ?></div>
                    <div class="card-label">Last 7 days</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Downloads</h3>
                        <div class="card-icon downloads">
                            <i class="fas fa-download"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($totalDownloads); ?></div>
                    <div class="card-label">Successful downloads</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Today's Downloads</h3>
                        <div class="card-icon downloads">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($todayDownloads); ?></div>
                    <div class="card-label">Downloads today</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card fade-in">
                <h3 class="card-title">Quick Actions</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <a href="admob.php" class="btn btn-primary">
                        <i class="fas fa-ad"></i>
                        Manage AdMob
                    </a>
                    <a href="push.php" class="btn btn-success">
                        <i class="fas fa-bell"></i>
                        Send Notification
                    </a>
                    <a href="stats.php" class="btn btn-warning">
                        <i class="fas fa-chart-line"></i>
                        View Analytics
                    </a>
                    <a href="users.php" class="btn btn-info">
                        <i class="fas fa-users"></i>
                        Manage Users
                    </a>
                </div>
            </div>



            <!-- Recent Activity -->
            <div class="card fade-in">
                <h3 class="card-title">Recent Activity</h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Device ID</th>
                                <th>Action</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT device_id, 'Download' as action, download_date as date, download_status as status
                                    FROM download_history 
                                    ORDER BY download_date DESC 
                                    LIMIT 10
                                ");
                                while ($row = $stmt->fetch()) {
                                    $statusClass = $row['status'] === 'success' ? 'badge-success' : 'badge-error';
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars(substr($row['device_id'], 0, 20)) . "...</td>";
                                    echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                    echo "<td><span class='badge $statusClass'>" . htmlspecialchars($row['status']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='4'>No recent activity</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.fade-in');
            cards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
            });
        });
    </script>
</body>
</html>