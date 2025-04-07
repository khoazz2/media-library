<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị Thư viện Media</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= ADMIN_URL ?>">Quản trị Thư viện Media</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>categories.php">Danh mục</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>books.php">Sách</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>stories.php">Truyện</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>musics.php">Âm nhạc</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>podcasts.php">Podcast</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>radio.php">Radio</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>">Xem website</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>
