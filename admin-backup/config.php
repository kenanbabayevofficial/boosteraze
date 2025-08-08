<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'db_kullanici_adi');
define('DB_PASS', 'db_sifresi');
define('DB_NAME', 'snaptikpro_admin');

// Firebase Configuration
define('FCM_SERVER_KEY', 'YOUR_FIREBASE_SERVER_KEY_HERE');

// App Configuration
define('APP_NAME', 'SnapTikPro Admin Panel');
define('APP_VERSION', '1.0');

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>