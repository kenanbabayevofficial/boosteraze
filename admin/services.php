<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

// Handle service actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $id = uniqid();
            $name = $_POST['name'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $platform = $_POST['platform'];
            $price = $_POST['price'];
            $delivery_time = $_POST['delivery_time'];
            
            $stmt = $pdo->prepare("INSERT INTO services (id, name, description, category, platform, price, delivery_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $name, $description, $category, $platform, $price, $delivery_time]);
            
        } elseif ($action === 'edit') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $platform = $_POST['platform'];
            $price = $_POST['price'];
            $delivery_time = $_POST['delivery_time'];
            
            $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, category = ?, platform = ?, price = ?, delivery_time = ? WHERE id = ?");
            $stmt->execute([$name, $description, $category, $platform, $price, $delivery_time, $id]);
            
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        header('Location: services.php');
        exit();
    }
}

// Get services
$stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hizmetler - TRLike Admin Panel</title>
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
                    <h1 class="h2">Hizmetler</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus"></i> Yeni Hizmet Ekle
                    </button>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Hizmet Listesi</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ad</th>
                                        <th>Kategori</th>
                                        <th>Platform</th>
                                        <th>Fiyat (Kredi)</th>
                                        <th>Teslimat Süresi</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td><?php echo $service['id']; ?></td>
                                        <td><?php echo $service['name']; ?></td>
                                        <td><?php echo $service['category']; ?></td>
                                        <td><?php echo $service['platform']; ?></td>
                                        <td><?php echo $service['price']; ?></td>
                                        <td><?php echo $service['delivery_time']; ?></td>
                                        <td>
                                            <?php if ($service['is_active']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="editService('<?php echo $service['id']; ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteService('<?php echo $service['id']; ?>')">
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
    
    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Hizmet Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Hizmet Adı</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Kategori</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="followers">Takipçi</option>
                                <option value="turkish_followers">Türk Takipçi</option>
                                <option value="likes">Beğeni</option>
                                <option value="comments">Yorum</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="platform" class="form-label">Platform</label>
                            <select class="form-control" id="platform" name="platform" required>
                                <option value="instagram">Instagram</option>
                                <option value="tiktok">TikTok</option>
                                <option value="youtube">YouTube</option>
                                <option value="twitter">Twitter</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Fiyat (Kredi)</label>
                            <input type="number" class="form-control" id="price" name="price" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="delivery_time" class="form-label">Teslimat Süresi</label>
                            <input type="text" class="form-control" id="delivery_time" name="delivery_time" placeholder="20-40 dakika" required>
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
        function editService(id) {
            // Implement edit functionality
            alert('Edit service: ' + id);
        }
        
        function deleteService(id) {
            if (confirm('Bu hizmeti silmek istediğinizden emin misiniz?')) {
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