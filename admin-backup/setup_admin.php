<?php
require_once 'config.php';
require_once 'db.php';

echo "<h2>SnapTikPro Admin Setup</h2>";

try {
    $pdo = getDB();
    
    // Create admin user with correct password hash
    $username = 'admin';
    $password = 'admin123';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $email = 'admin@snaptikpro.com';
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $existing_user = $stmt->fetch();
    
    if ($existing_user) {
        // Update existing admin user
        $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, email = ?, is_active = 1 WHERE username = ?");
        $stmt->execute([$password_hash, $email, $username]);
        echo "<p style='color: green;'>✅ Admin user updated successfully!</p>";
    } else {
        // Create new admin user
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash, email, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$username, $password_hash, $email]);
        echo "<p style='color: green;'>✅ Admin user created successfully!</p>";
    }
    
    echo "<h3>Login Credentials:</h3>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>Password Hash:</strong> " . $password_hash . "</p>";
    
    // Test login
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        echo "<p style='color: green;'>✅ Login test successful!</p>";
    } else {
        echo "<p style='color: red;'>❌ Login test failed!</p>";
    }
    
    echo "<br><a href='login.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?>