<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Xử lý thêm kênh radio mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $stream_url = sanitize($_POST['stream_url']);
    $category_id = (int)$_POST['category_id'];
    
    // Upload logo image
    $logo_image = '';
    if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['logo_image']['name']);
        $targetPath = COVER_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetPath)) {
            $logo_image = $fileName;
        }
    }
    
    // Thêm vào database
    $data = [
        'name' => $name,
        'description' => $description,
        'stream_url' => $stream_url,
        'category_id' => $category_id,
        'logo_image' => $logo_image
    ];
    
    $db->insert('radio_stations', $data);
    redirect(ADMIN_URL . 'radio.php?success=1');
}

// Xử lý cập nhật kênh radio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $stream_url = sanitize($_POST['stream_url']);
    $category_id = (int)$_POST['category_id'];
    
    // Lấy thông tin hiện tại
    $currentStation = $db->selectOne("SELECT * FROM radio_stations WHERE id = ?", [$id]);
    
    // Upload logo image nếu có
    $logo_image = $currentStation['logo_image'];
    if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['logo_image']['name']);
        $targetPath = COVER_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentStation['logo_image'])) {
                $oldFile = COVER_UPLOAD_PATH . $currentStation['logo_image'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $logo_image = $fileName;
        }
    }
    
    // Cập nhật vào database
    $data = [
        'name' => $name,
        'description' => $description,
        'stream_url' => $stream_url,
        'category_id' => $category_id,
        'logo_image' => $logo_image
    ];
    
    $db->update('radio_stations', $data, 'id = ?', [$id]);
    redirect(ADMIN_URL . 'radio.php?updated=1');
}

// Xử lý xóa kênh radio
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Lấy thông tin trước khi xóa
    $station = $db->selectOne("SELECT * FROM radio_stations WHERE id = ?", [$id]);
    
    if ($station) {
        // Xóa logo nếu có
        if (!empty($station['logo_image'])) {
            $file = COVER_UPLOAD_PATH . $station['logo_image'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Xóa từ database
        $db->delete('radio_stations', 'id = ?', [$id]);
    }
    
    redirect(ADMIN_URL . 'radio.php?deleted=1');
}

// Lấy danh sách danh mục
$categories = $db->select("SELECT * FROM categories WHERE type = 'radio' ORDER BY name ASC");

// Lấy danh sách kênh radio hoặc chi tiết một kênh
$editStation = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editStation = $db->selectOne("SELECT * FROM radio_stations WHERE id = ?", [(int)$_GET['edit']]);
}

$stations = $db->select("SELECT rs.*, c.name as category_name 
                          FROM radio_stations rs 
                          LEFT JOIN categories c ON rs.category_id = c.id 
                          ORDER BY rs.name ASC");

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $editStation ? 'Chỉnh sửa kênh radio' : 'Quản lý kênh radio' ?></h2>
                <?php if (!$editStation): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addRadioModal">
                    <i class="fas fa-plus"></i> Thêm kênh radio mới
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Thêm kênh radio mới thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Cập nhật kênh radio thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Xóa kênh radio thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if ($editStation): ?>
            <!-- Form chỉnh sửa kênh radio -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editStation['id'] ?>">
                        
                        <div class="form-group">
                            <label>Tên kênh radio</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($editStation['name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Danh mục</label>
                            <select class="form-control" name="category_id">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $editStation['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>URL stream</label>
                            <input type="url" class="form-control" name="stream_url" value="<?= htmlspecialchars($editStation['stream_url']) ?>" required>
                            <small class="form-text text-muted">Nhập URL stream của kênh radio (ví dụ: http://example.com/stream.mp3)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($editStation['description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Logo</label>
                            <?php if (!empty($editStation['logo_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $editStation['logo_image'] ?>" style="max-width: 200px; max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="logo_image" accept="image/*">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi logo.</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            <a href="<?= ADMIN_URL ?>radio.php" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Danh sách kênh radio -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Tên kênh</th>
                            <th>URL stream</th>
                            <th>Danh mục</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stations)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Không có kênh radio nào.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($stations as $station): ?>
                        <tr>
                            <td><?= $station['id'] ?></td>
                            <td>
                                <?php if (!empty($station['logo_image'])): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $station['logo_image'] ?>" 
                                     alt="<?= htmlspecialchars($station['name']) ?>" 
                                     style="max-width: 50px; max-height: 50px;">
                                <?php else: ?>
                                <span class="badge badge-secondary">Không có logo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($station['name']) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($station['stream_url']) ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                                    <?= htmlspecialchars($station['stream_url']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($station['category_name'] ?? 'Không có danh mục') ?></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>radio.php?edit=<?= $station['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="<?= ADMIN_URL ?>radio.php?action=delete&id=<?= $station['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa kênh radio này?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal thêm kênh radio mới -->
<div class="modal fade" id="addRadioModal" tabindex="-1" role="dialog" aria-labelledby="addRadioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRadioModalLabel">Thêm kênh radio mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Tên kênh radio</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Danh mục</label>
                        <select class="form-control" name="category_id">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>URL stream</label>
                        <input type="url" class="form-control" name="stream_url" required>
                        <small class="form-text text-muted">Nhập URL stream của kênh radio (ví dụ: http://example.com/stream.mp3)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea class="form-control" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Logo</label>
                        <input type="file" class="form-control-file" name="logo_image" accept="image/*">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
