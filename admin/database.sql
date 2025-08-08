-- SnapTikPro Admin Panel Database
-- Created for AdMob management, user statistics, and push notifications

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS snaptikpro_admin;
USE snaptikpro_admin;

-- Settings table for AdMob configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table for device registration
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(255) NOT NULL UNIQUE,
    device_model VARCHAR(255),
    android_version VARCHAR(50),
    app_version VARCHAR(50),
    first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    total_downloads INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
);

-- Push tokens table for FCM notifications
CREATE TABLE IF NOT EXISTS push_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(255) NOT NULL,
    fcm_token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (device_id) REFERENCES users(device_id) ON DELETE CASCADE
);

-- Download history table
CREATE TABLE IF NOT EXISTS download_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(255) NOT NULL,
    video_url TEXT,
    video_title VARCHAR(500),
    download_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_size BIGINT,
    download_status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    FOREIGN KEY (device_id) REFERENCES users(device_id) ON DELETE CASCADE
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role ENUM('admin', 'moderator') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Push notification history
CREATE TABLE IF NOT EXISTS push_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    sent_by INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    target_devices INT DEFAULT 0,
    successful_sends INT DEFAULT 0,
    failed_sends INT DEFAULT 0,
    FOREIGN KEY (sent_by) REFERENCES admin_users(id)
);

-- Insert default admin user (username: admin, password: admin123)
-- Note: Run setup_admin.php to create admin user with correct password hash
-- INSERT INTO admin_users (username, password_hash, email, role) VALUES 
-- ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@snaptikpro.com', 'admin');

-- Insert default AdMob settings (test ads)
INSERT INTO settings (setting_key, setting_value) VALUES 
('admob_banner', 'ca-app-pub-3940256099942544/6300978111'),
('admob_interstitial', 'ca-app-pub-3940256099942544/1033173712'),
('admob_rewarded', 'ca-app-pub-3940256099942544/5224354917'),
('admob_app_id', 'ca-app-pub-3940256099942544~3347511713'),
('fcm_server_key', 'YOUR_FCM_SERVER_KEY_HERE'),
('app_version', '1.0'),
('app_name', 'Video Downloader Pro');

-- Create indexes for better performance
CREATE INDEX idx_users_device_id ON users(device_id);
CREATE INDEX idx_push_tokens_device_id ON push_tokens(device_id);
CREATE INDEX idx_download_history_device_id ON download_history(device_id);
CREATE INDEX idx_download_history_date ON download_history(download_date);
CREATE INDEX idx_settings_key ON settings(setting_key);

-- Create views for statistics
CREATE VIEW user_stats AS
SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as active_users_7d,
    COUNT(CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as active_users_30d,
    SUM(total_downloads) as total_downloads
FROM users;

CREATE VIEW daily_downloads AS
SELECT 
    DATE(download_date) as download_date,
    COUNT(*) as downloads_count,
    COUNT(DISTINCT device_id) as unique_users
FROM download_history 
WHERE download_status = 'success'
GROUP BY DATE(download_date)
ORDER BY download_date DESC;

-- Grant permissions (adjust as needed)
-- GRANT ALL PRIVILEGES ON snaptikpro_admin.* TO 'snaptikpro_user'@'localhost' IDENTIFIED BY 'your_password';
-- FLUSH PRIVILEGES;