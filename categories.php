<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Thêm danh mục mặc định nếu cần
$defaultCategories = [
    ['name' => 'Sách văn học', 'type' => 'book'],
    ['name' => 'Sách kỹ năng', 'type' => 'book'],
    ['name' => 'Truyện ngắn', 'type' => 'story'],
    ['name' => 'Truyện dài', 'type' => 'story'],
    ['name' => 'Nhạc Pop', 'type' => 'music'],
    ['name' => 'Nhạc Rock', 'type' => 'music'],
    ['name' => 'Podcast Kỹ năng', 'type' => 'podcast'],
    ['name' => 'Podcast Kiến thức', 'type' => 'podcast'],
    ['name' => 'Radio FM', 'type' => 'radio'],
    ['name' => 'Radio Online', 'type' => 'radio']
];

// Kiểm tra xem có cần thêm danh mục mặc định không
if (isset($_GET['add_default']) && $_GET['add_default'] == 1) {
    foreach ($defaultCategories as $category) {
        // Kiểm tra xem danh mục đã tồn tại chưa
        $exists = $db->selectOne("SELECT id FROM categories WHERE name = ? AND type = ?", [$category['name'], $category['type']]);
        if (!$exists) {
            $db->insert('categories', $category);
        }
    }
    echo '<div class="alert alert-success">Đã thêm các danh mục mặc định</div>';
}

// Xử lý thêm danh mục mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = sanitize($_POST['name']);
    $type = sanitize($_POST['type']);
    
    $data = [
        'name' => $name,
        'type' => $type
    ];
    
    $db->insert('categories', $data);
    redirect(ADMIN_URL . 'categories.php?success=1');
}

// Xử lý cập nhật danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $name = sanitize($_POST['name']);
    $type = sanitize($_POST['type']);
    
    $data = [
        'name' => $name,
        'type' => $type
    ];
    
    $db->update('categories', $data, 'id = ?', [$id]);
    redirect(ADMIN_URL . 'categories.php?updated=1');
}

// Xử lý xóa danh mục
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $db->delete('categories', 'id = ?', [$id]);
    redirect(ADMIN_URL . 'categories.php?deleted=1');
}

// Lấy danh sách danh mục hoặc chi tiết một danh mục
$editCategory = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editCategory = $db->selectOne("SELECT * FROM categories WHERE id = ?", [(int)$_GET['edit']]);
}

$categories = $db->select("SELECT * FROM categories ORDER BY type, name");

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $editCategory ? 'Chỉnh sửa danh mục' : 'Quản lý danh mục' ?></h2>
                <?php if (!$editCategory): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Thêm danh mục mới
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Thêm danh mục mới thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Cập nhật danh mục thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Xóa danh mục thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if ($editCategory): ?>
            <!-- Form chỉnh sửa danh mục -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                        
                        <div class="form-group">
                            <label>Tên danh mục</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($editCategory['name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Loại</label>
                            <select class="form-control" name="type" required>
                                <option value="book" <?= $editCategory['type'] == 'book' ? 'selected' : '' ?>>Sách</option>
                                <option value="story" <?= $editCategory['type'] == 'story' ? 'selected' : '' ?>>Truyện</option>
                                <option value="music" <?= $editCategory['type'] == 'music' ? 'selected' : '' ?>>Âm nhạc</option>
                                <option value="podcast" <?= $editCategory['type'] == 'podcast' ? 'selected' : '' ?>>Podcast</option>
                                <option value="radio" <?= $editCategory['type'] == 'radio' ? 'selected' : '' ?>>Radio</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            <a href="<?= ADMIN_URL ?>categories.php" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Danh sách danh mục -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tên danh mục</th>
                            <th>Loại</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Không có danh mục nào.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= $category['id'] ?></td>
                            <td><?= htmlspecialchars($category['name']) ?></td>
                            <td>
                                <?php
                                $typeLabels = [
                                    'book' => 'Sách',
                                    'story' => 'Truyện',
                                    'music' => 'Âm nhạc',
                                    'podcast' => 'Podcast',
                                    'radio' => 'Radio'
                                ];
                                
                                $typeClasses = [
                                    'book' => 'primary',
                                    'story' => 'success',
                                    'music' => 'info',
                                    'podcast' => 'warning',
                                    'radio' => 'danger'
                                ];
                                
                                $typeLabel = $typeLabels[$category['type']] ?? $category['type'];
                                $typeClass = $typeClasses[$category['type']] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $typeClass ?>"><?= $typeLabel ?></span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($category['created_at'])) ?></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>categories.php?edit=<?= $category['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="<?= ADMIN_URL ?>categories.php?action=delete&id=<?= $category['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
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

<!-- Modal thêm danh mục mới -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Thêm danh mục mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Tên danh mục</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Loại</label>
                        <select class="form-control" name="type" required>
                            <option value="book">Sách</option>
                            <option value="story">Truyện</option>
                            <option value="music">Âm nhạc</option>
                            <option value="podcast">Podcast</option>
                            <option value="radio">Radio</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Thêm nút "Thêm danh mục mặc định" vào giao diện -->
<div class="mb-4">
    <a href="<?= ADMIN_URL ?>categories.php?add_default=1" class="btn btn-outline-primary">
        <i class="fas fa-plus-circle"></i> Thêm danh mục mặc định
    </a>
</div>

<?php include '../includes/admin_footer.php'; ?>
