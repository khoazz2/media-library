<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Xử lý thêm truyện mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $description = sanitize($_POST['description']);
    $content = $_POST['content']; // Nội dung truyện có thể có HTML
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
        $targetPath = STORY_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $targetPath)) {
            $audio_file = $fileName;
        }
    }
    
    // Thêm vào database
    $data = [
        'title' => $title,
        'author' => $author,
        'description' => $description,
        'content' => $content,
        'category_id' => $category_id,
        'cover_image' => $cover_image,
        'audio_file' => $audio_file
    ];
    
    $db->insert('stories', $data);
    redirect(ADMIN_URL . 'stories.php?success=1');
}

// Xử lý cập nhật truyện
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $description = sanitize($_POST['description']);
    $content = $_POST['content']; // Nội dung truyện có thể có HTML
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    
    // Lấy thông tin hiện tại
    $currentStory = $db->selectOne("SELECT * FROM stories WHERE id = ?", [$id]);
    
    // Upload cover image nếu có
    $cover_image = $currentStory['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['cover_image']['name']);
        $targetPath = COVER_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentStory['cover_image'])) {
                $oldFile = COVER_UPLOAD_PATH . $currentStory['cover_image'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $cover_image = $fileName;
        }
    }
    
    // Upload audio file nếu có
    $audio_file = $currentStory['audio_file'];
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['audio_file']['name']);
        $targetPath = STORY_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentStory['audio_file'])) {
                $oldFile = STORY_UPLOAD_PATH . $currentStory['audio_file'];
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
        'content' => $content,
        'category_id' => $category_id,
        'cover_image' => $cover_image,
        'audio_file' => $audio_file
    ];
    
    $db->update('stories', $data, 'id = ?', [$id]);
    redirect(ADMIN_URL . 'stories.php?updated=1');
}

// Xử lý xóa truyện
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Lấy thông tin trước khi xóa
    $story = $db->selectOne("SELECT * FROM stories WHERE id = ?", [$id]);
    
    if ($story) {
        // Xóa các file liên quan
        if (!empty($story['cover_image'])) {
            $file = COVER_UPLOAD_PATH . $story['cover_image'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        if (!empty($story['audio_file'])) {
            $file = STORY_UPLOAD_PATH . $story['audio_file'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Xóa từ database
        $db->delete('stories', 'id = ?', [$id]);
    }
    
    redirect(ADMIN_URL . 'stories.php?deleted=1');
}

// Lấy danh sách danh mục
$categories = $db->select("SELECT * FROM categories WHERE type = 'story' ORDER BY name ASC");

// Lấy danh sách truyện hoặc chi tiết một truyện
$editStory = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editStory = $db->selectOne("SELECT * FROM stories WHERE id = ?", [(int)$_GET['edit']]);
}

$stories = $db->select("SELECT s.*, c.name as category_name 
                      FROM stories s
                      LEFT JOIN categories c ON s.category_id = c.id 
                      ORDER BY s.created_at DESC");

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $editStory ? 'Chỉnh sửa truyện' : 'Quản lý truyện' ?></h2>
                <?php if (!$editStory): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addStoryModal">
                    <i class="fas fa-plus"></i> Thêm truyện mới
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Thêm truyện mới thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Cập nhật truyện thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Xóa truyện thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if ($editStory): ?>
            <!-- Form chỉnh sửa truyện -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editStory['id'] ?>">
                        
                        <div class="form-group">
                            <label>Tiêu đề truyện</label>
                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($editStory['title']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tác giả</label>
                            <input type="text" class="form-control" name="author" value="<?= htmlspecialchars($editStory['author']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Danh mục</label>
                            <select class="form-control" name="category_id">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $editStory['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($editStory['description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Nội dung truyện</label>
                            <textarea class="form-control" name="content" id="storyContent" rows="15"><?= htmlspecialchars($editStory['content']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Ảnh bìa</label>
                            <?php if (!empty($editStory['cover_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $editStory['cover_image'] ?>" style="max-width: 200px; max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="cover_image" accept="image/*">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi ảnh bìa.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>File audio (nếu có)</label>
                            <?php if (!empty($editStory['audio_file'])): ?>
                            <div class="mb-2">
                                <audio controls>
                                    <source src="<?= BASE_URL ?>assets/uploads/stories/<?= $editStory['audio_file'] ?>" type="audio/mpeg">
                                    Trình duyệt của bạn không hỗ trợ phát audio.
                                </audio>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="audio_file" accept="audio/*">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi file audio.</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            <a href="<?= ADMIN_URL ?>stories.php" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Danh sách truyện -->
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
                        <?php if (empty($stories)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có truyện nào.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($stories as $story): ?>
                        <tr>
                            <td><?= $story['id'] ?></td>
                            <td>
                                <?php if (!empty($story['cover_image'])): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $story['cover_image'] ?>" 
                                     alt="<?= htmlspecialchars($story['title']) ?>" 
                                     style="max-width: 50px; max-height: 70px;">
                                <?php else: ?>
                                <span class="badge badge-secondary">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($story['title']) ?></td>
                            <td><?= htmlspecialchars($story['author']) ?></td>
                            <td><?= htmlspecialchars($story['category_name'] ?? 'Không có danh mục') ?></td>
                            <td><?= date('d/m/Y', strtotime($story['created_at'])) ?></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>stories.php?edit=<?= $story['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="<?= ADMIN_URL ?>stories.php?action=delete&id=<?= $story['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa truyện này?')">
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

<!-- Modal thêm truyện mới -->
<div class="modal fade" id="addStoryModal" tabindex="-1" role="dialog" aria-labelledby="addStoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStoryModalLabel">Thêm truyện mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Tiêu đề truyện</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tác giả</label>
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
                        <label>Nội dung truyện</label>
                        <textarea class="form-control" name="content" id="newStoryContent" rows="15"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Ảnh bìa</label>
                        <input type="file" class="form-control-file" name="cover_image" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label>File audio (nếu có)</label>
                        <input type="file" class="form-control-file" name="audio_file" accept="audio/*">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Khởi tạo trình soạn thảo nội dung nếu có thư viện
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#storyContent'))
            .catch(error => {
                console.error(error);
            });
            
        ClassicEditor
            .create(document.querySelector('#newStoryContent'))
            .catch(error => {
                console.error(error);
            });
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?>