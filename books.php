<?php
require_once '../config.php';
require_once '../database/database.php';

$db = new Database();

// Xem chi tiết một sách
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $bookId = (int)$_GET['id'];
    $book = $db->selectOne("SELECT b.*, c.name as category_name 
                            FROM books b 
                            LEFT JOIN categories c ON b.category_id = c.id 
                            WHERE b.id = ?", [$bookId]);
    
    include '../includes/header.php';
    
    if (!$book) {
        echo '<div class="container mt-5"><div class="alert alert-danger">Sách không tồn tại!</div></div>';
        include '../includes/footer.php';
        exit;
    }
    
    // Hiển thị chi tiết sách
    ?>
    <div class="container mt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= USER_URL ?>books.php">Sách</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($book['title']) ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-4">
                <?php if (!empty($book['cover_image'])): ?>
                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $book['cover_image'] ?>" 
                     alt="<?= htmlspecialchars($book['title']) ?>" class="img-fluid rounded shadow">
                <?php else: ?>
                <div class="bg-secondary text-white p-5 text-center rounded">
                    <i class="fas fa-book fa-5x mb-3"></i>
                    <h5>Không có ảnh bìa</h5>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <?php if (!empty($book['pdf_file'])): ?>
                    <button class="btn btn-primary btn-block mb-2" id="togglePdfBtn" type="button">
                        <i class="fas fa-book-reader mr-2"></i> Đọc sách (PDF)
                    </button>

                    <div class="reader-backdrop" id="readerBackdrop"></div>
                    <div id="pdfViewerContainer" class="pdf-container">
                        <div class="reader-box shadow-lg">
                            <div class="reader-header">
                                <div class="reader-title">
                                    <i class="fas fa-book mr-2"></i>
                                    <span class="title-text"><?= htmlspecialchars($book['title']) ?></span>
                                </div>
                                <div class="reader-controls">
                                    <button class="btn-icon" id="fullscreenPdfBtn" title="Toàn màn hình">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                    <button class="btn-icon" id="closePdfBtn" title="Đóng">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="reader-toolbar">
                                <div class="page-navigation">
                                    <button class="nav-btn" id="prevPage" title="Trang trước">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <div class="page-indicator">
                                        <input type="number" id="currentPageInput" min="1" value="1">
                                        <span id="totalPagesLabel">/ ?</span>
                                    </div>
                                    <button class="nav-btn" id="nextPage" title="Trang sau">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                
                                <div class="zoom-controls">
                                    <button class="nav-btn" id="zoomOut" title="Thu nhỏ">
                                        <i class="fas fa-search-minus"></i>
                                    </button>
                                    <span id="zoomLevel">100%</span>
                                    <button class="nav-btn" id="zoomIn" title="Phóng to">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="reader-content">
                                <object id="pdfObject" 
                                        data="about:blank" 
                                        type="application/pdf" 
                                        width="100%" 
                                        height="100%">
                                    <div class="pdf-fallback">
                                        <div class="fallback-content">
                                            <i class="fas fa-file-pdf text-danger fa-4x mb-3"></i>
                                            <h4>Không thể hiển thị PDF</h4>
                                            <p>Trình duyệt của bạn không hỗ trợ đọc PDF trực tiếp.</p>
                                            
                                            <div class="fallback-actions">
                                                <a href="<?= BASE_URL ?>assets/uploads/books/<?= $book['pdf_file'] ?>" class="btn btn-primary" download>
                                                    <i class="fas fa-download"></i> Tải xuống
                                                </a>
                                                <a href="<?= BASE_URL ?>assets/uploads/books/<?= $book['pdf_file'] ?>" class="btn btn-info" target="_blank">
                                                    <i class="fas fa-external-link-alt"></i> Mở tab mới
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </object>
                            </div>
                            
                            <div class="reader-footer">
                                <div class="reading-progress">
                                    <div class="progress-bar">
                                        <div id="readingProgressBar" style="width: 0%"></div>
                                    </div>
                                    <span id="readingProgressText">Trang 1 / ?</span>
                                </div>
                                
                                <div class="footer-actions">
                                    <a href="<?= BASE_URL ?>assets/uploads/books/<?= $book['pdf_file'] ?>" class="footer-btn" download title="Tải xuống">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const togglePdfBtn = document.getElementById('togglePdfBtn');
                        const pdfViewerContainer = document.getElementById('pdfViewerContainer');
                        const readerBackdrop = document.getElementById('readerBackdrop');
                        const pdfObject = document.getElementById('pdfObject');
                        const closePdfBtn = document.getElementById('closePdfBtn');
                        const fullscreenPdfBtn = document.getElementById('fullscreenPdfBtn');
                        
                        // Navigation controls
                        const prevPageBtn = document.getElementById('prevPage');
                        const nextPageBtn = document.getElementById('nextPage');
                        const currentPageInput = document.getElementById('currentPageInput');
                        const totalPagesLabel = document.getElementById('totalPagesLabel');
                        
                        // Zoom controls
                        const zoomInBtn = document.getElementById('zoomIn');
                        const zoomOutBtn = document.getElementById('zoomOut');
                        const zoomLevel = document.getElementById('zoomLevel');
                        
                        // Reading progress elements
                        const readingProgressBar = document.getElementById('readingProgressBar');
                        const readingProgressText = document.getElementById('readingProgressText');
                        
                        // Current state variables
                        let currentPage = 1;
                        let totalPages = 10; // Default estimate
                        let zoomPercent = 100;
                        
                        // Open PDF viewer
                        if(togglePdfBtn && pdfViewerContainer && pdfObject) {
                            togglePdfBtn.addEventListener('click', function() {
                                console.log("Button clicked"); // Debug log
                                openReader();
                            });
                            
                            // Close button and backdrop
                            closePdfBtn.addEventListener('click', closeReader);
                            readerBackdrop.addEventListener('click', closeReader);
                            
                            // Fullscreen toggle
                            fullscreenPdfBtn.addEventListener('click', function() {
                                if(pdfViewerContainer.classList.contains('fullscreen')) {
                                    exitFullscreen();
                                } else {
                                    enterFullscreen();
                                }
                            });
                            
                            // ESC key handler
                            document.addEventListener('keydown', function(e) {
                                if (e.key === 'Escape') {
                                    if (pdfViewerContainer.classList.contains('fullscreen')) {
                                        exitFullscreen();
                                    } else if (pdfViewerContainer.classList.contains('open')) {
                                        closeReader();
                                    }
                                }
                            });
                            
                            // Page navigation
                            prevPageBtn.addEventListener('click', function() {
                                goToPage(Math.max(1, currentPage - 1));
                            });
                            
                            nextPageBtn.addEventListener('click', function() {
                                goToPage(currentPage + 1);
                            });
                            
                            currentPageInput.addEventListener('change', function() {
                                let pageNum = parseInt(this.value);
                                if(pageNum >= 1) {
                                    goToPage(pageNum);
                                } else {
                                    this.value = currentPage;
                                }
                            });
                            
                            // Zoom controls
                            zoomInBtn.addEventListener('click', function() {
                                zoomPercent = Math.min(200, zoomPercent + 25);
                                applyZoom();
                            });
                            
                            zoomOutBtn.addEventListener('click', function() {
                                zoomPercent = Math.max(50, zoomPercent - 25);
                                applyZoom();
                            });
                        }
                        
                        // Function to open the reader modal
                        function openReader() {
                            // Direct manipulation without transitions for reliability
                            pdfViewerContainer.style.opacity = '0';
                            pdfViewerContainer.style.display = 'flex';
                            readerBackdrop.style.display = 'block';
                            
                            // Force reflow
                            void pdfViewerContainer.offsetWidth;
                            
                            // Add visible styles
                            pdfViewerContainer.style.opacity = '1';
                            readerBackdrop.classList.add('open');
                            document.body.classList.add('reader-active');
                            
                            // Initialize PDF
                            initPdfObject();
                        }
                        
                        // Function to close the reader modal
                        function closeReader() {
                            pdfViewerContainer.style.opacity = '0';
                            readerBackdrop.classList.remove('open');
                            
                            setTimeout(function() {
                                pdfViewerContainer.style.display = 'none';
                                readerBackdrop.style.display = 'none';
                                document.body.classList.remove('reader-active');
                            }, 300);
                        }
                        
                        // Enter fullscreen mode
                        function enterFullscreen() {
                            pdfViewerContainer.classList.add('fullscreen');
                            fullscreenPdfBtn.innerHTML = '<i class="fas fa-compress"></i>';
                            fullscreenPdfBtn.title = 'Thoát toàn màn hình';
                        }
                        
                        // Exit fullscreen mode
                        function exitFullscreen() {
                            pdfViewerContainer.classList.remove('fullscreen');
                            fullscreenPdfBtn.innerHTML = '<i class="fas fa-expand"></i>';
                            fullscreenPdfBtn.title = 'Toàn màn hình';
                        }
                        
                        // Apply zoom level
                        function applyZoom() {
                            zoomLevel.textContent = zoomPercent + '%';
                            
                            try {
                                let url = new URL(pdfObject.data, window.location.href);
                                url.hash = `zoom=${zoomPercent}&page=${currentPage}`;
                                pdfObject.data = url.toString();
                            } catch(e) {
                                console.log("Error applying zoom");
                            }
                        }
                        
                        // Initialize PDF object
                        function initPdfObject() {
                            try {
                                // Use a more compatible approach for PDF viewing
                                const pdfPath = '<?= BASE_URL ?>assets/uploads/books/<?= $book['pdf_file'] ?>';
                                
                                // Use a direct approach that will work in most browsers
                                totalPages = estimateMoreAccuratePageCount();
                                totalPagesLabel.textContent = "/ " + totalPages;
                                currentPageInput.value = 1;
                                updateProgressBar();
                                
                                // Set better initial parameters
                                let url = new URL(pdfPath, window.location.href);
                                url.hash = 'view=FitH&navpanes=1&toolbar=1&page=1';
                                pdfObject.data = url.toString();
                            } catch(e) {
                                console.log("Error initializing PDF object:", e);
                                // Simple fallback
                                pdfObject.data = '<?= BASE_URL ?>assets/uploads/books/<?= $book['pdf_file'] ?>#toolbar=1';
                            }
                        }
                        
                        // Improved page navigation that works more reliably
                        function goToPage(pageNum) {
                            try {
                                // Validate page number
                                if (pageNum < 1) pageNum = 1;
                                if (pageNum > totalPages) pageNum = totalPages;
                                
                                // Get current URL and parameters
                                const currentUrl = pdfObject.data;
                                const baseUrl = currentUrl.split('#')[0];
                                
                                // Create new URL with updated page parameter
                                let newUrl = baseUrl + '#page=' + pageNum;
                                
                                // Force reload of the object with new parameters
                                pdfObject.data = 'about:blank'; // Clear current content
                                setTimeout(() => {
                                    pdfObject.data = newUrl;
                                    
                                    // Update UI
                                    currentPageInput.value = pageNum;
                                    currentPage = pageNum;
                                    updateProgressBar();
                                    
                                    console.log("Navigated to page", pageNum);
                                }, 50);
                            } catch(e) {
                                console.log("Error navigating to page:", e);
                            }
                        }

                        // Better page count estimation
                        function estimateMoreAccuratePageCount() {
                            // Try to get a better estimate based on the file size
                            const fileSizeKB = <?= !empty($book['file_size']) ? ($book['file_size'] / 1024) : 0 ?>;
                            
                            if (fileSizeKB > 0) {
                                // Rough estimate: ~3 pages per 100KB for a typical PDF
                                return Math.max(1, Math.ceil(fileSizeKB / 33));
                            }
                            
                            // Default fallback
                            return 25; // More reasonable default than 10
                        }

                        // Update applyZoom function too
                        function applyZoom() {
                            zoomLevel.textContent = zoomPercent + '%';
                            
                            try {
                                // Get current page before zooming
                                const currPage = parseInt(currentPageInput.value) || 1;
                                
                                // Create a new URL with zoom parameter
                                const currentUrl = pdfObject.data;
                                const baseUrl = currentUrl.split('#')[0];
                                
                                // Combine zoom and current page
                                let newUrl = baseUrl + '#zoom=' + zoomPercent + '&page=' + currPage;
                                
                                // Apply in a way that forces refresh
                                pdfObject.data = 'about:blank';
                                setTimeout(() => {
                                    pdfObject.data = newUrl;
                                }, 50);
                            } catch(e) {
                                console.log("Error applying zoom:", e);
                            }
                        }

                        // Add this function to check for actual PDF loading issues
                        function checkPdfLoadStatus() {
                            // After a delay, check if the PDF seems to have loaded
                            setTimeout(() => {
                                try {
                                    // Try to check if PDF is loaded by accessing the object
                                    const isPdfLoaded = pdfObject.contentDocument && 
                                                       !pdfObject.contentDocument.querySelector('.pdf-fallback');
                                    
                                    if (!isPdfLoaded) {
                                        console.log("PDF may not have loaded properly, attempting alternate loading method");
                                        // Try an alternate approach - open in new frame
                                        const pdfUrl = '<?= BASE_URL ?>assets/uploads/books/<?= $book['pdf_file'] ?>';
                                        window.open(pdfUrl, '_blank');
                                        
                                        // Show a message to the user
                                        alert('Có vẻ như trình duyệt của bạn không hiển thị được PDF trực tiếp. PDF sẽ được mở trong tab mới.');
                                        
                                        // Close the viewer
                                        closeReader();
                                    }
                                } catch(e) {
                                    console.log("Error checking PDF load status");
                                }
                            }, 3000);
                        }
                    });
                    </script>

                    <style>
                    /* Clean, modern reader styling */
                    .reader-active {
                        overflow: hidden;
                    }

                    .reader-backdrop {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.7);
                        z-index: 1040;
                        display: none;
                        opacity: 0;
                        transition: opacity 0.3s ease;
                    }

                    .reader-backdrop.open {
                        opacity: 1;
                    }

                    .pdf-container {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        z-index: 1050;
                        display: none;
                        opacity: 0;
                        transition: opacity 0.3s ease;
                    }

                    .pdf-container.open {
                        display: flex;
                        opacity: 1;
                        align-items: center;
                        justify-content: center;
                        padding: 20px;
                    }

                    .reader-box {
                        width: 100%;
                        max-width: 1200px;
                        height: calc(100vh - 40px);
                        background-color: #fff;
                        border-radius: 8px;
                        overflow: hidden;
                        display: flex;
                        flex-direction: column;
                    }

                    /* Header styling */
                    .reader-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 12px 16px;
                        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
                        color: white;
                    }

                    .reader-title {
                        display: flex;
                        align-items: center;
                        font-weight: 600;
                        font-size: 16px;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }

                    .title-text {
                        max-width: calc(100vw - 150px);
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }

                    .reader-controls {
                        display: flex;
                        gap: 8px;
                    }

                    .btn-icon {
                        background: rgba(255, 255, 255, 0.2);
                        border: none;
                        width: 32px;
                        height: 32px;
                        border-radius: 50%;
                        color: white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        transition: background 0.2s;
                    }

                    .btn-icon:hover {
                        background: rgba(255, 255, 255, 0.3);
                    }

                    /* Toolbar styling */
                    .reader-toolbar {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 8px 16px;
                        background-color: #f5f5f5;
                        border-bottom: 1px solid #e0e0e0;
                    }

                    .page-navigation, .zoom-controls {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }

                    .page-indicator {
                        display: flex;
                        align-items: center;
                        gap: 4px;
                        background: white;
                        border-radius: 4px;
                        padding: 0 8px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    }

                    .page-indicator input {
                        width: 40px;
                        border: none;
                        text-align: center;
                        padding: 4px 0;
                    }

                    .nav-btn {
                        background: white;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        transition: all 0.2s;
                    }

                    .nav-btn:hover {
                        background: #f0f0f0;
                    }

                    #zoomLevel {
                        background: white;
                        padding: 4px 8px;
                        border-radius: 4px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        min-width: 45px;
                        text-align: center;
                    }

                    /* PDF content area */
                    .reader-content {
                        flex: 1;
                        overflow: hidden;
                        background-color: #888;
                        position: relative;
                    }

                    #pdfObject {
                        display: block;
                        border: none;
                    }

                    .pdf-fallback {
                        height: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        text-align: center;
                        padding: 20px;
                        background: #f8f9fa;
                    }

                    .fallback-content {
                        max-width: 500px;
                    }

                    .fallback-actions {
                        display: flex;
                        gap: 10px;
                        justify-content: center;
                        margin-top: 20px;
                    }

                    /* Footer styling */
                    .reader-footer {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 8px 16px;
                        background-color: #f5f5f5;
                        border-top: 1px solid #e0e0e0;
                    }

                    .reading-progress {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }

                    .progress-bar {
                        width: 150px;
                        height: 4px;
                        background-color: #e0e0e0;
                        border-radius: 2px;
                        overflow: hidden;
                    }

                    #readingProgressBar {
                        height: 100%;
                        background-color: #4caf50;
                        transition: width 0.3s ease;
                    }

                    #readingProgressText {
                        font-size: 13px;
                        color: #666;
                    }

                    .footer-actions {
                        display: flex;
                        gap: 8px;
                    }

                    .footer-btn {
                        background: transparent;
                        color: #666;
                        border: none;
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 4px;
                        cursor: pointer;
                        transition: background 0.2s;
                    }

                    .footer-btn:hover {
                        background: #e0e0e0;
                        color: #333;
                    }

                    /* Fullscreen mode */
                    .pdf-container.fullscreen .reader-box {
                        max-width: none;
                        height: 100vh;
                        border-radius: 0;
                    }

                    /* Responsive adjustments */
                    @media (max-width: 768px) {
                        .reader-toolbar {
                            flex-direction: column;
                            gap: 8px;
                        }
                        
                        .zoom-controls {
                            width: 100%;
                            justify-content: center;
                        }
                        
                        .page-navigation {
                            width: 100%;
                            justify-content: center;
                        }
                        
                        .reading-progress {
                            flex-direction: column;
                            align-items: flex-start;
                        }
                        
                        .progress-bar {
                            width: 100%;
                        }
                    }

                    @media (max-width: 480px) {
                        .reader-header {
                            padding: 8px 12px;
                        }
                        
                        .reader-title {
                            font-size: 14px;
                        }
                        
                        .btn-icon, .nav-btn {
                            width: 28px;
                            height: 28px;
                        }
                        
                        .footer-btn {
                            width: 28px;
                            height: 28px;
                        }
                    }
                    </style>
                    <?php endif; ?>
                    
                    <?php if (!empty($book['audio_file'])): ?>
                    <button class="btn btn-success btn-block mb-2" type="button" id="toggleAudioBtn">
                        <i class="fas fa-headphones"></i> Nghe sách audio
                    </button>

                    <div class="card mt-3" id="audioPlayerContainer" style="display: none;">
                        <div class="card-body bg-light">
                            <h6 class="mb-3">
                                <i class="fas fa-play-circle"></i> 
                                Đang phát: <?= htmlspecialchars($book['title']) ?>
                            </h6>
                            <audio id="bookAudioPlayer" controls class="w-100">
                                <source src="<?= BASE_URL ?>assets/uploads/books/<?= $book['audio_file'] ?>" type="audio/mpeg">
                                Trình duyệt của bạn không hỗ trợ phát audio.
                            </audio>
                            
                            <div class="mt-3 d-flex justify-content-between">
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('bookAudioPlayer').currentTime -= 10">
                                        <i class="fas fa-backward"></i> 10s
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('bookAudioPlayer').playbackRate = 0.75">
                                        0.75x
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('bookAudioPlayer').playbackRate = 1">
                                        1x
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('bookAudioPlayer').playbackRate = 1.25">
                                        1.25x
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('bookAudioPlayer').playbackRate = 1.5">
                                        1.5x
                                    </button>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('bookAudioPlayer').currentTime += 10">
                                    <i class="fas fa-forward"></i> 10s
                                </button>
                            </div>
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const toggleBtn = document.getElementById('toggleAudioBtn');
                        const playerContainer = document.getElementById('audioPlayerContainer');
                        const audioPlayer = document.getElementById('bookAudioPlayer');
                        
                        if(toggleBtn && playerContainer && audioPlayer) {
                            toggleBtn.addEventListener('click', function() {
                                if(playerContainer.style.display === 'none') {
                                    playerContainer.style.display = 'block';
                                    // Tự động phát audio khi hiện player
                                    audioPlayer.play().catch(e => {
                                        console.log('Auto-play prevented:', e);
                                    });
                                    toggleBtn.innerHTML = '<i class="fas fa-pause"></i> Tạm dừng nghe';
                                } else {
                                    playerContainer.style.display = 'none';
                                    audioPlayer.pause();
                                    toggleBtn.innerHTML = '<i class="fas fa-headphones"></i> Nghe sách audio';
                                }
                            });
                            
                            // Cập nhật nút khi audio bị tạm dừng/phát lại
                            audioPlayer.addEventListener('play', function() {
                                toggleBtn.innerHTML = '<i class="fas fa-pause"></i> Tạm dừng nghe';
                            });
                            
                            audioPlayer.addEventListener('pause', function() {
                                toggleBtn.innerHTML = '<i class="fas fa-headphones"></i> Tiếp tục nghe';
                            });
                            
                            // Theo dõi và xử lý lỗi
                            audioPlayer.addEventListener('error', function(e) {
                                console.error('Audio error:', e);
                                alert('Không thể phát audio: File có thể bị lỗi hoặc định dạng không được hỗ trợ');
                                playerContainer.innerHTML += '<div class="alert alert-danger mt-2">Không thể phát audio: File có thể bị lỗi hoặc định dạng không được hỗ trợ</div>';
                            });
                        }
                    });
                    </script>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-8">
                <h1 class="mb-3"><?= htmlspecialchars($book['title']) ?></h1>
                
                <div class="mb-4">
                    <span class="badge badge-primary mr-2">Sách</span>
                    <?php if (!empty($book['category_name'])): ?>
                    <span class="badge badge-info"><?= htmlspecialchars($book['category_name']) ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <h5>Tác giả</h5>
                    <p><?= htmlspecialchars($book['author']) ?></p>
                </div>
                <div class="mb-4">
        <h5>Thể loại</h5>
        <?php if (!empty($book['category_name'])): ?>
        <p><?= htmlspecialchars($book['category_name']) ?></p>
        <?php else: ?>
        <p class="text-muted">Chưa phân loại</p>
        <?php endif; ?>
    </div>

                <div class="mb-4">
                    <h5>Mô tả</h5>
                    <?php if (!empty($book['description'])): ?>
                    <p class="text-justify"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                    <?php else: ?>
                    <p class="text-muted">Không có mô tả.</p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h5>Thông tin khác</h5>
                    <ul class="list-unstyled">
                        <li><strong>Ngày thêm:</strong> <?= date('d/m/Y', strtotime($book['created_at'])) ?></li>
                        <li>
                            <strong>Định dạng có sẵn:</strong>
                            <?php
                            $formats = [];
                            if (!empty($book['pdf_file'])) $formats[] = 'PDF';
                            if (!empty($book['audio_file'])) $formats[] = 'Audio';
                            echo !empty($formats) ? implode(', ', $formats) : 'Không có';
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    // Hiển thị danh sách tất cả sách
    $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
    
    // Xây dựng câu truy vấn
    $query = "SELECT b.*, c.name as category_name 
              FROM books b 
              LEFT JOIN categories c ON b.category_id = c.id";
    $params = [];
    
    $whereConditions = [];
    if ($categoryId) {
        $whereConditions[] = "b.category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($search) {
        $whereConditions[] = "(b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(' AND ', $whereConditions);
    }
    
    $query .= " ORDER BY b.created_at DESC";
    
    $books = $db->select($query, $params);
    $categories = $db->select("SELECT * FROM categories WHERE type = 'book' ORDER BY name");
    
    include '../includes/header.php';
    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tìm kiếm</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= USER_URL ?>books.php" method="get">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sách..." 
                                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Tìm kiếm</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Danh mục</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item <?= !isset($_GET['category']) ? 'active' : '' ?>">
                                <a href="<?= USER_URL ?>books.php" class="<?= !isset($_GET['category']) ? 'text-white' : 'text-dark' ?>">
                                    Tất cả sách
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li class="list-group-item <?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'active' : '' ?>">
                                <a href="<?= USER_URL ?>books.php?category=<?= $category['id'] ?>" 
                                   class="<?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'text-white' : 'text-dark' ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <h2 class="mb-4">Thư viện sách</h2>
                
                <?php if (isset($_GET['search']) || isset($_GET['category'])): ?>
                <div class="mb-4">
                    <h6>
                        <?php if (isset($_GET['search'])): ?>
                        Kết quả tìm kiếm cho: <span class="text-primary">"<?= htmlspecialchars($_GET['search']) ?>"</span>
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
                        Danh mục: <span class="text-primary"><?= htmlspecialchars($categoryName) ?></span>
                        <?php endif; ?>
                    </h6>
                    
                    <a href="<?= USER_URL ?>books.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <?php if (empty($books)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            Không có sách nào được tìm thấy.
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($books as $book): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-img-top book-cover">
                                <?php if (!empty($book['cover_image'])): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/covers/<?= $book['cover_image'] ?>" 
                                     alt="<?= htmlspecialchars($book['title']) ?>" class="img-fluid">
                                <?php else: ?>
                                <div class="no-cover">
                                    <i class="fas fa-book fa-3x"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($book['author']) ?></p>
                                
                                <div class="mb-2">
                                    <?php if (!empty($book['category_name'])): ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($book['category_name']) ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($book['pdf_file'])): ?>
                                    <span class="badge badge-success">PDF</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($book['audio_file'])): ?>
                                    <span class="badge badge-warning">Audio</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="<?= USER_URL ?>books.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-book-open"></i> Đọc sách
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
<?php
}
?>