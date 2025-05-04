<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Xem chi tiết một truyện
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $storyId = (int)$_GET['id'];
    $story = $db->selectOne("SELECT s.*, c.name as category_name 
                            FROM stories s 
                            LEFT JOIN categories c ON s.category_id = c.id 
                            WHERE s.id = ?", [$storyId]);
    
    include '../includes/header.php';
    
    if (!$story) {
        echo '<div class="container mt-5"><div class="alert alert-danger">Truyện không tồn tại!</div></div>';
        include '../includes/footer.php';
        exit;
    }
    
    // Hiển thị chi tiết truyện
    ?>
    <div class="container mt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= USER_URL ?>stories.php">Truyện</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($story['title']) ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-3">
                <?php if (!empty($story['cover_image'])): ?>
                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $story['cover_image'] ?>" 
                     alt="<?= htmlspecialchars($story['title']) ?>" class="img-fluid rounded shadow mb-4">
                <?php else: ?>
                <div class="bg-success text-white p-5 text-center rounded mb-4">
                    <i class="fas fa-book-open fa-5x mb-3"></i>
                    <h5>Không có ảnh bìa</h5>
                </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Thông tin truyện</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Tác giả:</strong> <?= htmlspecialchars($story['author']) ?></li>
                            <?php if (!empty($story['category_name'])): ?>
                            <li class="mb-2"><strong>Thể loại:</strong> <?= htmlspecialchars($story['category_name']) ?></li>
                            <?php endif; ?>
                            <li class="mb-2"><strong>Ngày đăng:</strong> <?= date('d/m/Y', strtotime($story['created_at'])) ?></li>
                        </ul>
                    </div>
                </div>
                
                <?php if (!empty($story['audio_file'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Nghe truyện Audio</h5>
                    </div>
                    <div class="card-body">
                        <audio controls class="w-100 mb-3">
                            <source src="<?= BASE_URL ?>assets/uploads/stories/<?= $story['audio_file'] ?>" type="audio/mpeg">
                            Trình duyệt của bạn không hỗ trợ phát audio.
                        </audio>
                        <button class="btn btn-success btn-sm btn-block mt-2" id="toggleAudioReading">
                            <i class="fas fa-headphones"></i> Nghe truyện
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="mb-3"><?= htmlspecialchars($story['title']) ?></h1>
                        
                        <?php if (!empty($story['description'])): ?>
                        <div class="story-description alert alert-light mb-4">
                            <h5 class="mb-3">Giới thiệu</h5>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($story['description'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="story-content mt-4">
                            <h5 class="mb-3">Nội dung truyện</h5>
                            <div class="story-text p-3 bg-light rounded">
                                <?php 
                                if (!empty($story['content'])) {
                                    // Format nội dung truyện, giữ định dạng HTML nếu có
                                    echo nl2br($story['content']);
                                } else {
                                    echo '<div class="alert alert-warning">Truyện này chưa có nội dung.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Truyện liên quan -->
                <?php
                if (!empty($story['category_id'])) {
                    $relatedStories = $db->select(
                        "SELECT * FROM stories 
                         WHERE category_id = ? AND id != ? 
                         ORDER BY created_at DESC LIMIT 3",
                        [$story['category_id'], $story['id']]
                    );
                    
                    if (!empty($relatedStories)):
                ?>
                <div class="mt-5">
                    <h4 class="mb-4">Truyện cùng thể loại</h4>
                    <div class="row">
                        <?php foreach ($relatedStories as $relatedStory): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-img-top" style="height: 150px; overflow: hidden;">
                                    <?php if (!empty($relatedStory['cover_image'])): ?>
                                    <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $relatedStory['cover_image'] ?>" 
                                         alt="<?= htmlspecialchars($relatedStory['title']) ?>" class="img-fluid w-100 h-100" style="object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-success text-white h-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-book-open fa-3x"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($relatedStory['title']) ?></h5>
                                    <p class="card-text small text-muted">Tác giả: <?= htmlspecialchars($relatedStory['author']) ?></p>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="<?= USER_URL ?>stories.php?id=<?= $relatedStory['id'] ?>" class="btn btn-success btn-sm btn-block">
                                        <i class="fas fa-book-reader"></i> Đọc truyện
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php 
                    endif;
                }
                ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleAudioReading = document.getElementById('toggleAudioReading');
            if (toggleAudioReading) {
                const audioPlayer = document.querySelector('audio');
                
                toggleAudioReading.addEventListener('click', function() {
                    if (audioPlayer.paused) {
                        audioPlayer.play();
                        this.innerHTML = '<i class="fas fa-pause"></i> Tạm dừng';
                    } else {
                        audioPlayer.pause();
                        this.innerHTML = '<i class="fas fa-headphones"></i> Tiếp tục nghe';
                    }
                });
            }
        });
    </script>
    <?php
} else {
    // Hiển thị danh sách tất cả truyện
    $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
    
    // Xây dựng câu truy vấn
    $query = "SELECT s.*, c.name as category_name 
              FROM stories s 
              LEFT JOIN categories c ON s.category_id = c.id";
    $params = [];
    
    $whereConditions = [];
    if ($categoryId) {
        $whereConditions[] = "s.category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($search) {
        $whereConditions[] = "(s.title LIKE ? OR s.author LIKE ? OR s.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(' AND ', $whereConditions);
    }
    
    $query .= " ORDER BY s.created_at DESC";
    
    $stories = $db->select($query, $params);
    $categories = $db->select("SELECT * FROM categories WHERE type = 'story' ORDER BY name");
    
    include '../includes/header.php';
    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Tìm kiếm</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= USER_URL ?>stories.php" method="get">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm truyện..." 
                                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            </div>
                            <button type="submit" class="btn btn-success btn-block">Tìm kiếm</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Thể loại</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item <?= !isset($_GET['category']) ? 'active' : '' ?>">
                                <a href="<?= USER_URL ?>stories.php" class="<?= !isset($_GET['category']) ? 'text-white' : 'text-dark' ?>">
                                    Tất cả thể loại
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li class="list-group-item <?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'active' : '' ?>">
                                <a href="<?= USER_URL ?>stories.php?category=<?= $category['id'] ?>" 
                                   class="<?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'text-white' : 'text-dark' ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <h2 class="mb-4">Thư viện truyện</h2>
                
                <?php if (isset($_GET['search']) || isset($_GET['category'])): ?>
                <div class="mb-4">
                    <h6>
                        <?php if (isset($_GET['search'])): ?>
                        Kết quả tìm kiếm cho: <span class="text-success">"<?= htmlspecialchars($_GET['search']) ?>"</span>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['category'])): ?>
                        <?php
                        $categoryName = '';
                        foreach ($categories as $cat) {
                            if ($cat['id'] == $_GET['category']) {
                                $categoryName = $cat['name'];
                                break;
                            }
                        }
                        ?>
                        Thể loại: <span class="text-success"><?= htmlspecialchars($categoryName) ?></span>
                        <?php endif; ?>
                    </h6>
                    
                    <a href="<?= USER_URL ?>stories.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <?php if (empty($stories)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            Không có truyện nào được tìm thấy.
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($stories as $story): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="row no-gutters">
                                <div class="col-md-4">
                                    <?php if (!empty($story['cover_image'])): ?>
                                    <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $story['cover_image'] ?>" 
                                         alt="<?= htmlspecialchars($story['title']) ?>" 
                                         class="img-fluid" style="height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-success text-white h-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-book-open fa-3x"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($story['title']) ?></h5>
                                        <p class="card-text small"><strong>Tác giả:</strong> <?= htmlspecialchars($story['author']) ?></p>
                                        
                                        <?php if (!empty($story['category_name'])): ?>
                                        <p class="card-text small">
                                            <span class="badge badge-success"><?= htmlspecialchars($story['category_name']) ?></span>
                                            
                                            <?php if (!empty($story['audio_file'])): ?>
                                            <span class="badge badge-warning">Có Audio</span>
                                            <?php endif; ?>
                                        </p>
                                        <?php endif; ?>
                                        
                                        <p class="card-text small text-muted">
                                            <?= substr(strip_tags(htmlspecialchars($story['description'] ?? '')), 0, 100) ?>...
                                        </p>
                                        
                                        <a href="<?= USER_URL ?>stories.php?id=<?= $story['id'] ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-book-reader"></i> Đọc truyện
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

include '../includes/footer.php';
?>