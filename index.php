<?php
$dbFile = 'files.json';
$uploadDir = 'uploads/';

// ID parametresi varsa dosya detay sayfasını göster
if (isset($_GET['id'])) {
    $fileId = trim($_GET['id']);
    
    error_log("Aranan ID: " . $fileId);
    
    if (file_exists($dbFile)) {
        $filesContent = file_get_contents($dbFile);
        error_log("JSON içeriği: " . $filesContent);
        
        $files = json_decode($filesContent, true);
        error_log("Decode edilen dosya sayısı: " . (is_array($files) ? count($files) : 0));
        
        $foundFile = null;
        
        if (is_array($files)) {
            foreach ($files as $file) {
                error_log("Karşılaştırma: '" . $file['id'] . "' === '" . $fileId . "'");
                // Hem normal hem de case-insensitive karşılaştırma
                if ($file['id'] === $fileId || strcasecmp($file['id'], $fileId) === 0) {
                    $foundFile = $file;
                    error_log("Dosya BULUNDU!");
                    break;
                }
            }
        }
        
        if ($foundFile) {
            // Dosya detay sayfası
            $isAudio = $foundFile['extension'] === 'mp3';
            $isVideo = in_array($foundFile['extension'], ['mp4', 'm4u']);
            $isImage = in_array($foundFile['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
            $isMedia = $isAudio || $isVideo || $isImage;
            $fileSizeMB = round($foundFile['size'] / 1024 / 1024, 2);
            ?>
            <!DOCTYPE html>
            <html lang="tr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?php echo htmlspecialchars($foundFile['original_name']); ?> - FreeFiles</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
                        min-height: 100vh;
                        color: #333;
                        padding: 2rem;
                    }

                    .container {
                        max-width: 800px;
                        margin: 0 auto;
                        background: rgba(255, 255, 255, 0.95);
                        border-radius: 20px;
                        padding: 3rem;
                        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                    }

                    .back-btn {
                        display: inline-block;
                        margin-bottom: 2rem;
                        color: #667eea;
                        text-decoration: none;
                        font-weight: 600;
                        transition: all 0.3s ease;
                    }

                    .back-btn:hover {
                        transform: translateX(-5px);
                    }

                    .file-header {
                        text-align: center;
                        margin-bottom: 2rem;
                    }

                    .file-icon-large {
                        font-size: 5rem;
                        margin-bottom: 1rem;
                    }

                    h1 {
                        font-size: 2rem;
                        color: #2d3748;
                        margin-bottom: 0.5rem;
                        word-break: break-word;
                    }

                    .file-id-display {
                        font-size: 1rem;
                        color: #667eea;
                        font-family: monospace;
                        background: #f7fafc;
                        padding: 0.5rem 1rem;
                        border-radius: 8px;
                        display: inline-block;
                        margin-top: 0.5rem;
                    }

                    .media-player {
                        width: 100%;
                        margin: 2rem 0;
                        border-radius: 12px;
                        overflow: hidden;
                        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
                    }

                    audio, video {
                        width: 100%;
                        display: block;
                    }

                    .file-info-section {
                        background: #f7fafc;
                        border-radius: 12px;
                        padding: 1.5rem;
                        margin: 2rem 0;
                    }

                    .info-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 0.8rem 0;
                        border-bottom: 1px solid #e2e8f0;
                    }

                    .info-row:last-child {
                        border-bottom: none;
                    }

                    .info-label {
                        font-weight: 600;
                        color: #4a5568;
                    }

                    .info-value {
                        color: #718096;
                    }

                    .download-btn {
                        display: block;
                        width: 100%;
                        padding: 1rem 2rem;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        text-align: center;
                        text-decoration: none;
                        border-radius: 12px;
                        font-weight: 600;
                        font-size: 1.1rem;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                    }

                    .download-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
                    }

                    .comments-section {
                        margin-top: 3rem;
                        padding-top: 2rem;
                        border-top: 2px solid #e2e8f0;
                    }

                    .comments-title {
                        font-size: 1.5rem;
                        font-weight: 700;
                        color: #2d3748;
                        margin-bottom: 1.5rem;
                    }

                    .comment-form {
                        background: #f7fafc;
                        border-radius: 12px;
                        padding: 1.5rem;
                        margin-bottom: 2rem;
                    }

                    .comment-input-wrapper {
                        position: relative;
                    }

                    .anonymous-badge {
                        display: inline-block;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 0.4rem 0.8rem;
                        border-radius: 6px;
                        font-size: 0.85rem;
                        font-weight: 600;
                        margin-bottom: 0.8rem;
                    }

                    .comment-input {
                        width: 100%;
                        padding: 1rem;
                        border: 2px solid #e2e8f0;
                        border-radius: 8px;
                        font-size: 0.95rem;
                        font-family: inherit;
                        resize: vertical;
                        min-height: 80px;
                        transition: all 0.3s ease;
                    }

                    .comment-input:focus {
                        outline: none;
                        border-color: #667eea;
                        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                    }

                    .char-counter {
                        text-align: right;
                        font-size: 0.8rem;
                        color: #718096;
                        margin-top: 0.5rem;
                    }

                    .comment-submit-btn {
                        width: 100%;
                        padding: 0.9rem;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border: none;
                        border-radius: 8px;
                        font-weight: 600;
                        font-size: 1rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        margin-top: 1rem;
                    }

                    .comment-submit-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                    }

                    .comment-submit-btn:disabled {
                        opacity: 0.6;
                        cursor: not-allowed;
                        transform: none;
                    }

                    .comments-list {
                        display: flex;
                        flex-direction: column;
                        gap: 1rem;
                    }

                    .comment-card {
                        background: white;
                        border-radius: 10px;
                        padding: 1.2rem;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                        transition: all 0.3s ease;
                    }

                    .comment-card:hover {
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
                    }

                    .comment-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 0.8rem;
                    }

                    .comment-author {
                        font-weight: 600;
                        color: #667eea;
                        font-size: 0.9rem;
                    }

                    .comment-date {
                        font-size: 0.8rem;
                        color: #a0aec0;
                    }

                    .comment-text {
                        color: #4a5568;
                        line-height: 1.6;
                        font-size: 0.95rem;
                    }

                    .no-comments {
                        text-align: center;
                        padding: 3rem 2rem;
                        color: #718096;
                    }

                    .no-comments-icon {
                        font-size: 3rem;
                        margin-bottom: 1rem;
                        opacity: 0.5;
                    }

                    @media (max-width: 768px) {
                        body {
                            padding: 1rem;
                        }
                        
                        .container {
                            padding: 2rem 1.5rem;
                        }
                        
                        h1 {
                            font-size: 1.5rem;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <a href="/" class="back-btn">← Ana Sayfaya Dön</a>
                    
                    <div class="file-header">
                        <div class="file-icon-large">
                            <?php
                            $icons = [
                                'mp3' => '🎵',
                                'mp4' => '🎬',
                                'm4u' => '🎬',
                                'pdf' => '📄',
                                'doc' => '📝',
                                'docx' => '📝',
                                'jpg' => '🖼️',
                                'jpeg' => '🖼️',
                                'png' => '🖼️',
                                'gif' => '🖼️',
                                'zip' => '📦',
                                'rar' => '📦',
                                'txt' => '📃'
                            ];
                            echo $icons[$foundFile['extension']] ?? '📄';
                            ?>
                        </div>
                        <h1><?php echo htmlspecialchars($foundFile['original_name']); ?></h1>
                        <div class="file-id-display">ID: <?php echo $foundFile['id']; ?></div>
                    </div>

                    <?php if ($isMedia): ?>
                    <div class="media-player">
                        <?php if ($isAudio): ?>
                            <audio controls>
                                <source src="<?php echo $foundFile['path']; ?>" type="audio/mpeg">
                                Tarayıcınız ses oynatmayı desteklemiyor.
                            </audio>
                        <?php elseif ($isVideo): ?>
                            <video controls>
                                <source src="<?php echo $foundFile['path']; ?>" type="video/<?php echo $foundFile['extension'] === 'm4u' ? 'mp4' : $foundFile['extension']; ?>">
                                Tarayıcınız video oynatmayı desteklemiyor.
                            </video>
                        <?php elseif ($isImage): ?>
                            <img src="<?php echo $foundFile['path']; ?>" alt="<?php echo htmlspecialchars($foundFile['original_name']); ?>" style="width: 100%; height: auto; border-radius: 12px; display: block;">
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="file-info-section">
                        <div class="info-row">
                            <span class="info-label">Dosya Adı:</span>
                            <span class="info-value"><?php echo htmlspecialchars($foundFile['original_name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Dosya Türü:</span>
                            <span class="info-value"><?php echo strtoupper($foundFile['extension']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Dosya Boyutu:</span>
                            <span class="info-value"><?php echo $fileSizeMB; ?> MB</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Yüklenme Tarihi:</span>
                            <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($foundFile['upload_date'])); ?></span>
                        </div>
                    </div>

                    <a href="<?php echo $foundFile['path']; ?>" class="download-btn" download="<?php echo htmlspecialchars($foundFile['original_name']); ?>">
                        ⬇️ Dosyayı İndir
                    </a>

                    <!-- Yorum Sistemi -->
                    <div class="comments-section">
                        <h2 class="comments-title">💬 Yorumlar</h2>
                        
                        <?php
                        // Yorumları yükle
                        $commentsFile = 'comments.json';
                        if (!file_exists($commentsFile)) {
                            file_put_contents($commentsFile, json_encode([]));
                        }
                        $allComments = json_decode(file_get_contents($commentsFile), true);
                        $fileComments = array_filter($allComments, function($c) use ($fileId) {
                            return $c['file_id'] === $fileId;
                        });
                        // Tarihe göre sırala (en yeni üstte)
                        usort($fileComments, function($a, $b) {
                            return strtotime($b['date']) - strtotime($a['date']);
                        });
                        ?>

                        <!-- Yorum Yazma Formu -->
                        <form id="commentForm" class="comment-form">
                            <div class="comment-input-wrapper">
                                <div class="anonymous-badge">👤 Anonim</div>
                                <textarea 
                                    id="commentText" 
                                    class="comment-input" 
                                    placeholder="Yorumunuzu yazın..."
                                    maxlength="500"
                                    rows="3"
                                    required
                                ></textarea>
                                <div class="char-counter">
                                    <span id="charCount">0</span>/500
                                </div>
                            </div>
                            <button type="submit" class="comment-submit-btn">📝 Yorum Yap</button>
                        </form>

                        <!-- Yorumlar Listesi -->
                        <div class="comments-list" id="commentsList">
                            <?php if (empty($fileComments)): ?>
                                <div class="no-comments">
                                    <div class="no-comments-icon">💭</div>
                                    <p>Henüz yorum yapılmamış. İlk yorumu sen yap!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($fileComments as $comment): ?>
                                    <div class="comment-card">
                                        <div class="comment-header">
                                            <span class="comment-author">👤 Anonim</span>
                                            <span class="comment-date"><?php 
                                                $time = strtotime($comment['date']);
                                                $diff = time() - $time;
                                                if ($diff < 60) echo 'Az önce';
                                                elseif ($diff < 3600) echo floor($diff/60) . ' dakika önce';
                                                elseif ($diff < 86400) echo floor($diff/3600) . ' saat önce';
                                                elseif ($diff < 604800) echo floor($diff/86400) . ' gün önce';
                                                else echo date('d.m.Y H:i', $time);
                                            ?></span>
                                        </div>
                                        <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['text'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <script>
                    // Karakter sayacı
                    const commentText = document.getElementById('commentText');
                    const charCount = document.getElementById('charCount');
                    
                    commentText.addEventListener('input', function() {
                        charCount.textContent = this.value.length;
                    });

                    // Yorum gönderme
                    document.getElementById('commentForm').addEventListener('submit', async function(e) {
                        e.preventDefault();
                        
                        const text = commentText.value.trim();
                        if (!text) {
                            alert('Lütfen bir yorum yazın!');
                            return;
                        }

                        const submitBtn = this.querySelector('button[type="submit"]');
                        const originalText = submitBtn.textContent;
                        submitBtn.textContent = 'Gönderiliyor...';
                        submitBtn.disabled = true;

                        try {
                            const response = await fetch('add_comment.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    file_id: '<?php echo $fileId; ?>',
                                    text: text
                                })
                            });

                            const result = await response.json();
                            
                            if (result.success) {
                                location.reload(); // Sayfayı yenile
                            } else {
                                alert('Hata: ' + result.message);
                                submitBtn.textContent = originalText;
                                submitBtn.disabled = false;
                            }
                        } catch (error) {
                            alert('Yorum gönderilemedi: ' + error.message);
                            submitBtn.textContent = originalText;
                            submitBtn.disabled = false;
                        }
                    });
                </script>
            </body>
            </html>
            <?php
            exit;
        } else {
            echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Dosya Bulunamadı</title></head><body style='font-family: Arial; text-align: center; padding: 50px;'><h1>😕 Dosya Bulunamadı</h1><p>Bu ID'ye ait dosya mevcut değil.</p><a href='/' style='color: #667eea; text-decoration: none; font-weight: bold;'>← Ana Sayfaya Dön</a></body></html>";
            exit;
        }
    }
}

