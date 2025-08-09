<?php
/**
 * Database Fix Script for SnapTikPro Admin Panel
 * This script fixes common database issues and updates table structures
 */

require_once 'config.php';
require_once 'db.php';

echo "<h2>SnapTikPro Admin Panel - Database Fix Script</h2>";

try {
    $pdo = getDB();
    
    echo "<h3>1. Checking and updating users table...</h3>";
    
    // Check if fcm_token column exists in users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'fcm_token'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN fcm_token TEXT NULL AFTER app_version");
        echo "✓ Added fcm_token column to users table<br>";
    } else {
        echo "✓ fcm_token column already exists<br>";
    }
    
    // Check if created_at column exists in users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER fcm_token");
        echo "✓ Added created_at column to users table<br>";
    } else {
        echo "✓ created_at column already exists<br>";
    }
    
    echo "<h3>2. Creating notifications table...</h3>";
    
    // Create notifications table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success_count INT DEFAULT 0,
            total_count INT DEFAULT 0,
            status ENUM('sent', 'failed', 'pending') DEFAULT 'pending'
        )
    ");
    echo "✓ Notifications table created/verified<br>";
    
    echo "<h3>3. Creating indexes...</h3>";
    
    // Create indexes for better performance
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_users_fcm_token ON users(fcm_token(255))",
        "CREATE INDEX IF NOT EXISTS idx_notifications_sent_at ON notifications(sent_at)",
        "CREATE INDEX IF NOT EXISTS idx_download_history_status ON download_history(download_status)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $pdo->exec($index);
            echo "✓ Index created successfully<br>";
        } catch (PDOException $e) {
            echo "ℹ Index might already exist: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h3>4. Updating settings...</h3>";
    
    // Insert or update default settings
    $settings = [
        'admob_banner' => 'ca-app-pub-3940256099942544/6300978111',
        'admob_interstitial' => 'ca-app-pub-3940256099942544/1033173712',
        'admob_rewarded' => 'ca-app-pub-3940256099942544/5224354917',
        'admob_app_id' => 'ca-app-pub-3940256099942544~3347511713',
        'fcm_server_key' => 'YOUR_FCM_SERVER_KEY_HERE',
        'app_version' => '1.0',
        'app_name' => 'Video Downloader Pro'
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
        echo "✓ Setting '$key' updated<br>";
    }
    
    echo "<h3>5. Creating views...</h3>";
    
    // Create or replace views
    $views = [
        "CREATE OR REPLACE VIEW user_stats AS
         SELECT 
             COUNT(*) as total_users,
             COUNT(CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as active_users_7d,
             COUNT(CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as active_users_30d,
             SUM(total_downloads) as total_downloads
         FROM users",
        
        "CREATE OR REPLACE VIEW daily_downloads AS
         SELECT 
             DATE(download_date) as download_date,
             COUNT(*) as downloads_count,
             COUNT(DISTINCT device_id) as unique_users
         FROM download_history 
         WHERE download_status = 'success'
         GROUP BY DATE(download_date)
         ORDER BY download_date DESC"
    ];
    
    foreach ($views as $view) {
        $pdo->exec($view);
        echo "✓ View created/updated<br>";
    }
    
    echo "<h3>6. Database statistics...</h3>";
    
    // Show database statistics
    $stats = [
        'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'downloads' => $pdo->query("SELECT COUNT(*) FROM download_history")->fetchColumn(),
        'notifications' => $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn(),
        'settings' => $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn()
    ];
    
    echo "✓ Total users: " . $stats['users'] . "<br>";
    echo "✓ Total downloads: " . $stats['downloads'] . "<br>";
    echo "✓ Total notifications: " . $stats['notifications'] . "<br>";
    echo "✓ Total settings: " . $stats['settings'] . "<br>";
    
    echo "<h3>✅ Database fix completed successfully!</h3>";
    echo "<p>All tables and indexes have been updated. The admin panel should now work correctly.</p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Error occurred:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; }
h3 { color: #666; margin-top: 20px; }
p { color: #888; }
</style>