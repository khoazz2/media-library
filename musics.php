<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Xử lý thêm nhạc mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = sanitize($_POST['title']);
    $artist = sanitize($_POST['artist']);
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
        $targetPath = MUSIC_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $targetPath)) {
            $audio_file = $fileName;
        }
    }
    
    // Thêm vào database
    $data = [
        'title' => $title,
        'artist' => $artist,
        'description' => $description,
        'category_id' => $category_id,
        'cover_image' => $cover_image,
        'audio_file' => $audio_file
    ];
    
    $db->insert('musics', $data);
    redirect(ADMIN_URL . 'musics.php?success=1');
}

// Xử lý cập nhật nhạc
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $title = sanitize($_POST['title']);
    $artist = sanitize($_POST['artist']);
    $description = sanitize($_POST['description']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    
    // Lấy thông tin hiện tại
    $currentMusic = $db->selectOne("SELECT * FROM musics WHERE id = ?", [$id]);
    
    // Upload cover image nếu có
    $cover_image = $currentMusic['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['cover_image']['name']);
        $targetPath = COVER_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentMusic['cover_image'])) {
                $oldFile = COVER_UPLOAD_PATH . $currentMusic['cover_image'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $cover_image = $fileName;
        }
    }
    
    // Upload audio file nếu có
    $audio_file = $currentMusic['audio_file'];
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['audio_file']['name']);
        $targetPath = MUSIC_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentMusic['audio_file'])) {
                $oldFile = MUSIC_UPLOAD_PATH . $currentMusic['audio_file'];
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
        'artist' => $artist,
        'description' => $description,
        'category_id' => $category_id,
        'cover_image' => $cover_image,
        'audio_file' => $audio_file
    ];
    
    $db->update('musics', $data, 'id = ?', [$id]);
    redirect(ADMIN_URL . 'musics.php?updated=1');
}

// Xử lý xóa nhạc
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Lấy thông tin trước khi xóa
    $music = $db->selectOne("SELECT * FROM musics WHERE id = ?", [$id]);
    
    if ($music) {
        // Xóa các file liên quan
        if (!empty($music['cover_image'])) {
            $file = COVER_UPLOAD_PATH . $music['cover_image'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        if (!empty($music['audio_file'])) {
            $file = MUSIC_UPLOAD_PATH . $music['audio_file'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Xóa từ database
        $db->delete('musics', 'id = ?', [$id]);
    }
    
    redirect(ADMIN_URL . 'musics.php?deleted=1');
}

// Lấy danh sách danh mục
$categories = $db->select("SELECT * FROM categories WHERE type = 'music' ORDER BY name ASC");

// Lấy danh sách nhạc hoặc chi tiết một bài hát
$editMusic = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editMusic = $db->selectOne("SELECT * FROM musics WHERE id = ?", [(int)$_GET['edit']]);
}

$musics = $db->select("SELECT m.*, c.name as category_name 
                      FROM musics m
                      LEFT JOIN categories c ON m.category_id = c.id 
                      ORDER BY m.created_at DESC");

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $editMusic ? 'Chỉnh sửa bài hát' : 'Quản lý âm nhạc' ?></h2>
                <?php if (!$editMusic): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addMusicModal">
                    <i class="fas fa-plus"></i> Thêm bài hát mới
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Thêm bài hát mới thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Cập nhật bài hát thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Xóa bài hát thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if ($editMusic): ?>
            <!-- Form chỉnh sửa bài hát -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editMusic['id'] ?>">
                        
                        <div class="form-group">
                            <label>Tiêu đề bài hát</label>
                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($editMusic['title']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nghệ sĩ</label>
                            <input type="text" class="form-control" name="artist" value="<?= htmlspecialchars($editMusic['artist']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Danh mục</label>
                            <select class="form-control" name="category_id">
                                <option value="">-- Không có danh mục --</option>
                                <?php 
                                if (empty($categories)): 
                                ?>
                                <option value="" disabled>Chưa có danh mục. Vui lòng thêm danh mục trước.</option>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $editMusic['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <?php if (empty($categories)): ?>
                            <small class="form-text text-danger">
                                Chưa có danh mục nào. <a href="<?= ADMIN_URL ?>categories.php">Thêm danh mục</a> trước khi thêm nội dung.
                            </small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($editMusic['description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Ảnh bìa</label>
                            <?php if (!empty($editMusic['cover_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $editMusic['cover_image'] ?>" style="max-width: 200px; max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="cover_image" accept="image/*">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi ảnh bìa.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>File audio</label>
                            <?php if (!empty($editMusic['audio_file'])): ?>
                            <div class="mb-2">
                                <audio controls>
                                    <source src="<?= BASE_URL ?>assets/uploads/musics/<?= $editMusic['audio_file'] ?>" type="audio/mpeg">
                                    Trình duyệt của bạn không hỗ trợ phát audio.
                                </audio>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="audio_file" accept="audio/*">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi file audio.</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            <a href="<?= ADMIN_URL ?>musics.php" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Danh sách bài hát -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh bìa</th>
                            <th>Tiêu đề</th>
                            <th>Nghệ sĩ</th>
                            <th>Danh mục</th>
                            <th>Nghe thử</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($musics)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có bài hát nào.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($musics as $music): ?>
                        <tr>
                            <td><?= $music['id'] ?></td>
                            <td>
                                <?php if (!empty($music['cover_image'])): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $music['cover_image'] ?>" 
                                     alt="<?= htmlspecialchars($music['title']) ?>" 
                                     style="max-width: 50px; max-height: 50px;">
                                <?php else: ?>
                                <span class="badge badge-secondary">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($music['title']) ?></td>
                            <td><?= htmlspecialchars($music['artist']) ?></td>
                            <td><?= htmlspecialchars($music['category_name'] ?? 'Không có danh mục') ?></td>
                            <td>
                                <?php if (!empty($music['audio_file'])): ?>
                                <audio controls style="max-width: 200px;">
                                    <source src="<?= BASE_URL ?>assets/uploads/musics/<?= $music['audio_file'] ?>" type="audio/mpeg">
                                    Trình duyệt của bạn không hỗ trợ phát audio.
                                </audio>
                                <?php else: ?>
                                <span class="badge badge-secondary">Không có file audio</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>musics.php?edit=<?= $music['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="<?= ADMIN_URL ?>musics.php?action=delete&id=<?= $music['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa bài hát này?')">
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

<!-- Modal thêm bài hát mới -->
<div class="modal fade" id="addMusicModal" tabindex="-1" role="dialog" aria-labelledby="addMusicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMusicModalLabel">Thêm bài hát mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Tiêu đề bài hát</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nghệ sĩ</label>
                        <input type="text" class="form-control" name="artist" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Danh mục</label>
                        <select class="form-control" name="category_id">
                            <option value="">-- Không có danh mục --</option>
                            <?php 
                            if (empty($categories)): 
                            ?>
                            <option value="" disabled>Chưa có danh mục. Vui lòng thêm danh mục trước.</option>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($categories)): ?>
                        <small class="form-text text-danger">
                            Chưa có danh mục nào. <a href="<?= ADMIN_URL ?>categories.php">Thêm danh mục</a> trước khi thêm nội dung.
                        </small>
                        <?php endif; ?>
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
