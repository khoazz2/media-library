<?php
require_once 'config.php';
require_once 'database/database.php';

// Xử lý routing cơ bản
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/media_library/';  // Điều chỉnh nếu cần thiết
$path = str_replace($basePath, '', $requestUri);
$path = strtok($path, '?');  // Loại bỏ query string

// Phân tích URI để xác định route
$segments = explode('/', trim($path, '/'));
$route = $segments[0] ?? '';

// Điều hướng dựa trên route
if ($route === 'admin') {
    $adminRoute = $segments[1] ?? 'index';
    $adminFile = 'admin/' . ($adminRoute === '' ? 'index' : $adminRoute) . '.php';
    
    if (file_exists($adminFile)) {
        include $adminFile;
    } else {
        include 'admin/index.php';
    }
} elseif ($route === 'user') {
    $userRoute = $segments[1] ?? 'index';
    $userFile = 'user/' . ($userRoute === '' ? 'index' : $userRoute) . '.php';
    
    if (file_exists($userFile)) {
        include $userFile;
    } else {
        include 'user/index.php';
    }
} else {
    // Trang chủ
    include 'includes/header.php';

    // Lấy dữ liệu cần thiết
    $db = new Database();
    $books = $db->select("SELECT * FROM books ORDER BY created_at DESC LIMIT 6");
    $stories = $db->select("SELECT * FROM stories ORDER BY created_at DESC LIMIT 6");
    $musics = $db->select("SELECT * FROM musics ORDER BY created_at DESC LIMIT 8");
    $podcasts = $db->select("SELECT * FROM podcasts ORDER BY created_at DESC LIMIT 4");
?>

<!-- Hero Section hiện đại -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-text-column">
                <h1 class="hero-title">Khám phá <span class="highlight">Tri thức</span> và <span class="highlight">Giải trí</span></h1>
                <p class="hero-subtitle">Thư viện đa phương tiện - Sách, truyện, nhạc và podcast trong tầm tay bạn</p>
                <div class="search-box">
                    <form id="heroSearchForm" action="<?= USER_URL ?>search.php" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" placeholder="Tìm kiếm sách, truyện, nhạc...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="hero-buttons">
                    <a href="<?= USER_URL ?>books.php" class="btn btn-primary btn-lg">Khám phá ngay</a>
                    <a href="#categories" class="btn btn-outline-light btn-lg">Xem danh mục</a>
                </div>
            </div>
            <div class="col-lg-6 hero-image-column">
                <div class="hero-image-wrap">
                    <img src="https://achaubook.com/wp-content/uploads/2019/11/doc-sach-la-gi-3.jpg" alt="Thư viện Media" class="hero-image">
                    <div class="floating-item item-book">
                        <i class="fas fa-book fa-2x"></i>
                    </div>
                    <div class="floating-item item-music">
                        <i class="fas fa-music fa-2x"></i>
                    </div>
                    <div class="floating-item item-podcast">
                        <i class="fas fa-podcast fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section: Danh mục nổi bật -->
<section class="categories-section py-5" id="categories">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Danh mục nổi bật</h2>
            <p class="section-subtitle">Khám phá theo sở thích của bạn</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <a href="<?= USER_URL ?>books.php" class="category-card category-books">
                    <div class="category-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3 class="category-title">Sách</h3>
                    <p class="category-description">Đọc và nghe hàng ngàn tựa sách</p>
                    <div class="category-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <a href="<?= USER_URL ?>stories.php" class="category-card category-stories">
                    <div class="category-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 class="category-title">Truyện</h3>
                    <p class="category-description">Đắm chìm trong thế giới truyện</p>
                    <div class="category-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <a href="<?= USER_URL ?>musics.php" class="category-card category-music">
                    <div class="category-icon">
                        <i class="fas fa-music"></i>
                    </div>
                    <h3 class="category-title">Âm nhạc</h3>
                    <p class="category-description">Thưởng thức âm nhạc đa dạng</p>
                    <div class="category-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <a href="<?= USER_URL ?>podcasts.php" class="category-card category-podcasts">
                    <div class="category-icon">
                        <i class="fas fa-podcast"></i>
                    </div>
                    <h3 class="category-title">Podcast</h3>
                    <p class="category-description">Nghe podcast chất lượng cao</p>
                    <div class="category-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Section: Sách mới nhất -->
<section class="books-section py-5 section-with-wave">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title">Sách mới nhất</h2>
                <p class="section-subtitle">Khám phá những cuốn sách mới nhất của thư viện</p>
            </div>
            <a href="<?= USER_URL ?>books.php" class="btn btn-outline-primary view-all-btn">
                Xem tất cả <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="book-slider">
            <div class="row">
                <?php
                if (empty($books)): 
                ?>
                <div class="col-12">
                    <div class="alert alert-info">Chưa có sách nào được thêm vào.</div>
                </div>
                <?php 
                else:
                    foreach ($books as $index => $book): 
                        $coverImage = !empty($book['cover_image']) ? BASE_URL . 'assets/uploads/covers/' . $book['cover_image'] : BASE_URL . 'assets/images/default-book.jpg';
                ?>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
                    <div class="book-card">
                        <div class="book-card-cover">
                            <img src="<?= $coverImage ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="img-fluid">
                            <div class="book-card-overlay">
                                <a href="<?= USER_URL ?>books.php?id=<?= $book['id'] ?>" class="btn btn-light btn-sm quick-view">
                                    <i class="fas fa-eye"></i> Xem nhanh
                                </a>
                            </div>
                        </div>
                        <div class="book-card-body">
                            <h5 class="book-title" title="<?= htmlspecialchars($book['title']) ?>"><?= htmlspecialchars($book['title']) ?></h5>
                            <p class="book-author"><?= htmlspecialchars($book['author']) ?></p>
                            <a href="<?= USER_URL ?>books.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm btn-block">Chi tiết</a>
                        </div>
                    </div>
                </div>
                <?php 
                    endforeach; 
                endif; 
                ?>
            </div>
        </div>
    </div>
</section>

<!-- Section: Âm nhạc -->
<section class="music-section py-5 section-gradient">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title">Âm nhạc nổi bật</h2>
                <p class="section-subtitle">Thưởng thức âm nhạc đa dạng</p>
            </div>
            <a href="<?= USER_URL ?>musics.php" class="btn btn-outline-light view-all-btn">
                Xem tất cả <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="row">
            <?php
            if (empty($musics)): 
            ?>
            <div class="col-12">
                <div class="alert alert-info">Chưa có bài hát nào được thêm vào.</div>
            </div>
            <?php 
            else:
                // Hiển thị 4 bài hát đầu tiên
                $displayMusics = array_slice($musics, 0, 4);
                foreach ($displayMusics as $music): 
                    $coverImage = !empty($music['cover_image']) ? BASE_URL . 'assets/uploads/covers/' . $music['cover_image'] : BASE_URL . 'assets/images/default-music.jpg';
            ?>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up">
                <div class="music-card">
                    <div class="music-card-cover">
                        <img src="<?= $coverImage ?>" alt="<?= htmlspecialchars($music['title']) ?>">
                        <div class="music-play-btn" data-audio="<?= BASE_URL ?>assets/uploads/musics/<?= $music['audio_file'] ?>">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                    <div class="music-card-body">
                        <h5 class="music-title"><?= htmlspecialchars($music['title']) ?></h5>
                        <p class="music-artist"><?= htmlspecialchars($music['artist']) ?></p>
                        
                        <?php if (!empty($music['audio_file'])): ?>
                        <div class="music-player">
                            <audio class="w-100" data-music-id="<?= $music['id'] ?>">
                                <source src="<?= BASE_URL ?>assets/uploads/musics/<?= $music['audio_file'] ?>" type="audio/mpeg">
                            </audio>
                            <div class="audio-controls mt-2 d-flex justify-content-between">
                                <button class="btn btn-sm btn-light play-btn">
                                    <i class="fas fa-play"></i>
                                </button>
                                <div class="progress flex-grow-1 mx-2" style="height: 5px; margin-top: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 0%"></div>
                                </div>
                                <a href="<?= USER_URL ?>musics.php?id=<?= $music['id'] ?>" class="btn btn-sm btn-light">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <a href="<?= USER_URL ?>musics.php?id=<?= $music['id'] ?>" class="btn btn-info btn-sm btn-block">Chi tiết</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
                
                // Hiển thị các bài hát còn lại dưới dạng list
                if (count($musics) > 4):
            ?>
            <div class="col-lg-12 mt-4">
                <div class="more-music-list">
                    <div class="row">
                        <?php 
                        $remainingMusics = array_slice($musics, 4);
                        foreach ($remainingMusics as $music): 
                            $coverImage = !empty($music['cover_image']) ? BASE_URL . 'assets/uploads/covers/' . $music['cover_image'] : BASE_URL . 'assets/images/default-music.jpg';
                        ?>
                        <div class="col-lg-6 mb-2">
                            <div class="music-list-item d-flex align-items-center p-2 rounded">
                                <img src="<?= $coverImage ?>" alt="<?= htmlspecialchars($music['title']) ?>" class="music-list-img mr-3">
                                <div class="music-list-info flex-grow-1">
                                    <h6 class="mb-0"><?= htmlspecialchars($music['title']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($music['artist']) ?></small>
                                </div>
                                <a href="<?= USER_URL ?>musics.php?id=<?= $music['id'] ?>" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-headphones"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php 
                endif;
            endif; 
            ?>
        </div>
    </div>
</section>

<!-- Section: Podcasts & Truyện mới nhất -->
<section class="featured-section py-5">
    <div class="container">
        <div class="row">
            <!-- Podcasts -->
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="section-header d-flex justify-content-between align-items-center mb-4">
                    <h3 class="section-title">Podcasts mới nhất</h3>
                    <a href="<?= USER_URL ?>podcasts.php" class="btn btn-sm btn-outline-warning">
                        Xem tất cả <i class="fas fa-angle-right"></i>
                    </a>
                </div>
                
                <?php if (empty($podcasts)): ?>
                <div class="alert alert-info">Chưa có podcast nào được thêm vào.</div>
                <?php else: ?>
                <div class="podcast-list">
                    <?php 
                    foreach ($podcasts as $podcast): 
                        $coverImage = !empty($podcast['cover_image']) ? BASE_URL . 'assets/uploads/covers/' . $podcast['cover_image'] : BASE_URL . 'assets/images/default-podcast.jpg';
                    ?>
                    <div class="podcast-item" data-aos="fade-right">
                        <div class="row no-gutters">
                            <div class="col-3">
                                <img src="<?= $coverImage ?>" alt="<?= htmlspecialchars($podcast['title']) ?>" class="img-fluid rounded">
                            </div>
                            <div class="col-9">
                                <div class="podcast-content p-3">
                                    <h5 class="podcast-title"><?= htmlspecialchars($podcast['title']) ?></h5>
                                    <p class="podcast-author"><?= htmlspecialchars($podcast['author']) ?></p>
                                    <?php if (!empty($podcast['audio_file'])): ?>
                                    <audio class="podcast-audio d-none">
                                        <source src="<?= BASE_URL ?>assets/uploads/podcasts/<?= $podcast['audio_file'] ?>" type="audio/mpeg">
                                    </audio>
                                    <button class="btn btn-sm btn-warning podcast-play-btn" data-index="<?= $podcast['id'] ?>">
                                        <i class="fas fa-play"></i> Nghe
                                    </button>
                                    <?php endif; ?>
                                    <a href="<?= USER_URL ?>podcasts.php?id=<?= $podcast['id'] ?>" class="btn btn-sm btn-link">Chi tiết</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Truyện -->
            <div class="col-lg-6">
                <div class="section-header d-flex justify-content-between align-items-center mb-4">
                    <h3 class="section-title">Truyện nổi bật</h3>
                    <a href="<?= USER_URL ?>stories.php" class="btn btn-sm btn-outline-success">
                        Xem tất cả <i class="fas fa-angle-right"></i>
                    </a>
                </div>
                
                <?php if (empty($stories)): ?>
                <div class="alert alert-info">Chưa có truyện nào được thêm vào.</div>
                <?php else: ?>
                <div class="row">
                    <?php 
                    $storyDisplay = array_slice($stories, 0, 4);
                    foreach ($storyDisplay as $index => $story): 
                        $coverImage = !empty($story['cover_image']) ? BASE_URL . 'assets/uploads/covers/' . $story['cover_image'] : BASE_URL . 'assets/images/default-story.jpg';
                    ?>
                    <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="story-card h-100">
                            <div class="story-card-body">
                                <div class="story-image mb-3">
                                    <img src="<?= $coverImage ?>" alt="<?= htmlspecialchars($story['title']) ?>" class="img-fluid rounded">
                                </div>
                                <h5 class="story-title"><?= htmlspecialchars($story['title']) ?></h5>
                                <p class="story-author">Tác giả: <?= htmlspecialchars($story['author']) ?></p>
                                <p class="story-excerpt">
                                    <?= substr(strip_tags(htmlspecialchars($story['description'] ?? '')), 0, 80) ?>...
                                </p>
                                <a href="<?= USER_URL ?>stories.php?id=<?= $story['id'] ?>" class="btn btn-success btn-sm read-btn">
                                    <i class="fas fa-book-reader"></i> Đọc truyện
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
</section>

<!-- Newsletter Section -->
<section class="newsletter-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3>Đăng ký nhận thông báo</h3>
                <p class="mb-4">Nhận thông báo khi có sách mới, truyện mới, nhạc mới và podcast mới</p>
                <form class="newsletter-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Email của bạn">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">Đăng ký</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- CSS Styles for index page -->
<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
    color: white;
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.05" d="M0,288L48,272C96,256,192,224,288,197.3C384,171,480,149,576,165.3C672,181,768,235,864,250.7C960,267,1056,245,1152,224C1248,203,1344,181,1392,170.7L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
    background-position: center bottom;
    background-repeat: no-repeat;
    background-size: cover;
    opacity: 0.2;
}

.hero-title {
    font-size: 3.2rem;
    font-weight: 800;
    margin-bottom: 20px;
    line-height: 1.2;
}

.hero-title .highlight {
    color: #ffca3a;
    position: relative;
    z-index: 1;
}

.hero-title .highlight::after {
    content: '';
    position: absolute;
    bottom: 5px;
    left: 0;
    width: 100%;
    height: 8px;
    background-color: rgba(255, 202, 58, 0.3);
    z-index: -1;
}

.hero-subtitle {
    font-size: 1.3rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

.search-box {
    margin-bottom: 30px;
    max-width: 500px;
}

.hero-buttons .btn {
    margin-right: 15px;
    padding: 12px 25px;
    border-radius: 50px;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 1px;
    transition: all 0.3s;
}

.hero-buttons .btn-primary {
    background: linear-gradient(45deg, #ff6b6b, #ff8e53);
    border: none;
    box-shadow: 0 7px 15px rgba(255, 107, 107, 0.3);
}

.hero-buttons .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(255, 107, 107, 0.4);
}

.hero-buttons .btn-outline-light {
    border-width: 2px;
}

.hero-buttons .btn-outline-light:hover {
    background-color: rgba(255,255,255,0.1);
    transform: translateY(-3px);
}

.hero-image-wrap {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

.hero-image {
    max-width: 100%;
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    transform: perspective(1000px) rotateY(-10deg);
    transition: all 0.5s;
}

.hero-image:hover {
    transform: perspective(1000px) rotateY(0);
}

.floating-item {
    position: absolute;
    background: white;
    border-radius: 50%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation-duration: 3s;
    animation-iteration-count: infinite;
    animation-timing-function: ease-in-out;
}

.item-book {
    top: 20%;
    left: 0;
    color: #4b6cb7;
    animation-name: float-1;
}

.item-music {
    top: 70%;
    right: 10%;
    color: #ff6b6b;
    animation-name: float-2;
}

.item-podcast {
    top: 40%;
    right: 5%;
    color: #ffca3a;
    animation-name: float-3;
}

@keyframes float-1 {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

@keyframes float-2 {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(15px); }
}

@keyframes float-3 {
    0%, 100% { transform: translateY(-10px); }
    50% { transform: translateY(10px); }
}

/* Categories Section */
.categories-section {
    background-color: #f9f9f9;
    position: relative;
}

.category-card {
    display: block;
    background: white;
    border-radius: 15px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    color: #333;
    text-decoration: none;
    transition: all 0.3s;
    height: 100%;
    position: relative;
    z-index: 1;
    overflow: hidden;
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(0,0,0,0.02) 0%, rgba(0,0,0,0) 100%);
    z-index: -1;
    transition: all 0.3s;
}

.category-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    color: white;
}

.category-card:hover::before {
    opacity: 0;
}

.category-books:hover {
    background: linear-gradient(45deg, #4b6cb7, #182848);
}

.category-stories:hover {
    background: linear-gradient(45deg, #56ab2f, #a8e063);
}

.category-music:hover {
    background: linear-gradient(45deg, #00c6ff, #0072ff);
}

.category-podcasts:hover {
    background: linear-gradient(45deg, #ff8008, #ffc837);
}

.category-icon {
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: #666;
    transition: all 0.3s;
}

.category-card:hover .category-icon {
    color: white;
    transform: scale(1.1);
}

.category-title {
    font-size: 1.4rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.category-description {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 20px;
    transition: all 0.3s;
}

.category-card:hover .category-description {
    color: rgba(255,255,255,0.8);
}

.category-arrow {
    position: absolute;
    bottom: 20px;
    right: 20px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    color: #333;
    transition: all 0.3s;
    opacity: 0;
    transform: translateX(20px);
}

.category-card:hover .category-arrow {
    opacity: 1;
    transform: translateX(0);
    background: rgba(255,255,255,0.2);
    color: white;
}

/* Section styling */
.section-title {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #333;
}

.section-subtitle {
    color: #666;
    margin-bottom: 20px;
}

.section-with-wave {
    position: relative;
    background-color: #f5f7fa;
}

.section-with-wave::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 70px;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" d="M0,288L48,272C96,256,192,224,288,197.3C384,171,480,149,576,165.3C672,181,768,235,864,250.7C960,267,1056,245,1152,224C1248,203,1344,181,1392,170.7L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 1;
}

.section-gradient {
    background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
    color: white;
    position: relative;
}

.section-gradient .section-title,
.section-gradient .section-subtitle {
    color: white;
}

.view-all-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 15px;
    border-radius: 30px;
    transition: all 0.3s;
}

.view-all-btn:hover {
    transform: translateX(5px);
}

/* Book cards */
.book-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: all 0.3s;
    height: 100%;
}

.book-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.book-card-cover {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.book-card-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.book-card:hover .book-card-cover img {
    transform: scale(1.05);
}

.book-card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s;
}

.book-card:hover .book-card-overlay {
    opacity: 1;
}

.book-card-body {
    padding: 15px;
}

.book-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.book-author {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 10px;
}

/* Music cards */
.music-card {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    transition: all 0.3s;
    height: 100%;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
}

.music-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.music-card-cover {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.music-card-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.5s;
}

.music-card:hover .music-card-cover img {
    transform: scale(1.05);
}

.music-play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.8);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    color: '#333';
}

.music-card-body {
    padding: 15px;
    color: white;
}

.music-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.music-artist {
    font-size: 0.9rem;
    opacity: 0.8;
    margin-bottom: 10px;
}

.music-list-item {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 5px;
    transition: all 0.3s;
}

.music-list-item:hover {
    background: rgba(255,255,255,0.2);
    transform: translateX(5px);
}

.music-list-img {
    width: 50px;
    height: 50px;
    border-radius: 5px;
    object-fit: cover;
}

/* Podcast Section */
.podcast-item {
    background: white;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    overflow: hidden;
    transition: all 0.3s;
}

.podcast-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.podcast-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.podcast-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.podcast-author {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 10px;
}

/* Story Cards */
.story-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    transition: all 0.3s;
}

