<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Xem chi tiết một bài hát
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $musicId = (int)$_GET['id'];
    $music = $db->selectOne("SELECT m.*, c.name as category_name 
                            FROM musics m 
                            LEFT JOIN categories c ON m.category_id = c.id 
                            WHERE m.id = ?", [$musicId]);
    
    include '../includes/header.php';
    
    if (!$music) {
        echo '<div class="container mt-5"><div class="alert alert-danger">Bài hát không tồn tại!</div></div>';
        include '../includes/footer.php';
        exit;
    }
    
    // Hiển thị chi tiết bài hát
    ?>
    <div class="container mt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= USER_URL ?>musics.php">Âm nhạc</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($music['title']) ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-4">
                <?php if (!empty($music['cover_image'])): ?>
                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $music['cover_image'] ?>" 
                     alt="<?= htmlspecialchars($music['title']) ?>" class="img-fluid rounded shadow mb-4">
                <?php else: ?>
                <div class="bg-info text-white p-5 text-center rounded mb-4">
                    <i class="fas fa-music fa-5x mb-3"></i>
                    <h5>Không có ảnh bìa</h5>
                </div>
                <?php endif; ?>
                
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Phát nhạc</h5>
                        <div class="media-player">
                            <?php if (!empty($music['audio_file'])): ?>
                            <audio id="audioPlayer" controls class="w-100 mb-3">
                                <source src="<?= BASE_URL ?>assets/uploads/musics/<?= $music['audio_file'] ?>" type="audio/mpeg">
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
                                Không có file audio cho bài hát này.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($music['category_name'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Thể loại</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?= USER_URL ?>musics.php?category=<?= $music['category_id'] ?>" class="btn btn-outline-info">
                            <?= htmlspecialchars($music['category_name']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="mb-3"><?= htmlspecialchars($music['title']) ?></h1>
                        <h5 class="text-muted mb-4">Nghệ sĩ: <?= htmlspecialchars($music['artist']) ?></h5>
                        
                        <div class="mb-4">
                            <span class="badge badge-info mr-2">Âm nhạc</span>
                            <?php if (!empty($music['category_name'])): ?>
                            <span class="badge badge-secondary"><?= htmlspecialchars($music['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Mô tả</h5>
                            <?php if (!empty($music['description'])): ?>
                            <p class="text-justify"><?= nl2br(htmlspecialchars($music['description'])) ?></p>
                            <?php else: ?>
                            <p class="text-muted">Không có mô tả.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <h5>Thông tin khác</h5>
                            <ul class="list-unstyled">
                                <li><strong>Ngày thêm:</strong> <?= date('d/m/Y', strtotime($music['created_at'])) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Bài hát cùng nghệ sĩ -->
                <?php
                $artistMusics = $db->select(
                    "SELECT * FROM musics 
                     WHERE artist = ? AND id != ? 
                     ORDER BY created_at DESC LIMIT 4",
                    [$music['artist'], $music['id']]
                );
                
                if (!empty($artistMusics)):
                ?>
                <div class="mt-5">
                    <h4 class="mb-4">Bài hát khác của <?= htmlspecialchars($music['artist']) ?></h4>
                    <div class="row">
                        <?php foreach ($artistMusics as $artistMusic): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-img-top" style="height: 120px; overflow: hidden;">
                                    <?php if (!empty($artistMusic['cover_image'])): ?>
                                    <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $artistMusic['cover_image'] ?>" 
                                         alt="<?= htmlspecialchars($artistMusic['title']) ?>" class="img-fluid w-100 h-100" style="object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-info text-white h-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-music fa-3x"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title text-truncate"><?= htmlspecialchars($artistMusic['title']) ?></h6>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="<?= USER_URL ?>musics.php?id=<?= $artistMusic['id'] ?>" class="btn btn-info btn-sm btn-block">
                                        <i class="fas fa-headphones"></i> Nghe
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Bài hát cùng thể loại -->
                <?php
                if (!empty($music['category_id'])) {
                    $categoryMusics = $db->select(
                        "SELECT * FROM musics 
                         WHERE category_id = ? AND id != ? 
                         ORDER BY created_at DESC LIMIT 3",
                        [$music['category_id'], $music['id']]
                    );
                    
                    if (!empty($categoryMusics) && empty($artistMusics)):
                    ?>
                    <div class="mt-5">
                        <h4 class="mb-4">Bài hát cùng thể loại</h4>
                        <div class="row">
                            <?php foreach ($categoryMusics as $categoryMusic): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-img-top" style="height: 150px; overflow: hidden;">
                                        <?php if (!empty($categoryMusic['cover_image'])): ?>
                                        <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $categoryMusic['cover_image'] ?>" 
                                             alt="<?= htmlspecialchars($categoryMusic['title']) ?>" class="img-fluid w-100 h-100" style="object-fit: cover;">
                                        <?php else: ?>
                                        <div class="bg-info text-white h-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-music fa-3x"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($categoryMusic['title']) ?></h5>
                                        <p class="card-text small text-muted">Nghệ sĩ: <?= htmlspecialchars($categoryMusic['artist']) ?></p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="<?= USER_URL ?>musics.php?id=<?= $categoryMusic['id'] ?>" class="btn btn-info btn-sm btn-block">
                                            <i class="fas fa-headphones"></i> Nghe
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
    // Hiển thị danh sách tất cả bài hát
    $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
    $artist = isset($_GET['artist']) ? sanitize($_GET['artist']) : null;
    
    // Xây dựng câu truy vấn
    $query = "SELECT m.*, c.name as category_name 
              FROM musics m 
              LEFT JOIN categories c ON m.category_id = c.id";
    $params = [];
    
    $whereConditions = [];
    if ($categoryId) {
        $whereConditions[] = "m.category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($search) {
        $whereConditions[] = "(m.title LIKE ? OR m.artist LIKE ? OR m.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($artist) {
        $whereConditions[] = "m.artist = ?";
        $params[] = $artist;
    }
    
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(' AND ', $whereConditions);
    }
    
    $query .= " ORDER BY m.created_at DESC";
    
    $musics = $db->select($query, $params);
    $categories = $db->select("SELECT * FROM categories WHERE type = 'music' ORDER BY name");
    
    // Lấy danh sách nghệ sĩ duy nhất
    $artists = $db->select("SELECT DISTINCT artist FROM musics ORDER BY artist ASC");
    
    include '../includes/header.php';
    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Tìm kiếm</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= USER_URL ?>musics.php" method="get">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm bài hát..." 
                                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            </div>
                            <button type="submit" class="btn btn-info btn-block">Tìm kiếm</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Thể loại</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item <?= !isset($_GET['category']) ? 'active' : '' ?>">
                                <a href="<?= USER_URL ?>musics.php" class="<?= !isset($_GET['category']) ? 'text-white' : 'text-dark' ?>">
                                    Tất cả thể loại
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li class="list-group-item <?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'active' : '' ?>">
                                <a href="<?= USER_URL ?>musics.php?category=<?= $category['id'] ?>" 
                                   class="<?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'text-white' : 'text-dark' ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Nghệ sĩ</h5>
                    </div>
                    <div class="card-body">
                        <div class="artist-list" style="max-height: 300px; overflow-y: auto;">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item <?= !isset($_GET['artist']) ? 'active' : '' ?>">
                                    <a href="<?= USER_URL ?>musics.php" class="<?= !isset($_GET['artist']) ? 'text-white' : 'text-dark' ?>">
                                        Tất cả nghệ sĩ
                                    </a>
                                </li>
                                <?php foreach ($artists as $artistItem): ?>
                                <li class="list-group-item <?= isset($_GET['artist']) && $_GET['artist'] == $artistItem['artist'] ? 'active' : '' ?>">
                                    <a href="<?= USER_URL ?>musics.php?artist=<?= urlencode($artistItem['artist']) ?>" 
                                       class="<?= isset($_GET['artist']) && $_GET['artist'] == $artistItem['artist'] ? 'text-white' : 'text-dark' ?>">
                                        <?= htmlspecialchars($artistItem['artist']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <h2 class="mb-4">Thư viện âm nhạc</h2>
                
                <?php if (isset($_GET['search']) || isset($_GET['category']) || isset($_GET['artist'])): ?>
                <div class="mb-4">
                    <h6>
                        <?php if (isset($_GET['search'])): ?>
                        Kết quả tìm kiếm cho: <span class="text-info">"<?= htmlspecialchars($_GET['search']) ?>"</span>
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
                        Thể loại: <span class="text-info"><?= htmlspecialchars($categoryName) ?></span>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['artist'])): ?>
                        Nghệ sĩ: <span class="text-info"><?= htmlspecialchars($_GET['artist']) ?></span>
                        <?php endif; ?>
                    </h6>
                    
                    <a href="<?= USER_URL ?>musics.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if (empty($musics)): ?>
                <div class="alert alert-info">
                    Không có bài hát nào được tìm thấy.
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($musics as $music): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-img-top" style="height: 180px; overflow: hidden;">
                                <?php if (!empty($music['cover_image'])): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $music['cover_image'] ?>" 
                                     alt="<?= htmlspecialchars($music['title']) ?>" class="img-fluid w-100 h-100" style="object-fit: cover;">
                                <?php else: ?>
                                <div class="bg-info text-white h-100 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-music fa-3x"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($music['title']) ?></h5>
                                <p class="card-text">
                                    <a href="<?= USER_URL ?>musics.php?artist=<?= urlencode($music['artist']) ?>" class="text-muted">
                                        <?= htmlspecialchars($music['artist']) ?>
                                    </a>
                                </p>
                                
                                <?php if (!empty($music['category_name'])): ?>
                                <p class="card-text">
                                    <span class="badge badge-info"><?= htmlspecialchars($music['category_name']) ?></span>
                                </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($music['audio_file'])): ?>
                                <audio controls class="w-100 mt-2">
                                    <source src="<?= BASE_URL ?>assets/uploads/musics/<?= $music['audio_file'] ?>" type="audio/mpeg">
                                    Trình duyệt của bạn không hỗ trợ phát audio.
                                </audio>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="<?= USER_URL ?>musics.php?id=<?= $music['id'] ?>" class="btn btn-info btn-sm btn-block">
                                    <i class="fas fa-headphones"></i> Chi tiết
                                </a>
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
