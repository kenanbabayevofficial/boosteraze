<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

// Handle coupon actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $id = uniqid();
            $code = strtoupper($_POST['code']);
            $discount = $_POST['discount'];
            $type = $_POST['type'];
            $max_uses = $_POST['max_uses'];
            $expires_at = $_POST['expires_at'] ? $_POST['expires_at'] : null;
            
            $stmt = $pdo->prepare("INSERT INTO coupons (id, code, discount, type, max_uses, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $code, $discount, $type, $max_uses, $expires_at]);
            
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        header('Location: coupons.php');
        exit();
    }
}

// Get coupons
$stmt = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC");
$coupons = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuponlar - TRLike Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Kuponlar</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                        <i class="fas fa-plus"></i> Yeni Kupon Ekle
                    </button>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Kupon Listesi</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Kod</th>
                                        <th>İndirim</th>
                                        <th>Tip</th>
                                        <th>Kullanım</th>
                                        <th>Durum</th>
                                        <th>Son Kullanım</th>
                                        <th>Oluşturulma</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($coupons as $coupon): ?>
                                    <tr>
                                        <td><code><?php echo $coupon['code']; ?></code></td>
                                        <td>
                                            <?php if ($coupon['type'] === 'percentage'): ?>
                                                %<?php echo $coupon['discount']; ?>
                                            <?php else: ?>
                                                <?php echo $coupon['discount']; ?> Kredi
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($coupon['type'] === 'percentage'): ?>
                                                <span class="badge bg-info">Yüzde</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Sabit</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $coupon['used_count']; ?> / <?php echo $coupon['max_uses']; ?></td>
                                        <td>
                                            <?php 
                                            $isExpired = $coupon['expires_at'] && strtotime($coupon['expires_at']) < time();
                                            $isUsedUp = $coupon['used_count'] >= $coupon['max_uses'];
                                            
                                            if (!$coupon['is_active'] || $isExpired || $isUsedUp): ?>
                                                <span class="badge bg-danger">Pasif</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $coupon['expires_at'] ? date('d.m.Y H:i', strtotime($coupon['expires_at'])) : 'Süresiz'; ?>
                                        </td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($coupon['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCoupon('<?php echo $coupon['id']; ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Add Coupon Modal -->
    <div class="modal fade" id="addCouponModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Kupon Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="code" class="form-label">Kupon Kodu</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">İndirim Tipi</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="percentage">Yüzde İndirim</option>
                                <option value="fixed_amount">Sabit İndirim (Kredi)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="discount" class="form-label">İndirim Miktarı</label>
                            <input type="number" class="form-control" id="discount" name="discount" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="max_uses" class="form-label">Maksimum Kullanım</label>
                            <input type="number" class="form-control" id="max_uses" name="max_uses" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="expires_at" class="form-label">Son Kullanım Tarihi (Opsiyonel)</label>
                            <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script>
        function deleteCoupon(id) {
            if (confirm('Bu kuponu silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>