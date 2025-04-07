<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Lấy thống kê tổng quát
$stats = [
    'books' => $db->selectOne("SELECT COUNT(*) as count FROM books")['count'] ?? 0,
    'stories' => $db->selectOne("SELECT COUNT(*) as count FROM stories")['count'] ?? 0,
    'musics' => $db->selectOne("SELECT COUNT(*) as count FROM musics")['count'] ?? 0,
    'podcasts' => $db->selectOne("SELECT COUNT(*) as count FROM podcasts")['count'] ?? 0,
    'radio_stations' => $db->selectOne("SELECT COUNT(*) as count FROM radio_stations")['count'] ?? 0,
    'categories' => $db->selectOne("SELECT COUNT(*) as count FROM categories")['count'] ?? 0
];

// Lấy danh sách nội dung mới nhất
$latestBooks = $db->select("SELECT * FROM books ORDER BY created_at DESC LIMIT 5");
$latestStories = $db->select("SELECT * FROM stories ORDER BY created_at DESC LIMIT 5");
$latestMusics = $db->select("SELECT * FROM musics ORDER BY created_at DESC LIMIT 5");

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Tổng quan hệ thống</h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Tổng số sách</h6>
                            <h2 class="mb-0"><?= $stats['books'] ?></h2>
                        </div>
                        <i class="fas fa-book fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= ADMIN_URL ?>books.php" class="text-white">
                        <small>Xem chi tiết <i class="fas fa-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Tổng số truyện</h6>
                            <h2 class="mb-0"><?= $stats['stories'] ?></h2>
                        </div>
                        <i class="fas fa-book-open fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= ADMIN_URL ?>stories.php" class="text-white">
                        <small>Xem chi tiết <i class="fas fa-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Tổng số bài hát</h6>
                            <h2 class="mb-0"><?= $stats['musics'] ?></h2>
                        </div>
                        <i class="fas fa-music fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= ADMIN_URL ?>musics.php" class="text-white">
                        <small>Xem chi tiết <i class="fas fa-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Tổng số podcast</h6>
                            <h2 class="mb-0"><?= $stats['podcasts'] ?></h2>
                        </div>
                        <i class="fas fa-podcast fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= ADMIN_URL ?>podcasts.php" class="text-white">
                        <small>Xem chi tiết <i class="fas fa-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Tổng số radio</h6>
                            <h2 class="mb-0"><?= $stats['radio_stations'] ?></h2>
                        </div>
                        <i class="fas fa-broadcast-tower fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= ADMIN_URL ?>radio.php" class="text-white">
                        <small>Xem chi tiết <i class="fas fa-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Tổng số danh mục</h6>
                            <h2 class="mb-0"><?= $stats['categories'] ?></h2>
                        </div>
                        <i class="fas fa-folder fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= ADMIN_URL ?>categories.php" class="text-white">
                        <small>Xem chi tiết <i class="fas fa-arrow-right"></i></small>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Sách mới nhất</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($latestBooks)): ?>
                        <li class="list-group-item text-muted">Chưa có sách nào.</li>
                        <?php else: ?>
                        <?php foreach ($latestBooks as $book): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($book['title']) ?></h6>
                                    <small class="text-muted">Tác giả: <?= htmlspecialchars($book['author']) ?></small>
                                </div>
                                <a href="<?= ADMIN_URL ?>books.php?edit=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-footer bg-white">
                    <a href="<?= ADMIN_URL ?>books.php" class="btn btn-primary btn-sm">
                        Xem tất cả sách <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Truyện mới nhất</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($latestStories)): ?>
                        <li class="list-group-item text-muted">Chưa có truyện nào.</li>
                        <?php else: ?>
                        <?php foreach ($latestStories as $story): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($story['title']) ?></h6>
                                    <small class="text-muted">Tác giả: <?= htmlspecialchars($story['author']) ?></small>
                                </div>
                                <a href="<?= ADMIN_URL ?>stories.php?edit=<?= $story['id'] ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-footer bg-white">
                    <a href="<?= ADMIN_URL ?>stories.php" class="btn btn-success btn-sm">
                        Xem tất cả truyện <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Bài hát mới nhất</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Tiêu đề</th>
                                    <th>Nghệ sĩ</th>
                                    <th>Ngày thêm</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($latestMusics)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Chưa có bài hát nào.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($latestMusics as $music): ?>
                                <tr>
                                    <td><?= htmlspecialchars($music['title']) ?></td>
                                    <td><?= htmlspecialchars($music['artist']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($music['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= ADMIN_URL ?>musics.php?edit=<?= $music['id'] ?>" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <a href="<?= ADMIN_URL ?>musics.php" class="btn btn-info btn-sm">
                        Xem tất cả bài hát <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