.story-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.story-card-body {
    padding: 15px;
}

.story-image {
    height: 150px;
    overflow: hidden;
    border-radius: 5px;
}

.story-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.story-card:hover .story-image img {
    transform: scale(1.05);
}

.story-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.story-author {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 5px;
}

.story-excerpt {
    font-size: 0.9rem;
    color: #444;
    margin-bottom: 15px;
}

.read-btn {
    align-self: flex-start;
}

/* Newsletter Section */
.newsletter-section {
    background-color: #f7f9fc;
    position: relative;
    overflow: hidden;
}

.newsletter-section::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: linear-gradient(45deg, rgba(75, 108, 183, 0.1) 0%, rgba(24, 40, 72, 0.1) 100%);
    z-index: 0;
}

.newsletter-section::after {
    content: '';
    position: absolute;
    bottom: -100px;
    left: -100px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: linear-gradient(45deg, rgba(255, 107, 107, 0.1) 0%, rgba(255, 142, 83, 0.1) 100%);
    z-index: 0;
}

.newsletter-form .form-control {
    height: 50px;
    border-radius: 50px 0 0 50px;
    padding-left: 20px;
}

.newsletter-form .btn {
    border-radius: 0 50px 50px 0;
    padding-left: 25px;
    padding-right: 25px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .hero-image-column {
        margin-top: 30px;
    }
    
    .book-card-cover {
        height: 180px;
    }
    
    .music-card-cover {
        height: 180px;
    }
}

