    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Readbook</h5>
                    <p>Nơi bạn có thể đọc sách, nghe nhạc, nghe podcast và nhiều hơn nữa.</p>
                </div>
                <div class="col-md-3">
                    <h5>Liên kết</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>" class="text-white">Trang chủ</a></li>
                        <li><a href="<?= USER_URL ?>books.php" class="text-white">Sách</a></li>
                        <li><a href="<?= USER_URL ?>musics.php" class="text-white">Âm nhạc</a></li>
                        <li><a href="<?= USER_URL ?>podcasts.php" class="text-white">Podcast</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope mr-2"></i> contact@medialibrary.com</li>
                        <li><i class="fas fa-phone mr-2"></i> (123) 456-7890</li>
                    </ul>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?= date('Y') ?> Readbook. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
