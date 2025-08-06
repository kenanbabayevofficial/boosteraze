<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../admin/config/database.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));
$endpoint = $path_parts[count($path_parts) - 1];

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request body
$input = json_decode(file_get_contents('php://input'), true);

// Get authorization header
$headers = getallheaders();
$auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Extract token from Authorization header
$token = null;
if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
    $token = $matches[1];
}

// Route requests
try {
    switch ($endpoint) {
        case 'auth':
            if ($method === 'POST') {
                handleGoogleSignIn($input);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'user':
            if ($method === 'GET') {
                handleGetUserProfile($token);
            } elseif ($method === 'PUT') {
                handleUpdateUserCredits($token, $input);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'services':
            if ($method === 'GET') {
                $category = isset($_GET['category']) ? $_GET['category'] : null;
                handleGetServices($category);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'orders':
            if ($method === 'GET') {
                $order_id = isset($_GET['id']) ? $_GET['id'] : null;
                if ($order_id) {
                    handleGetOrder($token, $order_id);
                } else {
                    handleGetUserOrders($token);
                }
            } elseif ($method === 'POST') {
                handleCreateOrder($token, $input);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'credit-packages':
            if ($method === 'GET') {
                handleGetCreditPackages();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'coupons':
            if ($method === 'POST') {
                $action = isset($_GET['action']) ? $_GET['action'] : '';
                if ($action === 'validate') {
                    handleValidateCoupon($input);
                } elseif ($action === 'apply') {
                    handleApplyCoupon($token, $input);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
                }
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

// Authentication functions
function handleGoogleSignIn($input) {
    global $pdo;
    
    if (!isset($input['idToken']) || !isset($input['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    // Verify Google ID token (in production, you should verify with Google)
    $id_token = $input['idToken'];
    $email = $input['email'];
    $name = $input['name'] ?? null;
    $photo_url = $input['photoUrl'] ?? null;
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Create new user
        $user_id = uniqid();
        $stmt = $pdo->prepare("INSERT INTO users (id, email, name, photo_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $email, $name, $photo_url]);
        
        $user = [
            'id' => $user_id,
            'email' => $email,
            'name' => $name,
            'photo_url' => $photo_url,
            'credits' => 0,
            'is_banned' => false
        ];
    }
    
    // Check if user is banned
    if ($user['is_banned']) {
        http_response_code(403);
        echo json_encode(['error' => 'Account is banned']);
        return;
    }
    
    // Generate JWT token (in production, use a proper JWT library)
    $token = generateJWTToken($user['id']);
    
    echo json_encode([
        'user' => $user,
        'token' => $token
    ]);
}

function handleGetUserProfile($token) {
    global $pdo;
    
    $user_id = validateToken($token);
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        return;
    }
    
    echo json_encode($user);
}

function handleUpdateUserCredits($token, $input) {
    global $pdo;
    
    $user_id = validateToken($token);
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    if (!isset($input['credits'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing credits field']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE users SET credits = ? WHERE id = ?");
    $stmt->execute([$input['credits'], $user_id]);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    echo json_encode($user);
}

// Service functions
function handleGetServices($category = null) {
    global $pdo;
    
    if ($category) {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE category = ? AND is_active = 1");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1");
    }
    
    $services = $stmt->fetchAll();
    echo json_encode($services);
}

// Order functions
function handleCreateOrder($token, $input) {
    global $pdo;
    
    $user_id = validateToken($token);
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    if (!isset($input['serviceId']) || !isset($input['username']) || !isset($input['quantity'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    // Get service details
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$input['serviceId']]);
    $service = $stmt->fetch();
    
    if (!$service) {
        http_response_code(404);
        echo json_encode(['error' => 'Service not found']);
        return;
    }
    
    // Check user credits
    $stmt = $pdo->prepare("SELECT credits FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    $total_cost = $service['price'] * $input['quantity'];
    
    if ($user['credits'] < $total_cost) {
        http_response_code(400);
        echo json_encode(['error' => 'Insufficient credits']);
        return;
    }
    
    // Create order
    $order_id = uniqid();
    $stmt = $pdo->prepare("INSERT INTO orders (id, user_id, service_id, username, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$order_id, $user_id, $input['serviceId'], $input['username'], $input['quantity'], $total_cost]);
    
    // Deduct credits
    $stmt = $pdo->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
    $stmt->execute([$total_cost, $user_id]);
    
    // Get created order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    echo json_encode($order);
}

function handleGetUserOrders($token) {
    global $pdo;
    
    $user_id = validateToken($token);
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
    
    echo json_encode($orders);
}

function handleGetOrder($token, $order_id) {
    global $pdo;
    
    $user_id = validateToken($token);
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        return;
    }
    
    echo json_encode($order);
}

// Credit package functions
function handleGetCreditPackages() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM credit_packages WHERE is_active = 1");
    $packages = $stmt->fetchAll();
    
    echo json_encode($packages);
}

// Coupon functions
function handleValidateCoupon($input) {
    global $pdo;
    
    if (!isset($input['code'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing coupon code']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmt->execute([strtoupper($input['code'])]);
    $coupon = $stmt->fetch();
    
    if (!$coupon) {
        echo json_encode([
            'isValid' => false,
            'coupon' => null,
            'message' => 'Invalid coupon code'
        ]);
        return;
    }
    
    // Check if coupon is expired
    if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
        echo json_encode([
            'isValid' => false,
            'coupon' => null,
            'message' => 'Coupon has expired'
        ]);
        return;
    }
    
    // Check if coupon usage limit reached
    if ($coupon['used_count'] >= $coupon['max_uses']) {
        echo json_encode([
            'isValid' => false,
            'coupon' => null,
            'message' => 'Coupon usage limit reached'
        ]);
        return;
    }
    
    echo json_encode([
        'isValid' => true,
        'coupon' => $coupon,
        'message' => 'Coupon is valid'
    ]);
}

function handleApplyCoupon($token, $input) {
    global $pdo;
    
    $user_id = validateToken($token);
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    if (!isset($input['code']) || !isset($input['orderId'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    // Validate coupon
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmt->execute([strtoupper($input['code'])]);
    $coupon = $stmt->fetch();
    
    if (!$coupon) {
        echo json_encode([
            'success' => false,
            'discount' => 0,
            'message' => 'Invalid coupon code'
        ]);
        return;
    }
    
    // Check if coupon is expired
    if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
        echo json_encode([
            'success' => false,
            'discount' => 0,
            'message' => 'Coupon has expired'
        ]);
        return;
    }
    
    // Check if coupon usage limit reached
    if ($coupon['used_count'] >= $coupon['max_uses']) {
        echo json_encode([
            'success' => false,
            'discount' => 0,
            'message' => 'Coupon usage limit reached'
        ]);
        return;
    }
    
    // Get order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['orderId'], $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode([
            'success' => false,
            'discount' => 0,
            'message' => 'Order not found'
        ]);
        return;
    }
    
    // Calculate discount
    $discount = 0;
    if ($coupon['type'] === 'percentage') {
        $discount = ($order['price'] * $coupon['discount']) / 100;
    } else {
        $discount = $coupon['discount'];
    }
    
    // Update coupon usage
    $stmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
    $stmt->execute([$coupon['id']]);
    
    echo json_encode([
        'success' => true,
        'discount' => $discount,
        'message' => 'Coupon applied successfully'
    ]);
}

// Helper functions
function generateJWTToken($user_id) {
    // In production, use a proper JWT library
    $payload = [
        'user_id' => $user_id,
        'exp' => time() + (60 * 60 * 24 * 7) // 7 days
    ];
    
    return base64_encode(json_encode($payload));
}

function validateToken($token) {
    if (!$token) {
        return false;
    }
    
    try {
        $payload = json_decode(base64_decode($token), true);
        
        if (!$payload || !isset($payload['user_id']) || !isset($payload['exp'])) {
            return false;
        }
        
        if ($payload['exp'] < time()) {
            return false;
        }
        
        return $payload['user_id'];
    } catch (Exception $e) {
        return false;
    }
}
?>