<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Lấy nội dung mới nhất
$latestBooks = $db->select("SELECT * FROM books ORDER BY created_at DESC LIMIT 6");
$latestStories = $db->select("SELECT * FROM stories ORDER BY created_at DESC LIMIT 6");
$latestMusics = $db->select("SELECT * FROM musics ORDER BY created_at DESC LIMIT 8");
$latestPodcasts = $db->select("SELECT * FROM podcasts ORDER BY created_at DESC LIMIT 4");
$popularCategories = $db->select("SELECT id, name, type FROM categories LIMIT 10");

include '../includes/header.php';
?>

<div class="container mt-5">
    <!-- Banner chính -->
    <div class="jumbotron bg-primary text-white">
        <h1 class="display-4">Thư viện Media trực tuyến</h1>
        <p class="lead">Khám phá hàng ngàn tựa sách, truyện, âm nhạc và podcast tại một nơi duy nhất.</p>
        <hr class="my-4 bg-white">
        <p>Bắt đầu trải nghiệm đọc và nghe ngay hôm nay!</p>
        <div class="input-group mt-4">
            <input type="text" class="form-control" placeholder="Tìm kiếm sách, truyện, nhạc...">
            <div class="input-group-append">
                <button class="btn btn-light" type="button">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
        </div>
    </div>
    
    <!-- Danh mục phổ biến -->
    <section class="mb-5">
        <h2 class="mb-4">Danh mục nội dung</h2>
        <div class="row">
            <?php foreach ($popularCategories as $category): ?>
                <?php
                $bgColor = 'bg-secondary';
                $icon = 'folder';
                
                switch ($category['type']) {
                    case 'book':
                        $bgColor = 'bg-primary';
                        $icon = 'book';
                        $url = USER_URL . 'books.php?category=' . $category['id'];
                        break;
                    case 'story':
                        $bgColor = 'bg-success';
                        $icon = 'book-open';
                        $url = USER_URL . 'stories.php?category=' . $category['id'];
                        break;
                    case 'music':
                        $bgColor = 'bg-info';
                        $icon = 'music';
                        $url = USER_URL . 'musics.php?category=' . $category['id'];
                        break;
                    case 'podcast':
                        $bgColor = 'bg-warning';
                        $icon = 'podcast';
                        $url = USER_URL . 'podcasts.php?category=' . $category['id'];
                        break;
                }
                ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <a href="<?= $url ?>" class="text-decoration-none">
                        <div class="card h-100 <?= $bgColor ?> text-white">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-<?= $icon ?> fa-3x mb-3"></i>
                                <h5 class="card-title"><?= htmlspecialchars($category['name']) ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- Sách mới nhất -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sách mới nhất</h2>
            <a href="<?= USER_URL ?>books.php" class="btn btn-outline-primary">Xem tất cả <i class="fas fa-arrow-right ml-2"></i></a>
        </div>
        
        <div class="row">
            <?php if (empty($latestBooks)): ?>
                <div class="col-12">
                    <div class="alert alert-info">Chưa có sách nào được thêm vào.</div>
                </div>
            <?php else: ?>
                <?php foreach ($latestBooks as $book): ?>
                    <div class="col-md-2 col-sm-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-img-top book-cover">
                                <?php if (!empty($book['cover_image'])): ?>
                                    <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $book['cover_image'] ?>" 
                                         alt="<?= htmlspecialchars($book['title']) ?>" class="img-fluid">
                                <?php else: ?>
                                    <div class="no-cover bg-light">
                                        <i class="fas fa-book fa-3x text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title text-truncate" title="<?= htmlspecialchars($book['title']) ?>">
                                    <?= htmlspecialchars($book['title']) ?>
                                </h6>
                                <p class="card-text small text-muted text-truncate">
                                    <?= htmlspecialchars($book['author']) ?>
                                </p>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <a href="<?= USER_URL ?>books.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-primary btn-block">
                                    <i class="fas fa-book-open"></i> Đọc ngay
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Truyện mới nhất -->
    <section class="mb-5 py-4 bg-light rounded">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Truyện mới nhất</h2>
                <a href="<?= USER_URL ?>stories.php" class="btn btn-outline-success">Xem tất cả <i class="fas fa-arrow-right ml-2"></i></a>
            </div>
            
            <div class="row">
                <?php if (empty($latestStories)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">Chưa có truyện nào được thêm vào.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($latestStories as $story): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <?php if (!empty($story['cover_image'])): ?>
                                            <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $story['cover_image'] ?>" 
                                                 alt="<?= htmlspecialchars($story['title']) ?>" class="img-fluid h-100" style="object-fit: cover;">
                                        <?php else: ?>
                                            <div class="no-cover bg-light h-100 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-book-open fa-3x text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($story['title']) ?></h5>
                                            <p class="card-text small text-muted"><?= htmlspecialchars($story['author']) ?></p>
                                            <p class="card-text small">
                                                <?= substr(strip_tags(htmlspecialchars($story['description'] ?? '')), 0, 80) ?>...
                                            </p>
                                            <a href="<?= USER_URL ?>stories.php?id=<?= $story['id'] ?>" class="btn btn-sm btn-success mt-2">
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
    </section>
    
    <!-- Âm nhạc mới nhất -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Âm nhạc mới nhất</h2>
            <a href="<?= USER_URL ?>musics.php" class="btn btn-outline-info">Xem tất cả <i class="fas fa-arrow-right ml-2"></i></a>
        </div>
        
        <div class="row">
            <?php if (empty($latestMusics)): ?>
                <div class="col-12">
                    <div class="alert alert-info">Chưa có bài hát nào được thêm vào.</div>
                </div>
            <?php else: ?>
                <?php foreach ($latestMusics as $music): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php if (!empty($music['cover_image'])): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $music['cover_image'] ?>" 
                                     alt="<?= htmlspecialchars($music['title']) ?>" class="card-img-top">
                            <?php else: ?>
                                <div class="card-img-top bg-info text-white d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <i class="fas fa-music fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($music['title']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($music['artist']) ?></p>
                                
                                <?php if (!empty($music['audio_file'])): ?>
                                <audio controls class="w-100 mt-2">
                                    <source src="<?= BASE_URL ?>assets/uploads/musics/<?= $music['audio_file'] ?>" type="audio/mpeg">
                                    Trình duyệt của bạn không hỗ trợ phát audio.
                                </audio>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="<?= USER_URL ?>musics.php?id=<?= $music['id'] ?>" class="btn btn-sm btn-info btn-block">
                                    <i class="fas fa-headphones"></i> Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Podcast -->
    <section class="mb-5 py-4 bg-warning rounded">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-white">Podcast nổi bật</h2>
                <a href="<?= USER_URL ?>podcasts.php" class="btn btn-outline-light">Xem tất cả <i class="fas fa-arrow-right ml-2"></i></a>
            </div>
            
            <div class="row">
                <?php if (empty($latestPodcasts)): ?>
                    <div class="col-12">
                        <div class="alert alert-light">Chưa có podcast nào được thêm vào.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($latestPodcasts as $podcast): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <?php if (!empty($podcast['cover_image'])): ?>
                                            <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $podcast['cover_image'] ?>" 
                                                 alt="<?= htmlspecialchars($podcast['title']) ?>" class="img-fluid rounded-left" style="height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-dark text-white h-100 d-flex align-items-center justify-content-center rounded-left">
                                                <i class="fas fa-podcast fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($podcast['title']) ?></h5>
                                            <p class="card-text"><small class="text-muted"><?= htmlspecialchars($podcast['author']) ?></small></p>
                                            <p class="card-text">
                                                <?= substr(strip_tags(htmlspecialchars($podcast['description'] ?? '')), 0, 100) ?>...
                                            </p>
                                            
                                            <?php if (!empty($podcast['audio_file'])): ?>
                                            <div class="mt-3">
                                                <audio controls class="w-100">
                                                    <source src="<?= BASE_URL ?>assets/uploads/podcasts/<?= $podcast['audio_file'] ?>" type="audio/mpeg">
                                                    Trình duyệt của bạn không hỗ trợ phát audio.
                                                </audio>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <a href="<?= USER_URL ?>podcasts.php?id=<?= $podcast['id'] ?>" class="btn btn-warning mt-3">
                                                <i class="fas fa-info-circle"></i> Xem chi tiết
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
    </section>
</div>

<?php include '../includes/footer.php'; ?>
