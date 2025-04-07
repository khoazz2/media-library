<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện Media</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>">Thư viện Media</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= USER_URL ?>books.php">Sách</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= USER_URL ?>stories.php">Truyện</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= USER_URL ?>musics.php">Âm nhạc</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= USER_URL ?>podcasts.php">Podcast</a>
                    </li>
                    
                </ul>
               
            </div>
        </div>
    </nav>
</body>
</html>
