<?php
require_once '../config.php';
require_once '../database/database.php';

// Khởi tạo thông báo và kết quả
$message = '';
$messageType = '';
$uploadedFiles = [];

// Xử lý xóa file
if (isset($_GET['delete']) && isset($_GET['dir'])) {
    $fileName = sanitize($_GET['delete']);
    $dirType = sanitize($_GET['dir']);
    
    // Xác định thư mục lưu trữ dựa trên loại
    switch ($dirType) {
        case 'books':
            $uploadDir = BOOK_UPLOAD_PATH;
            break;
        case 'stories':
            $uploadDir = STORY_UPLOAD_PATH;
            break;
        case 'musics':
            $uploadDir = MUSIC_UPLOAD_PATH;
            break;
        case 'podcasts':
            $uploadDir = PODCAST_UPLOAD_PATH;
            break;
        case 'covers':
            $uploadDir = COVER_UPLOAD_PATH;
            break;
        default:
            $message = 'Thư mục không hợp lệ!';
            $messageType = 'danger';
            redirect(ADMIN_URL . 'upload.php?error=invalid_dir');
            exit;
    }
    
    // Đường dẫn đầy đủ đến file cần xóa
    $filePath = $uploadDir . $fileName;
    
    // Kiểm tra file tồn tại, và thuộc thư mục upload
    if (file_exists($filePath) && is_file($filePath) && strpos(realpath($filePath), realpath($uploadDir)) === 0) {
        if (unlink($filePath)) {
            $message = 'Đã xóa file thành công!';
            $messageType = 'success';
            
            // Kiểm tra xem file có được sử dụng trong database không
            $db = new Database();
            $tables = [
                'books' => ['pdf_file', 'audio_file'],
                'stories' => ['audio_file'],
                'musics' => ['audio_file'],
                'podcasts' => ['audio_file'],
                'books' => ['cover_image'],
                'stories' => ['cover_image'],
                'musics' => ['cover_image'],
                'podcasts' => ['cover_image'],
                'radio_stations' => ['logo_image']
            ];
            
            foreach ($tables as $table => $fields) {
                foreach ($fields as $field) {
                    $check = $db->select("SELECT id FROM $table WHERE $field = ?", [$fileName]);
                    if (!empty($check)) {
                        $message .= ' <strong>Cảnh báo:</strong> File này đang được sử dụng trong bảng ' . $table . '. Có thể gây lỗi hiển thị!';
                        $messageType = 'warning';
                        break 2;
                    }
                }
            }
        } else {
            $message = 'Không thể xóa file! Vui lòng kiểm tra quyền truy cập.';
            $messageType = 'danger';
        }
    } else {
        $message = 'File không tồn tại hoặc đường dẫn không hợp lệ!';
        $messageType = 'danger';
    }
    
    // Chuyển hướng về trang upload với tab đang chọn
    redirect(ADMIN_URL . 'upload.php?tab=' . $dirType . '&msg=' . urlencode($message) . '&type=' . $messageType);
}

// Hiển thị thông báo từ URL nếu có
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'];
}