// Ana sayfa - dosya listesi
$files = [];
if (file_exists($dbFile)) {
    $files = json_decode(file_get_contents($dbFile), true);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreeFiles - Dünyanın En Özgür Dosya Paylaşım Sitesi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            min-height: 100vh;
            color: #333;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.2rem 2rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .hero {
            text-align: center;
            padding: 3rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2d3748;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.2rem;
            color: #4a5568;
            margin-bottom: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .files-panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .panel-header {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .file-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .file-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: #667eea;
        }

        .file-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: white;
        }

        .file-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-info {
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 0.3rem;
        }

        .file-id {
            font-size: 0.8rem;
            color: #667eea;
            font-family: monospace;
            background: #f7fafc;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            display: inline-block;
            margin-top: 0.5rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1.5rem;
        }

        .upload-area {
            border: 3px dashed #cbd5e0;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .upload-area:hover {
            border-color: #667eea;
            background: #edf2f7;
        }

        .upload-area.dragover {
            border-color: #667eea;
            background: #e6f2ff;
        }

        .upload-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        input[type="file"] {
            display: none;
        }

        .close-btn {
            float: right;
            font-size: 2rem;
            font-weight: 300;
            color: #718096;
            cursor: pointer;
            line-height: 1;
            margin-top: -1rem;
        }

        .close-btn:hover {
            color: #2d3748;
        }

        .search-area {
            margin-bottom: 2rem;
        }

        .search-input {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #718096;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .file-grid {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">FreeFiles</div>
        <div class="nav-buttons">
            <button class="btn btn-primary" onclick="openUploadModal()">📤 Dosya Yükle</button>
            <button class="btn btn-secondary" onclick="openSearchModal()">🔍 Dosya Arat</button>
        </div>
    </nav>

    <div class="hero">
        <h1>FreeFiles, Dünyanın En Özgür Dosya Paylaşım Sitesi.</h1>
        <p>Dosyalarınızı yükleyin, paylaşın ve özgürce erişin.</p>
    </div>

    <div class="container">
        <div class="files-panel">
            <div class="panel-header">
                📁 En Yeni Yüklenen Dosyalar
            </div>
            <div class="file-grid">
                <?php if (empty($files)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>Henüz hiç dosya yüklenmemiş. İlk sen ol!</p>
                </div>
                <?php else: ?>
                    <?php foreach ($files as $file): 
                        $icons = [
                            'mp3' => '🎵',
                            'mp4' => '🎬',
                            'm4u' => '🎬',
                            'pdf' => '📄',
                            'doc' => '📝',
                            'docx' => '📝',
                            'jpg' => '🖼️',
                            'jpeg' => '🖼️',
                            'png' => '🖼️',
                            'gif' => '🎨',
                            'webp' => '🖼️',
                            'bmp' => '🖼️',
                            'svg' => '🎨',
                            'zip' => '📦',
                            'rar' => '📦',
                            'txt' => '📃'
                        ];
                        $icon = $icons[$file['extension']] ?? '📄';
                        $fileSizeMB = round($file['size'] / 1024 / 1024, 2);
                    ?>
                    <a href="?id=<?php echo $file['id']; ?>" class="file-card">
                        <div class="file-icon"><?php echo $icon; ?></div>
                        <div class="file-name"><?php echo htmlspecialchars($file['original_name']); ?></div>
                        <div class="file-info">Boyut: <?php echo $fileSizeMB; ?> MB</div>
                        <div class="file-info">Tür: <?php echo strtoupper($file['extension']); ?></div>
                        <div class="file-info">Tarih: <?php echo date('d.m.Y', strtotime($file['upload_date'])); ?></div>
                        <div class="file-id">ID: <?php echo $file['id']; ?></div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal" id="uploadModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeUploadModal()">&times;</span>
            <div class="modal-header">Dosya Yükle</div>
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="file" id="fileInput" name="file" style="display: none;">
                <div class="upload-area" id="uploadArea" onclick="triggerFileInput()">
                    <div class="upload-icon">☁️</div>
                    <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Dosya Seç veya Sürükle</p>
                    <p style="font-size: 0.9rem; color: #718096;">Tüm dosya türleri desteklenir</p>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem;">Yükle</button>
            </form>
        </div>
    </div>

    <!-- Search Modal -->
    <div class="modal" id="searchModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeSearchModal()">&times;</span>
            <div class="modal-header">Dosya Ara</div>
            <div class="search-area">
                <input type="text" class="search-input" id="searchInput" placeholder="Dosya ID'si girin..." onkeypress="handleSearchKeypress(event)">
                <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="searchFile()">Ara</button>
            </div>
        </div>
    </div>

    <script>
        function triggerFileInput() {
            const fileInput = document.getElementById('fileInput');
            if (fileInput) {
                fileInput.click();
            }
        }

        function openUploadModal() {
            document.getElementById('uploadModal').classList.add('active');
            // Input change event'i ekle
            const fileInput = document.getElementById('fileInput');
            if (fileInput) {
                fileInput.onchange = function() {
                    handleFileSelect(this);
                };
            }
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('active');
        }

        function openSearchModal() {
            document.getElementById('searchModal').classList.add('active');
        }

        function closeSearchModal() {
            document.getElementById('searchModal').classList.remove('active');
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
                handleFileSelect(document.getElementById('fileInput'));
            }
        });

        function handleFileSelect(input) {
            if (input && input.files && input.files[0]) {
                const fileName = input.files[0].name;
                const uploadArea = document.getElementById('uploadArea');
                uploadArea.innerHTML = `
                    <div class="upload-icon">✅</div>
                    <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">${fileName}</p>
                    <p style="font-size: 0.9rem; color: #718096;">Yüklemek için butona tıklayın</p>
                `;
            }
        }

        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fileInput = document.getElementById('fileInput');
            
            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                alert('Lütfen bir dosya seçin!');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            // Debug için
            console.log('Dosya seçildi:', fileInput.files[0].name);
            console.log('FormData içeriği:', formData.get('file'));

            // Yükleme göstergesi
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Yükleniyor...';
            submitBtn.disabled = true;

            try {
                console.log('Dosya gönderiliyor...');
                const response = await fetch('upload.php', {
                    method: 'POST',
                    body: formData
                    // NOT: Content-Type header'ı ekleme! FormData otomatik ayarlıyor
                });

                console.log('Response status:', response.status);
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('JSON parse hatası:', e);
                    alert('Sunucu hatası: ' + responseText.substring(0, 200));
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    return;
                }
                
                if (result.success) {
                    alert('Dosya başarıyla yüklendi!\nDosya ID: ' + result.fileId);
                    closeUploadModal();
                    location.reload();
                } else {
                    alert('Hata: ' + result.message);
                    if (result.debug) {
                        console.log('Debug bilgisi:', result.debug);
                    }
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Fetch hatası:', error);
                alert('Yükleme hatası: ' + error.message);
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });

        function handleSearchKeypress(event) {
            if (event.key === 'Enter') {
                searchFile();
            }
        }

        function searchFile() {
            const fileId = document.getElementById('searchInput').value.trim();
            if (fileId) {
                window.location.href = '?id=' + fileId;
            } else {
                alert('Lütfen bir dosya ID\'si girin!');
            }
        }

        window.onclick = function(event) {
            const uploadModal = document.getElementById('uploadModal');
            const searchModal = document.getElementById('searchModal');
            if (event.target === uploadModal) {
                closeUploadModal();
            }
            if (event.target === searchModal) {
                closeSearchModal();
            }
        }
    </script>
</body>
</html>