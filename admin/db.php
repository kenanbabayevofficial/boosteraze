<?php
require_once 'config.php';

function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// For backward compatibility
try {
    $pdo = getDB();
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>