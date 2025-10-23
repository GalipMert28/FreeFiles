<?php
$dbFile = 'files.json';
$uploadDir = 'uploads/';
$likesFile = 'likes.json';

// Beğeni dosyası yoksa oluştur
if (!file_exists($likesFile)) {
    file_put_contents($likesFile, json_encode(['files' => [], 'comments' => []]));
}

// Görüntülenme sayısını artır
function incrementViews($fileId, $dbFile) {
    $files = json_decode(file_get_contents($dbFile), true);
    if (is_array($files)) {
        foreach ($files as &$file) {
            if ($file['id'] === $fileId) {
                if (!isset($file['views'])) {
                    $file['views'] = 0;
                }
                $file['views']++;
                file_put_contents($dbFile, json_encode($files, JSON_PRETTY_PRINT));
                return $file['views'];
            }
        }
    }
    return 0;
}

// Arama fonksiyonu
function searchFiles($files, $query) {
    if (empty($query)) {
        return $files;
    }
    
    $query = strtolower(trim($query));
    $results = [];
    
    foreach ($files as $file) {
        $score = 0;
        
        // Başlıkta ara
        if (isset($file['title']) && stripos($file['title'], $query) !== false) {
            $score += 10;
        }
        
        // Açıklamada ara
        if (isset($file['description']) && stripos($file['description'], $query) !== false) {
            $score += 5;
        }
        
        // Anahtar kelimelerde ara
        if (isset($file['keywords']) && is_array($file['keywords'])) {
            foreach ($file['keywords'] as $keyword) {
                if (stripos($keyword, $query) !== false) {
                    $score += 8;
                }
            }
        }
        
        // Dosya adında ara
        if (isset($file['original_name']) && stripos($file['original_name'], $query) !== false) {
            $score += 3;
        }
        
        // ID'de ara
        if (isset($file['id']) && stripos($file['id'], $query) !== false) {
            $score += 15;
        }
        
        if ($score > 0) {
            $file['search_score'] = $score;
            $results[] = $file;
        }
    }
    
    // Skora göre sırala
    usort($results, function($a, $b) {
        return $b['search_score'] - $a['search_score'];
    });
    
    return $results;
}

