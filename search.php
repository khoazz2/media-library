<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/database.php';

$db = new Database();

// Lấy từ khóa tìm kiếm
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Nếu không có từ khóa, chuyển về trang chủ
if (empty($search)) {
    header('Location: ' . BASE_URL);
    exit;
}

// Tìm kiếm trong các bảng
$books = $db->select(
    "SELECT *, 'book' as type FROM books 
     WHERE title LIKE ? OR author LIKE ? OR description LIKE ?",
    ["%$search%", "%$search%", "%$search%"]
);

$stories = $db->select(
    "SELECT *, 'story' as type FROM stories 
     WHERE title LIKE ? OR author LIKE ? OR description LIKE ?",
    ["%$search%", "%$search%", "%$search%"]
);

$musics = $db->select(
    "SELECT *, 'music' as type FROM musics 
     WHERE title LIKE ? OR artist LIKE ? OR description LIKE ?",
    ["%$search%", "%$search%", "%$search%"]
);

$podcasts = $db->select(
    "SELECT *, 'podcast' as type FROM podcasts 
     WHERE title LIKE ? OR author LIKE ? OR description LIKE ?",
    ["%$search%", "%$search%", "%$search%"]
);

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="search-header mb-4">
        <h2>Kết quả tìm kiếm</h2>
        <p class="text-muted">Tìm thấy kết quả cho: "<?= htmlspecialchars($search) ?>"</p>
    </div>

    <!-- Hiển thị kết quả sách -->
    <?php if (!empty($books)): ?>
    <div class="search-section mb-5">
        <h3 class="section-title">Sách (<?= count($books) ?>)</h3>
        <div class="row">
            <?php foreach ($books as $book): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-img-top book-cover">
                        <?php if (!empty($book['cover_image'])): ?>
                        <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $book['cover_image'] ?>" 
                             alt="<?= htmlspecialchars($book['title']) ?>" class="img-fluid">
                        <?php else: ?>
                        <div class="no-cover text-center py-5 bg-light">
                            <i class="fas fa-book fa-3x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($book['author']) ?></p>
                        <a href="<?= USER_URL ?>books.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-book-open"></i> Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hiển thị kết quả truyện -->
    <?php if (!empty($stories)): ?>
    <div class="search-section mb-5">
        <h3 class="section-title">Truyện (<?= count($stories) ?>)</h3>
        <div class="row">
            <?php foreach ($stories as $story): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-img-top story-cover">
                        <?php if (!empty($story['cover_image'])): ?>
                        <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $story['cover_image'] ?>" 
                             alt="<?= htmlspecialchars($story['title']) ?>" class="img-fluid">
                        <?php else: ?>
                        <div class="no-cover text-center py-5 bg-light">
                            <i class="fas fa-book-open fa-3x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($story['title']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($story['author']) ?></p>
                        <a href="<?= USER_URL ?>stories.php?id=<?= $story['id'] ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-book-reader"></i> Đọc truyện
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hiển thị kết quả âm nhạc -->
    <?php if (!empty($musics)): ?>
    <div class="search-section mb-5">
        <h3 class="section-title">Âm nhạc (<?= count($musics) ?>)</h3>
        <div class="row">
            <?php foreach ($musics as $music): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-img-top music-cover">
                        <?php if (!empty($music['cover_image'])): ?>
                        <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $music['cover_image'] ?>" 
                             alt="<?= htmlspecialchars($music['title']) ?>" class="img-fluid">
                        <?php else: ?>
                        <div class="no-cover text-center py-5 bg-light">
                            <i class="fas fa-music fa-3x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($music['title']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($music['artist']) ?></p>
                        <?php if (!empty($music['audio_file'])): ?>
                        <audio class="w-100 mb-2" controls>
                            <source src="<?= BASE_URL ?>assets/uploads/musics/<?= $music['audio_file'] ?>" type="audio/mpeg">
                        </audio>
                        <?php endif; ?>
                        <a href="<?= USER_URL ?>musics.php?id=<?= $music['id'] ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-headphones"></i> Chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hiển thị kết quả podcast -->
    <?php if (!empty($podcasts)): ?>
    <div class="search-section mb-5">
        <h3 class="section-title">Podcast (<?= count($podcasts) ?>)</h3>
        <div class="row">
            <?php foreach ($podcasts as $podcast): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-img-top podcast-cover">
                        <?php if (!empty($podcast['cover_image'])): ?>
                        <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $podcast['cover_image'] ?>" 
                             alt="<?= htmlspecialchars($podcast['title']) ?>" class="img-fluid">
                        <?php else: ?>
                        <div class="no-cover text-center py-5 bg-light">
                            <i class="fas fa-podcast fa-3x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($podcast['title']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($podcast['author']) ?></p>
                        <?php if (!empty($podcast['audio_file'])): ?>
                        <audio class="w-100 mb-2" controls>
                            <source src="<?= BASE_URL ?>assets/uploads/podcasts/<?= $podcast['audio_file'] ?>" type="audio/mpeg">
                        </audio>
                        <?php endif; ?>
                        <a href="<?= USER_URL ?>podcasts.php?id=<?= $podcast['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-podcast"></i> Chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($books) && empty($stories) && empty($musics) && empty($podcasts)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Không tìm thấy kết quả nào cho "<?= htmlspecialchars($search) ?>"
    </div>
    <?php endif; ?>
</div>

<style>
.search-header {
    background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.search-header h2 {
    margin-bottom: 10px;
}

.section-title {
    color: #333;
    font-size: 1.5rem;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eee;
}

.card {
    transition: transform 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.card:hover {
    transform: translateY(-5px);
}

.book-cover, .story-cover, .music-cover, .podcast-cover {
    height: 200px;
    overflow: hidden;
}

.book-cover img, .story-cover img, .music-cover img, .podcast-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-cover {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .search-header {
        padding: 20px;
    }
    
    .book-cover, .story-cover, .music-cover, .podcast-cover {
        height: 150px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>