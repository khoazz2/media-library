<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Xử lý thêm podcast mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $description = sanitize($_POST['description']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    
    // Upload cover image
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['cover_image']['name']);
        $targetPath = COVER_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
            $cover_image = $fileName;
        }
    }
    
    // Upload audio file
    $audio_file = '';
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['audio_file']['name']);
        $targetPath = PODCAST_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $targetPath)) {
            $audio_file = $fileName;
        }
    }
    
    // Thêm vào database
    $data = [
        'title' => $title,
        'author' => $author,
        'description' => $description,
        'category_id' => $category_id,
        'cover_image' => $cover_image,
        'audio_file' => $audio_file
    ];
    
    $db->insert('podcasts', $data);
    redirect(ADMIN_URL . 'podcasts.php?success=1');
}

// Xử lý cập nhật podcast
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $description = sanitize($_POST['description']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    
    // Lấy thông tin hiện tại
    $currentPodcast = $db->selectOne("SELECT * FROM podcasts WHERE id = ?", [$id]);
    
    // Upload cover image nếu có
    $cover_image = $currentPodcast['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['cover_image']['name']);
        $targetPath = COVER_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentPodcast['cover_image'])) {
                $oldFile = COVER_UPLOAD_PATH . $currentPodcast['cover_image'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $cover_image = $fileName;
        }
    }
    
    // Upload audio file nếu có
    $audio_file = $currentPodcast['audio_file'];
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['audio_file']['name']);
        $targetPath = PODCAST_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentPodcast['audio_file'])) {
                $oldFile = PODCAST_UPLOAD_PATH . $currentPodcast['audio_file'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $audio_file = $fileName;
        }
    }
    
    // Cập nhật vào database
    $data = [
        'title' => $title,
        'author' => $author,
        'description' => $description,
        'category_id' => $category_id,
        'cover_image' => $cover_image,
        'audio_file' => $audio_file
    ];
    
    $db->update('podcasts', $data, 'id = ?', [$id]);
    redirect(ADMIN_URL . 'podcasts.php?updated=1');
}

// Xử lý xóa podcast
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Lấy thông tin trước khi xóa
    $podcast = $db->selectOne("SELECT * FROM podcasts WHERE id = ?", [$id]);
    
    if ($podcast) {
        // Xóa các file liên quan
        if (!empty($podcast['cover_image'])) {
            $file = COVER_UPLOAD_PATH . $podcast['cover_image'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        if (!empty($podcast['audio_file'])) {
            $file = PODCAST_UPLOAD_PATH . $podcast['audio_file'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Xóa từ database
        $db->delete('podcasts', 'id = ?', [$id]);
    }
    
    redirect(ADMIN_URL . 'podcasts.php?deleted=1');
}

// Lấy danh sách danh mục
$categories = $db->select("SELECT * FROM categories WHERE type = 'podcast' ORDER BY name ASC");

// Lấy danh sách podcast hoặc chi tiết một podcast
$editPodcast = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editPodcast = $db->selectOne("SELECT * FROM podcasts WHERE id = ?", [(int)$_GET['edit']]);
}

$podcasts = $db->select("SELECT p.*, c.name as category_name 
                      FROM podcasts p
                      LEFT JOIN categories c ON p.category_id = c.id 
                      ORDER BY p.created_at DESC");

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $editPodcast ? 'Chỉnh sửa podcast' : 'Quản lý podcast' ?></h2>
                <?php if (!$editPodcast): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPodcastModal">
                    <i class="fas fa-plus"></i> Thêm podcast mới
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Thêm podcast mới thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Cập nhật podcast thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Xóa podcast thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if ($editPodcast): ?>
            <!-- Form chỉnh sửa podcast -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editPodcast['id'] ?>">
                        
                        <div class="form-group">
                            <label>Tiêu đề podcast</label>
                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($editPodcast['title']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tác giả/Host</label>
                            <input type="text" class="form-control" name="author" value="<?= htmlspecialchars($editPodcast['author']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Danh mục</label>
                            <select class="form-control" name="category_id">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $editPodcast['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($editPodcast['description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Ảnh bìa</label>
                            <?php if (!empty($editPodcast['cover_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $editPodcast['cover_image'] ?>" style="max-width: 200px; max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="cover_image" accept="image/*">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi ảnh bìa.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>File audio</label>
                            <?php if (!empty($editPodcast['audio_file'])): ?>
                            <div class="mb-2">
                                <audio controls>
                                    <source src="<?= BASE_URL ?>assets/uploads/podcasts/<?= $editPodcast['audio_file'] ?>" type="audio/mpeg">
                                    Trình duyệt của bạn không hỗ trợ phát audio.
                                </audio>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="audio_file" accept="audio/*">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi file audio.</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            <a href="<?= ADMIN_URL ?>podcasts.php" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Danh sách podcast -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh bìa</th>
                            <th>Tiêu đề</th>
                            <th>Tác giả</th>
                            <th>Danh mục</th>
                            <th>Ngày thêm</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($podcasts)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có podcast nào.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($podcasts as $podcast): ?>
                        <tr>
                            <td><?= $podcast['id'] ?></td>
                            <td>
                                <?php if (!empty($podcast['cover_image'])): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $podcast['cover_image'] ?>" 
                                     alt="<?= htmlspecialchars($podcast['title']) ?>" 
                                     style="max-width: 50px; max-height: 70px;">
                                <?php else: ?>
                                <span class="badge badge-secondary">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($podcast['title']) ?></td>
                            <td><?= htmlspecialchars($podcast['author']) ?></td>
                            <td><?= htmlspecialchars($podcast['category_name'] ?? 'Không có danh mục') ?></td>
                            <td><?= date('d/m/Y', strtotime($podcast['created_at'])) ?></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>podcasts.php?edit=<?= $podcast['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="<?= ADMIN_URL ?>podcasts.php?action=delete&id=<?= $podcast['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa podcast này?')">
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

<!-- Modal thêm podcast mới -->
<div class="modal fade" id="addPodcastModal" tabindex="-1" role="dialog" aria-labelledby="addPodcastModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPodcastModalLabel">Thêm podcast mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Tiêu đề podcast</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tác giả/Host</label>
                        <input type="text" class="form-control" name="author" required>
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
                        <label>Mô tả</label>
                        <textarea class="form-control" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Ảnh bìa</label>
                        <input type="file" class="form-control-file" name="cover_image" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label>File audio</label>
                        <input type="file" class="form-control-file" name="audio_file" accept="audio/*" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