// Xử lý upload file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $uploadType = sanitize($_POST['upload_type']);
    $allowUpload = true;
    
    // Xác định thư mục upload dựa trên loại
    switch ($uploadType) {
        case 'book':
            $uploadDir = BOOK_UPLOAD_PATH;
            $allowedTypes = ['application/pdf', 'audio/mpeg', 'audio/mp3'];
            break;
        case 'story':
            $uploadDir = STORY_UPLOAD_PATH;
            $allowedTypes = ['text/plain', 'audio/mpeg', 'audio/mp3'];
            break;
        case 'music':
            $uploadDir = MUSIC_UPLOAD_PATH;
            $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav'];
            break;
        case 'podcast':
            $uploadDir = PODCAST_UPLOAD_PATH;
            $allowedTypes = ['audio/mpeg', 'audio/mp3'];
            break;
        case 'cover':
            $uploadDir = COVER_UPLOAD_PATH;
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            break;
        default:
            $allowUpload = false;
            $message = 'Loại upload không hợp lệ!';
            $messageType = 'danger';
    }
    
    // Tiến hành upload nếu loại hợp lệ
    if ($allowUpload) {
        // Kiểm tra nếu có files được chọn
        if (!empty($_FILES['files']['name'][0])) {
            // Lặp qua từng file
            $fileCount = count($_FILES['files']['name']);
            $successCount = 0;
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['files']['error'][$i] === 0) {
                    $tempName = $_FILES['files']['tmp_name'][$i];
                    $originalName = $_FILES['files']['name'][$i];
                    $fileType = $_FILES['files']['type'][$i];
                    $fileSize = $_FILES['files']['size'][$i];
                    
                    // Kiểm tra loại file
                    if (!in_array($fileType, $allowedTypes)) {
                        $uploadedFiles[] = [
                            'name' => $originalName,
                            'status' => 'Thất bại',
                            'reason' => 'Loại file không được hỗ trợ'
                        ];
                        continue;
                    }
                    
                    // Kiểm tra kích thước file (giới hạn 50MB)
                    if ($fileSize > 50 * 1024 * 1024) {
                        $uploadedFiles[] = [
                            'name' => $originalName,
                            'status' => 'Thất bại',
                            'reason' => 'Kích thước file vượt quá giới hạn (50MB)'
                        ];
                        continue;
                    }
                    
                    // Tạo tên file mới
                    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
                    $destination = $uploadDir . $fileName;
                    
                    // Di chuyển file
                    if (move_uploaded_file($tempName, $destination)) {
                        $uploadedFiles[] = [
                            'name' => $originalName,
                            'new_name' => $fileName,
                            'status' => 'Thành công',
                            'path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $destination)
                        ];
                        $successCount++;
                    } else {
                        $uploadedFiles[] = [
                            'name' => $originalName,
                            'status' => 'Thất bại',
                            'reason' => 'Lỗi khi di chuyển file'
                        ];
                    }
                } else {
                    $uploadedFiles[] = [
                        'name' => $_FILES['files']['name'][$i],
                        'status' => 'Thất bại',
                        'reason' => 'Lỗi upload: ' . $_FILES['files']['error'][$i]
                    ];
                }
            }
            
            if ($successCount > 0) {
                $message = "Đã upload thành công $successCount/$fileCount file!";
                $messageType = 'success';
            } else {
                $message = 'Không có file nào được upload thành công!';
                $messageType = 'danger';
            }
        } else {
            $message = 'Vui lòng chọn ít nhất một file để upload!';
            $messageType = 'warning';
        }
    }
}

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Quản lý Upload File</h2>
            
            <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Upload File</h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Loại nội dung</label>
                            <select class="form-control" name="upload_type" id="uploadType" required>
                                <option value="">-- Chọn loại nội dung --</option>
                                <option value="book">Sách (PDF, Audio)</option>
                                <option value="story">Truyện (Text, Audio)</option>
                                <option value="music">Âm nhạc (MP3, WAV)</option>
                                <option value="podcast">Podcast (MP3)</option>
                                <option value="cover">Ảnh bìa (JPG, PNG, GIF)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Chọn file</label>
                            <input type="file" class="form-control-file" name="files[]" id="files" multiple required>
                            <small class="form-text text-muted" id="formatHelp">
                                Hãy chọn loại nội dung trước.
                            </small>
                        </div>
                        
                        <button type="submit" name="upload" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if (!empty($uploadedFiles)): ?>
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Kết quả Upload</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Tên file</th>
                                    <th>Tên file mới</th>
                                    <th>Trạng thái</th>
                                    <th>Đường dẫn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($uploadedFiles as $file): ?>
                                <tr>
                                    <td><?= htmlspecialchars($file['name']) ?></td>
                                    <td><?= isset($file['new_name']) ? htmlspecialchars($file['new_name']) : '-' ?></td>
                                    <td>
                                        <?php if ($file['status'] === 'Thành công'): ?>
                                        <span class="badge badge-success">Thành công</span>
                                        <?php else: ?>
                                        <span class="badge badge-danger" title="<?= htmlspecialchars($file['reason'] ?? '') ?>">Thất bại</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($file['status'] === 'Thành công'): ?>
                                        <a href="<?= isset($file['path']) ? BASE_URL . substr($file['path'], 1) : '#' ?>" target="_blank">
                                            Xem file
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted"><?= htmlspecialchars($file['reason'] ?? 'Lỗi không xác định') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Danh sách thư mục và file -->
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Quản lý thư mục upload</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="folderTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="books-tab" data-toggle="tab" href="#books" role="tab">Sách</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="stories-tab" data-toggle="tab" href="#stories" role="tab">Truyện</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="musics-tab" data-toggle="tab" href="#musics" role="tab">Âm nhạc</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="podcasts-tab" data-toggle="tab" href="#podcasts" role="tab">Podcast</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="covers-tab" data-toggle="tab" href="#covers" role="tab">Ảnh bìa</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="folderTabContent">
                        <?php
                        $directories = [
                            'books' => BOOK_UPLOAD_PATH,
                            'stories' => STORY_UPLOAD_PATH,
                            'musics' => MUSIC_UPLOAD_PATH,
                            'podcasts' => PODCAST_UPLOAD_PATH,
                            'covers' => COVER_UPLOAD_PATH
                        ];
                        
                        foreach ($directories as $key => $dir):
                            $active = ($key === 'books') ? 'show active' : '';
                            $files = [];
                            if (is_dir($dir)) {
                                $files = array_diff(scandir($dir), ['.', '..']);
                            }
                        ?>
                        <div class="tab-pane fade <?= $active ?>" id="<?= $key ?>" role="tabpanel">
                            <?php if (empty($files)): ?>
                            <div class="alert alert-info">Không có file nào trong thư mục này.</div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tên file</th>
                                            <th>Loại</th>
                                            <th>Kích thước</th>
                                            <th>Ngày tạo</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($files as $file): ?>
                                        <?php
                                            $filePath = $dir . $file;
                                            if (is_file($filePath)):
                                                $fileSize = filesize($filePath);
                                                $fileType = mime_content_type($filePath);
                                                $fileTime = filemtime($filePath);
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($file) ?></td>
                                            <td><?= htmlspecialchars($fileType) ?></td>
                                            <td>
                                                <?php
                                                    if ($fileSize < 1024) {
                                                        echo $fileSize . ' B';
                                                    } elseif ($fileSize < 1024 * 1024) {
                                                        echo round($fileSize / 1024, 2) . ' KB';
                                                    } else {
                                                        echo round($fileSize / (1024 * 1024), 2) . ' MB';
                                                    }
                                                ?>
                                            </td>
                                            <td><?= date('d/m/Y H:i', $fileTime) ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?= BASE_URL ?>assets/uploads/<?= $key ?>/<?= urlencode($file) ?>" 
                                                       class="btn btn-sm btn-info" target="_blank">
                                                        <i class="fas fa-eye"></i> Xem
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" 
                                                            data-toggle="modal" 
                                                            data-target="#deleteFileModal"
                                                            data-file="<?= htmlspecialchars($file) ?>"
                                                            data-dir="<?= $key ?>"
                                                            data-path="<?= htmlspecialchars(substr($filePath, strlen($_SERVER['DOCUMENT_ROOT']))) ?>">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa file -->
