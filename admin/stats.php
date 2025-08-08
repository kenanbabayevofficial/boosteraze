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

try {
    $pdo = getDB();
    
    // Get overall statistics
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $stmt->fetch()['total'];
    
    // Active users (last 7 days)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['active_users_7d'] = $stmt->fetch()['total'];
    
    // Active users (last 30 days)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['active_users_30d'] = $stmt->fetch()['total'];
    
    // Total downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success'");
    $stats['total_downloads'] = $stmt->fetch()['total'];
    
    // Today's downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success' AND DATE(download_date) = CURDATE()");
    $stats['today_downloads'] = $stmt->fetch()['total'];
    
    // Yesterday's downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success' AND DATE(download_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
    $stats['yesterday_downloads'] = $stmt->fetch()['total'];
    
    // This week's downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success' AND YEARWEEK(download_date) = YEARWEEK(NOW())");
    $stats['week_downloads'] = $stmt->fetch()['total'];
    
    // This month's downloads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM download_history WHERE download_status = 'success' AND MONTH(download_date) = MONTH(NOW()) AND YEAR(download_date) = YEAR(NOW())");
    $stats['month_downloads'] = $stmt->fetch()['total'];
    
    // Daily downloads for last 7 days
    $stmt = $pdo->query("
        SELECT DATE(download_date) as date, COUNT(*) as count 
        FROM download_history 
        WHERE download_status = 'success' 
        AND download_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(download_date) 
        ORDER BY date
    ");
    $daily_downloads = $stmt->fetchAll();
    
    // Top users by downloads
    $stmt = $pdo->query("
        SELECT u.device_id, COUNT(dh.id) as download_count, u.last_seen
        FROM users u
        LEFT JOIN download_history dh ON u.device_id = dh.device_id AND dh.download_status = 'success'
        GROUP BY u.device_id
        ORDER BY download_count DESC
        LIMIT 10
    ");
    $top_users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - SnapTikPro Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="change_password.php" class="btn btn-primary" style="margin-right: 0.5rem;">
                    <i class="fas fa-key"></i>
                    Change Password
                </a>
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
                <a href="stats.php" class="nav-link active">
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
                <h1>Statistics & Analytics</h1>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Detailed analytics and insights about your app usage.
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error fade-in">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Overview Cards -->
            <div class="dashboard-grid fade-in">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Users</h3>
                        <div class="card-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="card-label">Registered devices</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Active Users (7d)</h3>
                        <div class="card-icon users">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($stats['active_users_7d']); ?></div>
                    <div class="card-label">Last 7 days</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Downloads</h3>
                        <div class="card-icon downloads">
                            <i class="fas fa-download"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($stats['total_downloads']); ?></div>
                    <div class="card-label">All time</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Today's Downloads</h3>
                        <div class="card-icon downloads">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($stats['today_downloads']); ?></div>
                    <div class="card-label">Downloads today</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="card fade-in">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Daily Downloads (Last 7 Days)
                </h3>
                <canvas id="downloadsChart" width="400" height="200"></canvas>
            </div>

            <!-- Detailed Stats -->
            <div class="dashboard-grid fade-in">
                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-week"></i>
                        Weekly Statistics
                    </h3>
                    <div style="margin-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>This Week:</span>
                            <strong><?php echo number_format($stats['week_downloads']); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Yesterday:</span>
                            <strong><?php echo number_format($stats['yesterday_downloads']); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Active Users (30d):</span>
                            <strong><?php echo number_format($stats['active_users_30d']); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt"></i>
                        Monthly Statistics
                    </h3>
                    <div style="margin-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>This Month:</span>
                            <strong><?php echo number_format($stats['month_downloads']); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Avg Daily:</span>
                            <strong><?php echo number_format(round($stats['month_downloads'] / date('d'))); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Total Users:</span>
                            <strong><?php echo number_format($stats['total_users']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Users -->
            <div class="card fade-in">
                <h3 class="card-title">
                    <i class="fas fa-trophy"></i>
                    Top Users by Downloads
                </h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Device ID</th>
                                <th>Downloads</th>
                                <th>Last Seen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($top_users)): ?>
                                <?php foreach ($top_users as $index => $user): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index < 3): ?>
                                                <span style="color: #FFD700; font-weight: bold;">🥇</span>
                                            <?php else: ?>
                                                <span class="badge badge-info"><?php echo $index + 1; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars(substr($user['device_id'], 0, 20)); ?>...</code>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">
                                                <?php echo number_format($user['download_count']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['last_seen']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-secondary);">
                                        No data available
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Chart.js configuration
        const ctx = document.getElementById('downloadsChart').getContext('2d');
        
        const chartData = {
            labels: <?php echo json_encode(array_column($daily_downloads, 'date')); ?>,
            datasets: [{
                label: 'Downloads',
                data: <?php echo json_encode(array_column($daily_downloads, 'count')); ?>,
                backgroundColor: 'rgba(33, 150, 243, 0.2)',
                borderColor: 'rgba(33, 150, 243, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#FFFFFF'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#FFFFFF'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#FFFFFF'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>