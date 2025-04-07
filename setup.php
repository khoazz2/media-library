<?php
// Thông tin kết nối đến database
$host = 'localhost';
$dbname = 'media_library';
$username = 'root'; // Mặc định của Laragon
$password = ''; // Mặc định của Laragon

try {
    // Kết nối đến MySQL
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tạo database nếu chưa tồn tại
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    
    // Tạo bảng categories (danh mục)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `type` ENUM('book', 'story', 'music', 'podcast', 'radio') NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Tạo bảng books (sách)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `books` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `author` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `cover_image` VARCHAR(255),
        `pdf_file` VARCHAR(255),
        `audio_file` VARCHAR(255),
        `category_id` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
    )");
    
    // Tạo bảng stories (truyện)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `stories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `author` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `cover_image` VARCHAR(255),
        `content` LONGTEXT,
        `audio_file` VARCHAR(255),
        `category_id` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
    )");
    
    // Tạo bảng musics (nhạc)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `musics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `artist` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `cover_image` VARCHAR(255),
        `audio_file` VARCHAR(255) NOT NULL,
        `category_id` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
    )");
    
    // Tạo bảng podcasts
    $pdo->exec("CREATE TABLE IF NOT EXISTS `podcasts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `author` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `cover_image` VARCHAR(255),
        `audio_file` VARCHAR(255) NOT NULL,
        `category_id` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
    )");
    
    // Tạo bảng radio stations
    $pdo->exec("CREATE TABLE IF NOT EXISTS `radio_stations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `logo_image` VARCHAR(255),
        `stream_url` VARCHAR(255) NOT NULL,
        `category_id` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
    )");
    
    echo "Database và các bảng đã được tạo thành công!";
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>