<div class="modal fade" id="deleteFileModal" tabindex="-1" role="dialog" aria-labelledby="deleteFileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteFileModalLabel">Xác nhận xóa file</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa file <strong id="fileName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Cảnh báo: Việc xóa file có thể gây ra lỗi hiển thị nếu file này đang được sử dụng trong website.
                </div>
                
                <div id="filePreview" class="text-center my-3">
                    <!-- Xem trước file sẽ được hiển thị ở đây -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Xác nhận xóa
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadType = document.getElementById('uploadType');
    const formatHelp = document.getElementById('formatHelp');
    const fileInput = document.getElementById('files');
    
    uploadType.addEventListener('change', function() {
        switch(this.value) {
            case 'book':
                formatHelp.textContent = 'Định dạng hỗ trợ: PDF, MP3. Kích thước tối đa: 50MB.';
                fileInput.accept = '.pdf,.mp3';
                break;
            case 'story':
                formatHelp.textContent = 'Định dạng hỗ trợ: TXT, MP3. Kích thước tối đa: 50MB.';
                fileInput.accept = '.txt,.mp3';
                break;
            case 'music':
                formatHelp.textContent = 'Định dạng hỗ trợ: MP3, WAV. Kích thước tối đa: 50MB.';
                fileInput.accept = '.mp3,.wav';
                break;
            case 'podcast':
                formatHelp.textContent = 'Định dạng hỗ trợ: MP3. Kích thước tối đa: 50MB.';
                fileInput.accept = '.mp3';
                break;
            case 'cover':
                formatHelp.textContent = 'Định dạng hỗ trợ: JPG, PNG, GIF, WEBP. Kích thước tối đa: 5MB.';
                fileInput.accept = '.jpg,.jpeg,.png,.gif,.webp';
                break;
            default:
                formatHelp.textContent = 'Hãy chọn loại nội dung trước.';
                fileInput.accept = '';
        }
    });
});

// JavaScript để xử lý modal xóa file
$(document).ready(function() {
    $('#deleteFileModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var fileName = button.data('file');
        var fileDir = button.data('dir');
        var filePath = button.data('path');
        
        var modal = $(this);
        modal.find('#fileName').text(fileName);
        
        // Tạo URL xóa file
        var deleteUrl = '<?= ADMIN_URL ?>upload.php?delete=' + encodeURIComponent(fileName) + '&dir=' + fileDir;
        modal.find('#confirmDeleteBtn').attr('href', deleteUrl);
        
        // Hiển thị xem trước file tùy theo loại
        var filePreview = modal.find('#filePreview');
        filePreview.empty();
        
        var fileExtension = fileName.split('.').pop().toLowerCase();
        var fileUrl = '<?= BASE_URL ?>assets/uploads/' + fileDir + '/' + fileName;
        
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
            // Hiển thị xem trước hình ảnh
            filePreview.html('<img src="' + fileUrl + '" class="img-fluid" style="max-height: 200px;">');
        } else if (['mp3', 'wav', 'ogg'].includes(fileExtension)) {
            // Hiển thị player audio
            filePreview.html('<audio controls class="w-100"><source src="' + fileUrl + '"></audio>');
        } else if (['mp4', 'webm'].includes(fileExtension)) {
            // Hiển thị player video
            filePreview.html('<video controls class="w-100" style="max-height: 200px;"><source src="' + fileUrl + '"></video>');
        } else if ('pdf' === fileExtension) {
            // Hiển thị link PDF
            filePreview.html('<a href="' + fileUrl + '" target="_blank" class="btn btn-outline-info"><i class="fas fa-file-pdf"></i> Xem file PDF</a>');
        } else {
            // Hiển thị icon file khác
            filePreview.html('<i class="fas fa-file fa-4x text-secondary"></i><p class="mt-2">'+fileName+'</p>');
        }
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>