// ID parametresi varsa dosya detay sayfasını göster
if (isset($_GET['id'])) {
    $fileId = trim($_GET['id']);
    
    if (file_exists($dbFile)) {
        $filesContent = file_get_contents($dbFile);
        $files = json_decode($filesContent, true);
        $foundFile = null;
        
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file['id'] === $fileId || strcasecmp($file['id'], $fileId) === 0) {
                    $foundFile = $file;
                    break;
                }
            }
        }
        
        if ($foundFile) {
            // Görüntülenme sayısını artır
            $views = incrementViews($fileId, $dbFile);
            $foundFile['views'] = $views;
            
            // Dosya detay sayfası
            $isAudio = $foundFile['extension'] === 'mp3';
            $isVideo = in_array($foundFile['extension'], ['mp4', 'm4u', 'webm', 'avi', 'mov']);
            $isImage = in_array($foundFile['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
            $isText = in_array($foundFile['extension'], ['txt', 'md', 'log', 'json', 'xml', 'html', 'css', 'js', 'php', 'py', 'java', 'c', 'cpp']);
            $isMedia = $isAudio || $isVideo || $isImage;
            $fileSizeMB = round(($foundFile['compressed_size'] ?? $foundFile['original_size'] ?? $foundFile['size']) / 1024 / 1024, 2);
            
            // Beğeni sayılarını al
            $likes = json_decode(file_get_contents($likesFile), true);
            $fileLikes = isset($likes['files'][$fileId]['likes']) ? $likes['files'][$fileId]['likes'] : 0;
            $fileDislikes = isset($likes['files'][$fileId]['dislikes']) ? $likes['files'][$fileId]['dislikes'] : 0;
            
            include 'file_detail.php';
            exit;
        } else {
            echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Dosya Bulunamadı</title><style>body{font-family:Arial;background:#1a1a1a;color:#e0e0e0;text-align:center;padding:50px;}</style></head><body><h1>😕 Dosya Bulunamadı</h1><p>Bu ID'ye ait dosya mevcut değil.</p><a href='/' style='color:#4a9eff;text-decoration:none;'>← Ana Sayfaya Dön</a></body></html>";
            exit;
        }
    }
}

// Ana sayfa - dosya listesi
$files = [];
if (file_exists($dbFile)) {
    $files = json_decode(file_get_contents($dbFile), true);
}

// Arama yapıldıysa
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
if (!empty($searchQuery)) {
    $files = searchFiles($files, $searchQuery);
}

// Beğeni sayılarını al
$likes = json_decode(file_get_contents($likesFile), true);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreeFiles - Profesyonel Dosya Paylaşım Platformu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #1a1a1a;
            color: #e0e0e0;
            min-height: 100vh;
        }

        .navbar {
            background: #2a2a2a;
            border-bottom: 1px solid #3a3a3a;
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .logo {
            font-size: 1.4rem;
            font-weight: 600;
            color: #4a9eff;
            text-decoration: none;
            white-space: nowrap;
        }

        .search-container {
            flex: 1;
            max-width: 600px;
        }

        .search-form {
            display: flex;
            gap: 0.5rem;
        }

        .search-input {
            flex: 1;
            padding: 0.7rem 1rem;
            background: #1a1a1a;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            color: #e0e0e0;
            font-size: 0.9rem;
        }

        .search-input:focus {
            outline: none;
            border-color: #4a9eff;
        }

        .search-btn {
            padding: 0.7rem 1.5rem;
            background: #4a9eff;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .search-btn:hover {
            background: #3a8eef;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.7rem 1.2rem;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            background: transparent;
            color: #e0e0e0;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
            white-space: nowrap;
        }

        .btn:hover {
            background: #3a3a3a;
        }

        .btn-primary {
            background: #4a9eff;
            border-color: #4a9eff;
            color: #fff;
        }

        .btn-primary:hover {
            background: #3a8eef;
        }

        .hero {
            max-width: 1400px;
            margin: 3rem auto 2rem;
            padding: 0 2rem;
        }

        .hero h1 {
            font-size: 2.2rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .hero p {
            font-size: 1rem;
            color: #999;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto 3rem;
            padding: 0 2rem;
        }

        .search-info {
            padding: 1rem;
            background: #2a2a2a;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #3a3a3a;
        }

        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .file-card {
            background: #2a2a2a;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            overflow: hidden;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .file-card:hover {
            border-color: #4a9eff;
            transform: translateY(-2px);
        }

        .file-preview {
            width: 100%;
            height: 200px;
            background: #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .file-preview img,
        .file-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file-icon {
            font-size: 3.5rem;
        }

        .file-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .file-card:hover .file-overlay {
            opacity: 1;
        }

        .play-icon {
            font-size: 3rem;
            color: #fff;
        }

        .file-info {
            padding: 1.2rem;
        }

        .file-title {
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 1rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .file-description {
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 0.8rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .file-keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-bottom: 0.8rem;
        }

        .keyword-tag {
            padding: 0.2rem 0.6rem;
            background: #3a3a3a;
            border-radius: 3px;
            font-size: 0.75rem;
            color: #4a9eff;
        }

        .file-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.8rem;
            padding-top: 0.8rem;
            border-top: 1px solid #3a3a3a;
        }

        .file-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
            color: #999;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #2a2a2a;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #fff;
        }

        .close-btn {
            background: none;
            border: none;
            color: #999;
            font-size: 2rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .close-btn:hover {
            color: #fff;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #e0e0e0;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 0.8rem;
            background: #1a1a1a;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            color: #e0e0e0;
            font-family: inherit;
            font-size: 0.9rem;
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #4a9eff;
        }

        .form-hint {
            font-size: 0.75rem;
            color: #666;
            margin-top: 0.3rem;
        }

        .upload-area {
            border: 2px dashed #3a3a3a;
            border-radius: 4px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #1a1a1a;
            margin-bottom: 1.5rem;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: #4a9eff;
            background: #252525;
        }

        .upload-icon {
            font-size: 2.5rem;
            margin-bottom: 0.8rem;
        }

        input[type="file"] {
            display: none;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 1024px) {
            .file-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
            
            .nav-content {
                flex-wrap: wrap;
            }
            
            .search-container {
                order: 3;
                width: 100%;
                max-width: none;
            }
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .file-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="/" class="logo">FreeFiles</a>
            <div class="search-container">
                <form class="search-form" method="GET">
                    <input type="text" name="q" class="search-input" placeholder="Başlık, anahtar kelime veya ID ile ara..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit" class="search-btn">🔍 Ara</button>
                </form>
            </div>
            <div class="nav-buttons">
                <button class="btn btn-primary" onclick="openUploadModal()">📤 Yükle</button>
            </div>
        </div>
    </nav>

    <div class="hero">
        <h1>FreeFiles - Profesyonel İçerik Platformu</h1>
        <p>Dosyalarınızı başlık ve anahtar kelimelerle yükleyin, paylaşın ve keşfedin</p>
    </div>

    <div class="container">
        <?php if (!empty($searchQuery)): ?>
            <div class="search-info">
                <strong>"<?php echo htmlspecialchars($searchQuery); ?>"</strong> için <?php echo count($files); ?> sonuç bulundu
                <?php if (count($files) > 0): ?>
                    <a href="/" style="color: #4a9eff; margin-left: 1rem; text-decoration: none;">← Tümünü Göster</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h2 class="section-title">
            <?php echo !empty($searchQuery) ? '🔍 Arama Sonuçları' : '📂 Son Yüklenenler'; ?>
        </h2>
        
        <div class="file-grid">
            <?php if (empty($files)): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <div class="empty-icon"><?php echo !empty($searchQuery) ? '🔍' : '📭'; ?></div>
                    <p><?php echo !empty($searchQuery) ? 'Arama kriterlerine uygun sonuç bulunamadı' : 'Henüz dosya yok. İlk sen yükle!'; ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($files as $file): 
                    $icons = [
                        'mp3' => '🎵', 'mp4' => '🎬', 'm4u' => '🎬', 'webm' => '🎬',
                        'pdf' => '📄', 'doc' => '📝', 'docx' => '📝',
                        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🎨',
                        'webp' => '🖼️', 'bmp' => '🖼️', 'svg' => '🎨',
                        'zip' => '📦', 'rar' => '📦', 'txt' => '📃', 'md' => '📃'
                    ];
                    $icon = $icons[$file['extension']] ?? '📄';
                    $fileSizeMB = round(($file['compressed_size'] ?? $file['size']) / 1024 / 1024, 2);
                    $fileId = $file['id'];
                    $fileLikes = isset($likes['files'][$fileId]['likes']) ? $likes['files'][$fileId]['likes'] : 0;
                    $fileDislikes = isset($likes['files'][$fileId]['dislikes']) ? $likes['files'][$fileId]['dislikes'] : 0;
                    
                    $isImage = in_array($file['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                    $isVideo = in_array($file['extension'], ['mp4', 'm4u', 'webm', 'avi', 'mov']);
                    
                    $title = isset($file['title']) ? $file['title'] : $file['original_name'];
                    $description = isset($file['description']) ? $file['description'] : '';
                    $keywords = isset($file['keywords']) && is_array($file['keywords']) ? $file['keywords'] : [];
                    $views = isset($file['views']) ? $file['views'] : 0;
                ?>
                <a href="?id=<?php echo $file['id']; ?>" class="file-card">
                    <div class="file-preview">
                        <?php if ($isImage): ?>
                            <img src="<?php echo $file['path']; ?>" alt="<?php echo htmlspecialchars($title); ?>">
                        <?php elseif ($isVideo): ?>
                            <video src="<?php echo $file['path']; ?>" muted></video>
                            <div class="file-overlay">
                                <span class="play-icon">▶️</span>
                            </div>
                        <?php else: ?>
                            <div class="file-icon"><?php echo $icon; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="file-info">
                        <div class="file-title"><?php echo htmlspecialchars($title); ?></div>
                        
                        <?php if (!empty($description)): ?>
                            <div class="file-description"><?php echo htmlspecialchars($description); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($keywords)): ?>
                            <div class="file-keywords">
                                <?php foreach (array_slice($keywords, 0, 3) as $keyword): ?>
                                    <span class="keyword-tag"><?php echo htmlspecialchars($keyword); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="file-meta">
                            <span><?php echo strtoupper($file['extension']); ?> • <?php echo $fileSizeMB; ?> MB</span>
                            <span><?php echo date('d.m.Y', strtotime($file['upload_date'])); ?></span>
                        </div>
                        
                        <div class="file-stats">
                            <span class="stat-item">
                                <span>👁️</span>
                                <span><?php echo $views; ?></span>
                            </span>
                            <span class="stat-item">
                                <span>👍</span>
                                <span><?php echo $fileLikes; ?></span>
                            </span>
                            <span class="stat-item">
                                <span>👎</span>
                                <span><?php echo $fileDislikes; ?></span>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal" id="uploadModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Dosya Yükle</div>
                <button class="close-btn" onclick="closeUploadModal()">×</button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Başlık *</label>
                    <input type="text" id="titleInput" class="form-input" placeholder="Dosya başlığı..." maxlength="200" required>
                    <div class="form-hint">Maksimum 200 karakter</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Açıklama</label>
                    <textarea id="descriptionInput" class="form-textarea" placeholder="Dosya hakkında kısa açıklama..." maxlength="500"></textarea>
                    <div class="form-hint">Maksimum 500 karakter (opsiyonel)</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Anahtar Kelimeler</label>
                    <input type="text" id="keywordsInput" class="form-input" placeholder="örnek: müzik, rock, 2024">
                    <div class="form-hint">Virgülle ayırarak yazın (opsiyonel)</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Dosya *</label>
                    <input type="file" id="fileInput" name="file">
                    <div class="upload-area" id="uploadArea" onclick="triggerFileInput()">
                        <div class="upload-icon">☁️</div>
                        <p style="font-weight: 500; margin-bottom: 0.5rem;">Dosya Seç veya Sürükle</p>
                        <p style="font-size: 0.85rem; color: #666;">Resimler otomatik sıkıştırılır</p>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">📤 Yükle</button>
            </form>
        </div>
    </div>

    <script>
        function triggerFileInput() {
            document.getElementById('fileInput').click();
        }

        function openUploadModal() {
            document.getElementById('uploadModal').classList.add('active');
            const fileInput = document.getElementById('fileInput');
            if (fileInput) {
                fileInput.onchange = function() {
                    if (this.files && this.files[0]) {
                        const fileName = this.files[0].name;
                        const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
                        document.getElementById('uploadArea').innerHTML = `
                            <div class="upload-icon">✅</div>
                            <p style="font-weight: 500; margin-bottom: 0.3rem;">${fileName}</p>
                            <p style="font-size: 0.85rem; color: #666;">${fileSize} MB</p>
                        `;
                    }
                };
            }
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('active');
        }

        const uploadArea = document.getElementById('uploadArea');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('fileInput').files = files;
                const fileName = files[0].name;
                const fileSize = (files[0].size / 1024 / 1024).toFixed(2);
                uploadArea.innerHTML = `
                    <div class="upload-icon">✅</div>
                    <p style="font-weight: 500; margin-bottom: 0.3rem;">${fileName}</p>
                    <p style="font-size: 0.85rem; color: #666;">${fileSize} MB</p>
                `;
            }
        });

        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const fileInput = document.getElementById('fileInput');
            const title = document.getElementById('titleInput').value.trim();
            const description = document.getElementById('descriptionInput').value.trim();
            const keywords = document.getElementById('keywordsInput').value.trim();
            
            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                alert('Lütfen bir dosya seçin!');
                return;
            }
            
            if (!title) {
                alert('Lütfen bir başlık girin!');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('keywords', keywords);

            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = '⏳ Yükleniyor...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('upload.php', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    alert('Sunucu hatası: ' + responseText.substring(0, 200));
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    return;
                }
                
                if (result.success) {
                    let message = 'Dosya başarıyla yüklendi!\n\n';
                    message += 'Başlık: ' + result.title + '\n';
                    message += 'ID: ' + result.fileId;
                    if (result.compressionRatio > 0) {
                        message += '\n\nSıkıştırma: %' + result.compressionRatio + ' tasarruf';
                    }
                    alert(message);
                    closeUploadModal();
                    location.reload();
                } else {
                    alert('Hata: ' + result.message);
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                alert('Yükleme hatası: ' + error.message);
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });

        window.onclick = function(event) {
            const uploadModal = document.getElementById('uploadModal');
            if (event.target === uploadModal) {
                closeUploadModal();
            }
        }
    </script>
</body>
</html>