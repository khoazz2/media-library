<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Xem chi tiết một podcast
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $podcastId = (int)$_GET['id'];
    $podcast = $db->selectOne("SELECT p.*, c.name as category_name 
                            FROM podcasts p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.id = ?", [$podcastId]);
    
    include '../includes/header.php';
    
    if (!$podcast) {
        echo '<div class="container mt-5"><div class="alert alert-danger">Podcast không tồn tại!</div></div>';
        include '../includes/footer.php';
        exit;
    }
    
    // Hiển thị chi tiết podcast
    ?>
    <div class="container mt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= USER_URL ?>podcasts.php">Podcast</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($podcast['title']) ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-4">
                <?php if (!empty($podcast['cover_image'])): ?>
                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $podcast['cover_image'] ?>" 
                     alt="<?= htmlspecialchars($podcast['title']) ?>" class="img-fluid rounded shadow mb-4">
                <?php else: ?>
                <div class="bg-warning text-white p-5 text-center rounded mb-4">
                    <i class="fas fa-podcast fa-5x mb-3"></i>
                    <h5>Không có ảnh bìa</h5>
                </div>
                <?php endif; ?>
                
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Nghe Podcast</h5>
                        <div class="media-player">
                            <?php if (!empty($podcast['audio_file'])): ?>
                            <audio id="audioPlayer" controls class="w-100 mb-3">
                                <source src="<?= BASE_URL ?>assets/uploads/podcasts/<?= $podcast['audio_file'] ?>" type="audio/mpeg">
                                Trình duyệt của bạn không hỗ trợ phát audio.
                            </audio>
                            
                            <div class="d-flex justify-content-between">
                                <button onclick="document.getElementById('audioPlayer').play()" class="btn btn-sm btn-light">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button onclick="document.getElementById('audioPlayer').pause()" class="btn btn-sm btn-light">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <button onclick="document.getElementById('audioPlayer').volume += 0.1" class="btn btn-sm btn-light">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <button onclick="document.getElementById('audioPlayer').volume -= 0.1" class="btn btn-sm btn-light">
                                    <i class="fas fa-volume-down"></i>
                                </button>
                                <button onclick="document.getElementById('audioPlayer').currentTime = 0" class="btn btn-sm btn-light">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-light">
                                Không có file audio cho podcast này.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($podcast['category_name'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Thể loại</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?= USER_URL ?>podcasts.php?category=<?= $podcast['category_id'] ?>" class="btn btn-outline-warning">
                            <?= htmlspecialchars($podcast['category_name']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="mb-3"><?= htmlspecialchars($podcast['title']) ?></h1>
                        <h5 class="text-muted mb-4">Host: <?= htmlspecialchars($podcast['author']) ?></h5>
                        
                        <div class="mb-4">
                            <span class="badge badge-warning mr-2">Podcast</span>
                            <?php if (!empty($podcast['category_name'])): ?>
                            <span class="badge badge-secondary"><?= htmlspecialchars($podcast['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Mô tả</h5>
                            <?php if (!empty($podcast['description'])): ?>
                            <p class="text-justify"><?= nl2br(htmlspecialchars($podcast['description'])) ?></p>
                            <?php else: ?>
                            <p class="text-muted">Không có mô tả.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <h5>Thông tin khác</h5>
                            <ul class="list-unstyled">
                                <li><strong>Ngày thêm:</strong> <?= date('d/m/Y', strtotime($podcast['created_at'])) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Podcast cùng tác giả -->
                <?php
                $authorPodcasts = $db->select(
                    "SELECT * FROM podcasts 
                     WHERE author = ? AND id != ? 
                     ORDER BY created_at DESC LIMIT 4",
                    [$podcast['author'], $podcast['id']]
                );
                
                if (!empty($authorPodcasts)):
                ?>
                <div class="mt-5">
                    <h4 class="mb-4">Podcast khác của <?= htmlspecialchars($podcast['author']) ?></h4>
                    <div class="row">
                        <?php foreach ($authorPodcasts as $authorPodcast): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <?php if (!empty($authorPodcast['cover_image'])): ?>
                                        <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $authorPodcast['cover_image'] ?>" 
                                             alt="<?= htmlspecialchars($authorPodcast['title']) ?>" class="img-fluid h-100" style="object-fit: cover;">
                                        <?php else: ?>
                                        <div class="bg-warning text-white h-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-podcast fa-3x"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($authorPodcast['title']) ?></h5>
                                            <p class="card-text small">
                                                <?= substr(strip_tags(htmlspecialchars($authorPodcast['description'] ?? '')), 0, 80) ?>...
                                            </p>
                                            <a href="<?= USER_URL ?>podcasts.php?id=<?= $authorPodcast['id'] ?>" class="btn btn-sm btn-warning mt-2">
                                                <i class="fas fa-headphones"></i> Nghe
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Podcast cùng thể loại -->
                <?php
                if (!empty($podcast['category_id'])) {
                    $categoryPodcasts = $db->select(
                        "SELECT * FROM podcasts 
                         WHERE category_id = ? AND id != ? 
                         ORDER BY created_at DESC LIMIT 3",
                        [$podcast['category_id'], $podcast['id']]
                    );
                    
                    if (!empty($categoryPodcasts) && empty($authorPodcasts)):
                    ?>
                    <div class="mt-5">
                        <h4 class="mb-4">Podcast cùng thể loại</h4>
                        <div class="row">
                            <?php foreach ($categoryPodcasts as $categoryPodcast): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-img-top" style="height: 150px; overflow: hidden;">
                                        <?php if (!empty($categoryPodcast['cover_image'])): ?>
                                        <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $categoryPodcast['cover_image'] ?>" 
                                             alt="<?= htmlspecialchars($categoryPodcast['title']) ?>" class="img-fluid w-100 h-100" style="object-fit: cover;">
                                        <?php else: ?>
                                        <div class="bg-warning text-white h-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-podcast fa-3x"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($categoryPodcast['title']) ?></h5>
                                        <p class="card-text small text-muted">Host: <?= htmlspecialchars($categoryPodcast['author']) ?></p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="<?= USER_URL ?>podcasts.php?id=<?= $categoryPodcast['id'] ?>" class="btn btn-warning btn-sm btn-block">
                                            <i class="fas fa-podcast"></i> Nghe
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
    <?php
} else {
    // Hiển thị danh sách tất cả podcast
    $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
    $author = isset($_GET['author']) ? sanitize($_GET['author']) : null;
    
    // Xây dựng câu truy vấn
    $query = "SELECT p.*, c.name as category_name 
              FROM podcasts p 
              LEFT JOIN categories c ON p.category_id = c.id";
    $params = [];
    
    $whereConditions = [];
    if ($categoryId) {
        $whereConditions[] = "p.category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($search) {
        $whereConditions[] = "(p.title LIKE ? OR p.author LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($author) {
                $whereConditions[] = "p.author = ?";
        $params[] = $author;
    }
    
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(' AND ', $whereConditions);
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    $podcasts = $db->select($query, $params);
    $categories = $db->select("SELECT * FROM categories WHERE type = 'podcast' ORDER BY name");
    
    // Lấy danh sách host/tác giả duy nhất
    $authors = $db->select("SELECT DISTINCT author FROM podcasts ORDER BY author ASC");
    
    include '../includes/header.php';
    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Tìm kiếm</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= USER_URL ?>podcasts.php" method="get">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm podcast..." 
                                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            </div>
                            <button type="submit" class="btn btn-warning btn-block">Tìm kiếm</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Thể loại</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item <?= !isset($_GET['category']) ? 'active' : '' ?>">
                                <a href="<?= USER_URL ?>podcasts.php" class="<?= !isset($_GET['category']) ? 'text-white' : 'text-dark' ?>">
                                    Tất cả thể loại
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li class="list-group-item <?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'active' : '' ?>">
                                <a href="<?= USER_URL ?>podcasts.php?category=<?= $category['id'] ?>" 
                                   class="<?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'text-white' : 'text-dark' ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <?php if (!empty($authors)): ?>
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Host / Tác giả</h5>
                    </div>
                    <div class="card-body">
                        <div class="author-list" style="max-height: 300px; overflow-y: auto;">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item <?= !isset($_GET['author']) ? 'active' : '' ?>">
                                    <a href="<?= USER_URL ?>podcasts.php" class="<?= !isset($_GET['author']) ? 'text-white' : 'text-dark' ?>">
                                        Tất cả tác giả
                                    </a>
                                </li>
                                <?php foreach ($authors as $authorItem): ?>
                                <li class="list-group-item <?= isset($_GET['author']) && $_GET['author'] == $authorItem['author'] ? 'active' : '' ?>">
                                    <a href="<?= USER_URL ?>podcasts.php?author=<?= urlencode($authorItem['author']) ?>" 
                                       class="<?= isset($_GET['author']) && $_GET['author'] == $authorItem['author'] ? 'text-white' : 'text-dark' ?>">
                                        <?= htmlspecialchars($authorItem['author']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-9">
                <h2 class="mb-4">Thư viện Podcast</h2>
                
                <?php if (isset($_GET['search']) || isset($_GET['category']) || isset($_GET['author'])): ?>
                <div class="mb-4">
                    <h6>
                        <?php if (isset($_GET['search'])): ?>
                        Kết quả tìm kiếm cho: <span class="text-warning">"<?= htmlspecialchars($_GET['search']) ?>"</span>
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
                        Thể loại: <span class="text-warning"><?= htmlspecialchars($categoryName) ?></span>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['author'])): ?>
                        Tác giả/Host: <span class="text-warning"><?= htmlspecialchars($_GET['author']) ?></span>
                        <?php endif; ?>
                    </h6>
                    
                    <a href="<?= USER_URL ?>podcasts.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if (empty($podcasts)): ?>
                <div class="alert alert-info">
                    Không có podcast nào được tìm thấy.
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($podcasts as $podcast): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="row no-gutters">
                                <div class="col-md-4">
                                    <?php if (!empty($podcast['cover_image'])): ?>
                                    <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $podcast['cover_image'] ?>" 
                                         alt="<?= htmlspecialchars($podcast['title']) ?>" 
                                         class="img-fluid h-100" style="object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-warning text-white h-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-podcast fa-3x"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($podcast['title']) ?></h5>
                                        <p class="card-text small text-muted">Host: <?= htmlspecialchars($podcast['author']) ?></p>
                                        
                                        <?php if (!empty($podcast['category_name'])): ?>
                                        <p class="card-text small">
                                            <span class="badge badge-warning"><?= htmlspecialchars($podcast['category_name']) ?></span>
                                        </p>
                                        <?php endif; ?>
                                        
                                        <p class="card-text small">
                                            <?= substr(strip_tags(htmlspecialchars($podcast['description'] ?? '')), 0, 100) ?>...
                                        </p>
                                        
                                        <?php if (!empty($podcast['audio_file'])): ?>
                                        <audio controls class="w-100 mt-2">
                                            <source src="<?= BASE_URL ?>assets/uploads/podcasts/<?= $podcast['audio_file'] ?>" type="audio/mpeg">
                                            Trình duyệt của bạn không hỗ trợ phát audio.
                                        </audio>
                                        <?php endif; ?>
                                        
                                        <a href="<?= USER_URL ?>podcasts.php?id=<?= $podcast['id'] ?>" class="btn btn-sm btn-warning mt-2">
                                            <i class="fas fa-info-circle"></i> Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

include '../includes/footer.php';
?>