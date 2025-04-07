<?php
// Thông tin kết nối database
define('DB_HOST', 'localhost');
define('DB_NAME', 'media_library');
define('DB_USER', 'root');
define('DB_PASS', '');

// Đường dẫn upload
define('UPLOAD_PATH', __DIR__ . '/assets/uploads/');
define('BOOK_UPLOAD_PATH', UPLOAD_PATH . 'books/');
define('STORY_UPLOAD_PATH', UPLOAD_PATH . 'stories/');
define('MUSIC_UPLOAD_PATH', UPLOAD_PATH . 'musics/');
define('PODCAST_UPLOAD_PATH', UPLOAD_PATH . 'podcasts/');
define('COVER_UPLOAD_PATH', UPLOAD_PATH . 'covers/');

// URL cơ sở của website
define('BASE_URL', 'http://localhost:81/media_library/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('USER_URL', BASE_URL . 'user/');

// Tạo thư mục upload nếu chưa tồn tại
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
    mkdir(BOOK_UPLOAD_PATH, 0777, true);
    mkdir(STORY_UPLOAD_PATH, 0777, true);
    mkdir(MUSIC_UPLOAD_PATH, 0777, true);
    mkdir(PODCAST_UPLOAD_PATH, 0777, true);
    mkdir(COVER_UPLOAD_PATH, 0777, true);
}

// Hàm kết nối đến database
function getDbConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("SET NAMES utf8mb4");
        return $pdo;
    } catch (PDOException $e) {
        die("Lỗi kết nối database: " . $e->getMessage());
    }
}

// Hàm lọc dữ liệu đầu vào
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Hàm chuyển hướng
function redirect($url) {
    header("Location: $url");
    exit;
}

// Hàm kiểm tra route admin
function isAdminRoute() {
    $uri = $_SERVER['REQUEST_URI'];
    return strpos($uri, '/admin/') !== false;
}
?>
