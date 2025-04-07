<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Xử lý thêm sách mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $description = sanitize($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    
    // Upload cover image
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['cover_image']['name']);
        $targetPath = COVER_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
            $cover_image = $fileName;
        }
    }
    
    // Upload PDF file
    $pdf_file = '';
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['pdf_file']['name']);
        $targetPath = BOOK_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $targetPath)) {
            $pdf_file = $fileName;
        }
    }
    
    // Upload audio file
    $audio_file = '';
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['audio_file']['name']);
        $targetPath = BOOK_UPLOAD_PATH . $fileName;
        
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
        'pdf_file' => $pdf_file,
        'audio_file' => $audio_file
    ];
    
    $db->insert('books', $data);
    redirect(ADMIN_URL . 'books.php?success=1');
}

// Xử lý cập nhật sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $description = sanitize($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    
    // Lấy thông tin hiện tại
    $currentBook = $db->selectOne("SELECT * FROM books WHERE id = ?", [$id]);
    
    // Upload cover image nếu có
    $cover_image = $currentBook['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['cover_image']['name']);
        $targetPath = COVER_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentBook['cover_image'])) {
                $oldFile = COVER_UPLOAD_PATH . $currentBook['cover_image'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $cover_image = $fileName;
        }
    }
    
    // Upload PDF file nếu có
    $pdf_file = $currentBook['pdf_file'];
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['pdf_file']['name']);
        $targetPath = BOOK_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentBook['pdf_file'])) {
                $oldFile = BOOK_UPLOAD_PATH . $currentBook['pdf_file'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $pdf_file = $fileName;
        }
    }
    
    // Upload audio file nếu có
    $audio_file = $currentBook['audio_file'];
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['audio_file']['name']);
        $targetPath = BOOK_UPLOAD_PATH . $fileName;
        
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $targetPath)) {
            // Xóa file cũ nếu có
            if (!empty($currentBook['audio_file'])) {
                $oldFile = BOOK_UPLOAD_PATH . $currentBook['audio_file'];
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
        'pdf_file' => $pdf_file,
        'audio_file' => $audio_file
    ];
    
    $db->update('books', $data, 'id = ?', [$id]);
    redirect(ADMIN_URL . 'books.php?updated=1');
}

// Xử lý xóa sách
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Lấy thông tin trước khi xóa
    $book = $db->selectOne("SELECT * FROM books WHERE id = ?", [$id]);
    
    if ($book) {
        // Xóa các file liên quan
        if (!empty($book['cover_image'])) {
            $file = COVER_UPLOAD_PATH . $book['cover_image'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        if (!empty($book['pdf_file'])) {
            $file = BOOK_UPLOAD_PATH . $book['pdf_file'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        if (!empty($book['audio_file'])) {
            $file = BOOK_UPLOAD_PATH . $book['audio_file'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Xóa từ database
        $db->delete('books', 'id = ?', [$id]);
    }
    
    redirect(ADMIN_URL . 'books.php?deleted=1');
}

// Lấy danh sách danh mục
$categories = $db->select("SELECT * FROM categories WHERE type = 'book' ORDER BY name ASC");

// Lấy danh sách sách hoặc chi tiết một sách
$editBook = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editBook = $db->selectOne("SELECT * FROM books WHERE id = ?", [(int)$_GET['edit']]);
}

$books = $db->select("SELECT b.*, c.name as category_name 
                      FROM books b 
                      LEFT JOIN categories c ON b.category_id = c.id 
                      ORDER BY b.created_at DESC");

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $editBook ? 'Chỉnh sửa sách' : 'Quản lý sách' ?></h2>
                <?php if (!$editBook): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBookModal">
                    <i class="fas fa-plus"></i> Thêm sách mới
                </button>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Thêm sách mới thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Cập nhật sách thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Xóa sách thành công!
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <?php if ($editBook): ?>
            <!-- Form chỉnh sửa sách -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editBook['id'] ?>">
                        
                        <div class="form-group">
                            <label>Tiêu đề sách</label>
                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($editBook['title']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tác giả</label>
                            <input type="text" class="form-control" name="author" value="<?= htmlspecialchars($editBook['author']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Danh mục</label>
                            <select class="form-control" name="category_id">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $editBook['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($editBook['description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Ảnh bìa</label>
                            <?php if (!empty($editBook['cover_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $editBook['cover_image'] ?>" style="max-width: 200px; max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="cover_image" accept="image/*">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi ảnh bìa.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>File PDF</label>
                            <?php if (!empty($editBook['pdf_file'])): ?>
                            <div class="mb-2">
                                <a href="<?= BASE_URL ?>assets/uploads/books/<?= $editBook['pdf_file'] ?>" target="_blank">
                                    <?= $editBook['pdf_file'] ?>
                                </a>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="pdf_file" accept=".pdf">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi file PDF.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>File audio</label>
                            <?php if (!empty($editBook['audio_file'])): ?>
                            <div class="mb-2">
                                <audio controls>
                                    <source src="<?= BASE_URL ?>assets/uploads/books/<?= $editBook['audio_file'] ?>" type="audio/mpeg">
                                    Trình duyệt của bạn không hỗ trợ phát audio.
                                </audio>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="audio_file" accept="audio/*">
                            <small class="form-text text-muted">Để trống nếu không muốn thay đổi file audio.</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            <a href="<?= ADMIN_URL ?>books.php" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Danh sách sách -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh bìa</th>
                            <th>Tiêu đề</th>
                            <th>Tác giả</th>
                            <th>Danh mục</th>
                            <th>Có PDF</th>
                            <th>Có audio</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($books)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Không có sách nào.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= $book['id'] ?></td>
                            <td>
                                <?php if (!empty($book['cover_image'])): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $book['cover_image'] ?>" 
                                     alt="<?= htmlspecialchars($book['title']) ?>" 
                                     style="max-width: 50px; max-height: 70px;">
                                <?php else: ?>
                                <span class="badge badge-secondary">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['category_name'] ?? 'Không có danh mục') ?></td>
                            <td>
                                <?php if (!empty($book['pdf_file'])): ?>
                                <span class="badge badge-success">Có</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">Không</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($book['audio_file'])): ?>
                                <span class="badge badge-success">Có</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">Không</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>books.php?edit=<?= $book['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="<?= ADMIN_URL ?>books.php?action=delete&id=<?= $book['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sách này?')">
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

<!-- Modal thêm sách mới -->
<div class="modal fade" id="addBookModal" tabindex="-1" role="dialog" aria-labelledby="addBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBookModalLabel">Thêm sách mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Tiêu đề sách</label>
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
                        <label>Ảnh bìa</label>
                        <input type="file" class="form-control-file" name="cover_image" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label>File PDF</label>
                        <input type="file" class="form-control-file" name="pdf_file" accept=".pdf">
                    </div>
                    
                    <div class="form-group">
                        <label>File audio</label>
                        <input type="file" class="form-control-file" name="audio_file" accept="audio/*">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>