@media (max-width: 768px) {
    .hero-section {
        padding: 70px 0;
        text-align: center;
    }
    
    .search-box {
        margin: 0 auto 30px;
    }
    
    .hero-buttons {
        justify-content: center;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .music-card-cover {
        height: 160px;
    }
    
    .podcast-item img {
        max-height: 100px;
    }
}

@media (max-width: 576px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .book-card-cover,
    .music-card-cover {
        height: 200px;
    }
    
    .floating-item {
        width: 50px;
        height: 50px;
    }
}
</style>

<!-- JavaScript for Index Page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // AOS Animation Initialization
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }
    
    // Music Player Functionality
    const musicPlayButtons = document.querySelectorAll('.music-play-btn');
    let currentPlayingAudio = null;
    
    musicPlayButtons.forEach(button => {
        button.addEventListener('click', function() {
            const audioSrc = this.getAttribute('data-audio');
            
            // Stop currently playing audio if exists
            if (currentPlayingAudio && !currentPlayingAudio.paused) {
                currentPlayingAudio.pause();
                const oldButton = document.querySelector(`.music-play-btn[data-audio="${currentPlayingAudio.src.replace(location.origin, '')}"]`);
                if (oldButton) {
                    oldButton.innerHTML = '<i class="fas fa-play"></i>';
                }
            }
            
            // If clicked on the same audio that was playing, just stop
            if (currentPlayingAudio && audioSrc === currentPlayingAudio.src.replace(location.origin, '')) {
                currentPlayingAudio = null;
                return;
            }
            
            // Play new audio
            const audio = new Audio(audioSrc);
            audio.addEventListener('canplaythrough', () => {
                audio.play();
                this.innerHTML = '<i class="fas fa-pause"></i>';
                currentPlayingAudio = audio;
                
                // When audio ends
                audio.addEventListener('ended', () => {
                    this.innerHTML = '<i class="fas fa-play"></i>';
                    currentPlayingAudio = null;
                });
            });
            
            audio.addEventListener('error', () => {
                alert('Không thể phát file âm thanh này.');
                this.innerHTML = '<i class="fas fa-play"></i>';
            });
        });
    });
    
    // Podcast Play Functionality
    const podcastPlayButtons = document.querySelectorAll('.podcast-play-btn');
    
    podcastPlayButtons.forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            const audioElement = this.parentElement.querySelector('.podcast-audio');
            
            if (!audioElement) return;
            
            if (audioElement.paused) {
                // Stop any currently playing podcast
                document.querySelectorAll('.podcast-audio').forEach(audio => {
                    if (!audio.paused) {
                        audio.pause();
                        const playBtn = audio.parentElement.querySelector('.podcast-play-btn');
                        if (playBtn) {
                            playBtn.innerHTML = '<i class="fas fa-play"></i> Nghe';
                        }
                    }
                });
                
                // Play this podcast
                audioElement.play();
                this.innerHTML = '<i class="fas fa-pause"></i> Tạm dừng';
                
                // When audio ends
                audioElement.addEventListener('ended', () => {
                    this.innerHTML = '<i class="fas fa-play"></i> Nghe';
                });
            } else {
                // Pause this podcast
                audioElement.pause();
                this.innerHTML = '<i class="fas fa-play"></i> Nghe';
            }
        });
    });
    
    // Book Card Hover Animation
    const bookCards = document.querySelectorAll('.book-card');
    
    bookCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s';
        });
    });
    
    // Smooth scroll for category links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 70,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Newsletter Form Submission (example functionality)
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (!email) {
                alert('Vui lòng nhập địa chỉ email của bạn.');
                return;
            }
            
            // Simulate API call
            alert(`Đã đăng ký email ${email} thành công!`);
            emailInput.value = '';
        });
    }
});
</script>

<?php
    include 'includes/footer.php';
}
?>