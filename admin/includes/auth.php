<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_admin = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = true;
        return true;
    }
    
    return false;
}

function logout() {
    session_destroy();
}

function getDashboardStats() {
    global $pdo;
    
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $stmt->fetch()['count'];
    
    // Total services
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services");
    $stats['total_services'] = $stmt->fetch()['count'];
    
    // Pending orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $stmt->fetch()['count'];
    
    return $stats;
}

function getRecentOrders($limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT o.*, u.email as user_email, s.name as service_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN services s ON o.service_id = s.id 
        ORDER BY o.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'secondary';
        case 'failed':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'pending':
            return 'Bekliyor';
        case 'processing':
            return 'İşleniyor';
        case 'completed':
            return 'Tamamlandı';
        case 'cancelled':
            return 'İptal Edildi';
        case 'failed':
            return 'Başarısız';
        default:
            return 'Bilinmiyor';
    }
}
?>