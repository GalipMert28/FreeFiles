<?php
$dbFile = 'files.json';
$uploadDir = 'uploads/';
$likesFile = 'likes.json';

// Beğeni dosyası yoksa oluştur
if (!file_exists($likesFile)) {
    file_put_contents($likesFile, json_encode(['files' => [], 'comments' => []]));
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
            // Dosya detay sayfası
            $isAudio = $foundFile['extension'] === 'mp3';
            $isVideo = in_array($foundFile['extension'], ['mp4', 'm4u', 'webm', 'avi', 'mov']);
            $isImage = in_array($foundFile['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
            $isText = in_array($foundFile['extension'], ['txt', 'md', 'log', 'json', 'xml', 'html', 'css', 'js', 'php']);
            $isMedia = $isAudio || $isVideo || $isImage;
            $fileSizeMB = round($foundFile['size'] / 1024 / 1024, 2);
            
            // Beğeni sayısını al
            $likes = json_decode(file_get_contents($likesFile), true);
            $fileLikes = isset($likes['files'][$fileId]) ? $likes['files'][$fileId] : 0;
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
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                        background: #1a1a1a;
                        color: #e0e0e0;
                        min-height: 100vh;
                        line-height: 1.6;
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
                        max-width: 1000px;
                        margin: 0 auto;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }

                    .logo {
                        font-size: 1.4rem;
                        font-weight: 600;
                        color: #4a9eff;
                        text-decoration: none;
                    }

                    .back-btn {
                        color: #4a9eff;
                        text-decoration: none;
                        font-size: 0.9rem;
                        padding: 0.5rem 1rem;
                        border: 1px solid #3a3a3a;
                        border-radius: 4px;
                        transition: all 0.2s;
                    }

                    .back-btn:hover {
                        background: #3a3a3a;
                    }

                    .container {
                        max-width: 1000px;
                        margin: 2rem auto;
                        padding: 0 2rem;
                    }

                    .content-box {
                        background: #2a2a2a;
                        border: 1px solid #3a3a3a;
                        border-radius: 4px;
                        padding: 2rem;
                        margin-bottom: 1.5rem;
                    }

                    h1 {
                        font-size: 1.8rem;
                        color: #fff;
                        margin-bottom: 0.5rem;
                        font-weight: 600;
                        word-break: break-word;
                    }

                    .file-meta {
                        display: flex;
                        gap: 1.5rem;
                        margin: 1rem 0;
                        font-size: 0.85rem;
                        color: #999;
                        flex-wrap: wrap;
                    }

                    .file-meta-item {
                        display: flex;
                        align-items: center;
                        gap: 0.4rem;
                    }

                    .media-player {
                        width: 100%;
                        margin: 1.5rem 0;
                        background: #1a1a1a;
                        border: 1px solid #3a3a3a;
                        border-radius: 4px;
                        overflow: hidden;
                    }

                    audio, video, img {
                        width: 100%;
                        display: block;
                    }

                    .text-viewer {
                        background: #1a1a1a;
                        border: 1px solid #3a3a3a;
                        border-radius: 4px;
                        padding: 1.5rem;
                        margin: 1.5rem 0;
                        max-height: 500px;
                        overflow-y: auto;
                        font-family: 'Courier New', monospace;
                        font-size: 0.9rem;
                        line-height: 1.5;
                        white-space: pre-wrap;
                        word-wrap: break-word;
                    }

                    .action-bar {
                        display: flex;
                        gap: 1rem;
                        margin: 1.5rem 0;
                        padding: 1rem 0;
                        border-top: 1px solid #3a3a3a;
                        border-bottom: 1px solid #3a3a3a;
                    }

                    .action-btn {
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                        padding: 0.6rem 1rem;
                        background: transparent;
                        border: 1px solid #3a3a3a;
                        border-radius: 4px;
                        color: #e0e0e0;
                        cursor: pointer;
                        font-size: 0.9rem;
                        transition: all 0.2s;
                    }

                    .action-btn:hover {
                        background: #3a3a3a;
                    }

                    .action-btn.liked {
                        background: #4a9eff;
                        border-color: #4a9eff;
                        color: #fff;
                    }

                    .download-btn {
                        flex: 1;
                        text-align: center;
                        padding: 0.8rem;
                        background: #4a9eff;
                        color: #fff;
                        text-decoration: none;
                        border-radius: 4px;
                        font-weight: 500;
                        transition: all 0.2s;
                    }

                    .download-btn:hover {
                        background: #3a8eef;
                    }

                    .section-title {
                        font-size: 1.2rem;
                        font-weight: 600;
                        color: #fff;
                        margin-bottom: 1rem;
                        padding-bottom: 0.5rem;
                        border-bottom: 1px solid #3a3a3a;
                    }

                    .comment-form {
                        margin-bottom: 2rem;
                    }

                    .comment-input {
                        width: 100%;
                        padding: 0.8rem;
                        background: #1a1a1a;
                        border: 1px solid #3a3a3a;
                        border-radius: 4px;
                        color: #e0e0e0;
                        font-family: inherit;
                        font-size: 0.9rem;
                        resize: vertical;
                        min-height: 80px;
                    }

                    .comment-input:focus {
                        outline: none;
                        border-color: #4a9eff;
                    }

                    .form-footer {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-top: 0.5rem;
                    }

                    .char-counter {
                        font-size: 0.8rem;
                        color: #666;
                    }

                    .submit-btn {
                        padding: 0.6rem 1.5rem;
                        background: #4a9eff;
                        color: #fff;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 0.9rem;
                        font-weight: 500;
                        transition: all 0.2s;
                    }

                    .submit-btn:hover {
                        background: #3a8eef;
                    }

                    .submit-btn:disabled {
                        opacity: 0.5;
                        cursor: not-allowed;
                    }

                    .comment-card {
                        background: #2a2a2a;
                        border: 1px solid #3a3a3a;
                        border-radius: 4px;
                        padding: 1rem;
                        margin-bottom: 1rem;
                    }

                    .comment-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 0.8rem;
                    }

                    .comment-author {
                        font-weight: 500;
                        color: #4a9eff;
                        font-size: 0.9rem;
                    }

                    .comment-date {
                        font-size: 0.8rem;
                        color: #666;
                    }

                    .comment-text {
                        color: #e0e0e0;
                        line-height: 1.6;
                        margin-bottom: 0.8rem;
                        white-space: pre-wrap;
                    }

                    .comment-actions {
                        display: flex;
                        gap: 1rem;
                    }

                    .comment-action-btn {
                        background: transparent;
                        border: none;
                        color: #999;
                        cursor: pointer;
                        font-size: 0.85rem;
                        display: flex;
                        align-items: center;
                        gap: 0.3rem;
                        transition: all 0.2s;
                        padding: 0.3rem 0.5rem;
                        border-radius: 3px;
                    }

                    .comment-action-btn:hover {
                        background: #3a3a3a;
                        color: #e0e0e0;
                    }

                    .comment-action-btn.liked {
                        color: #4a9eff;
                    }

                    .reply-form {
                        margin-top: 1rem;
                        padding-left: 1rem;
                        border-left: 2px solid #3a3a3a;
                    }

                    .replies {
                        margin-top: 1rem;
                        padding-left: 2rem;
                        border-left: 2px solid #3a3a3a;
                    }

                    .reply-card {
                        background: #1a1a1a;
                        border: 1px solid #3a3a3a;
                        border-radius: 4px;
                        padding: 0.8rem;
                        margin-bottom: 0.8rem;
                    }

                    .no-comments {
                        text-align: center;
                        padding: 3rem;
                        color: #666;
                    }

                    @media (max-width: 768px) {
                        .container {
                            padding: 0 1rem;
                        }
                        
                        .content-box {
                            padding: 1.5rem;
                        }
                        
                        h1 {
                            font-size: 1.4rem;
                        }

                        .action-bar {
                            flex-wrap: wrap;
                        }
                    }
                </style>
            </head>
            <body>
                <nav class="navbar">
                    <div class="nav-content">
                        <a href="/" class="logo">FreeFiles</a>
                        <a href="/" class="back-btn">← Geri Dön</a>
                    </div>
                </nav>

                <div class="container">
                    <div class="content-box">
                        <h1><?php echo htmlspecialchars($foundFile['original_name']); ?></h1>
                        
                        <div class="file-meta">
                            <span class="file-meta-item">
                                <span>📁</span>
                                <span><?php echo strtoupper($foundFile['extension']); ?></span>
                            </span>
                            <span class="file-meta-item">
                                <span>💾</span>
                                <span><?php echo $fileSizeMB; ?> MB</span>
                            </span>
                            <span class="file-meta-item">
                                <span>📅</span>
                                <span><?php echo date('d.m.Y H:i', strtotime($foundFile['upload_date'])); ?></span>
                            </span>
                            <span class="file-meta-item">
                                <span>🔑</span>
                                <span><?php echo $foundFile['id']; ?></span>
                            </span>
                        </div>

                        <?php if ($isMedia): ?>
                        <div class="media-player">
                            <?php if ($isAudio): ?>
                                <audio controls>
                                    <source src="<?php echo $foundFile['path']; ?>" type="audio/mpeg">
                                </audio>
                            <?php elseif ($isVideo): ?>
                                <video controls>
                                    <source src="<?php echo $foundFile['path']; ?>" type="video/<?php echo $foundFile['extension']; ?>">
                                </video>
                            <?php elseif ($isImage): ?>
                                <img src="<?php echo $foundFile['path']; ?>" alt="<?php echo htmlspecialchars($foundFile['original_name']); ?>">
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($isText && filesize($foundFile['path']) < 1000000): ?>
                        <div class="text-viewer"><?php echo htmlspecialchars(file_get_contents($foundFile['path'])); ?></div>
                        <?php endif; ?>

                        <div class="action-bar">
                            <button class="action-btn" id="likeBtn" data-file-id="<?php echo $fileId; ?>">
                                <span id="likeIcon">👍</span>
                                <span id="likeCount"><?php echo $fileLikes; ?></span>
                            </button>
                            <a href="<?php echo $foundFile['path']; ?>" class="download-btn" download="<?php echo htmlspecialchars($foundFile['original_name']); ?>">
                                ⬇️ İndir
                            </a>
                        </div>
                    </div>

                    <div class="content-box">
                        <h2 class="section-title">💬 Yorumlar</h2>
                        
                        <?php
                        $commentsFile = 'comments.json';
                        if (!file_exists($commentsFile)) {
                            file_put_contents($commentsFile, json_encode([]));
                        }
                        $allComments = json_decode(file_get_contents($commentsFile), true);
                        $fileComments = array_filter($allComments, function($c) use ($fileId) {
                            return isset($c['file_id']) && $c['file_id'] === $fileId && (!isset($c['parent_id']) || $c['parent_id'] === null);
                        });
                        usort($fileComments, function($a, $b) {
                            return strtotime($b['date']) - strtotime($a['date']);
                        });
                        
                        $commentLikes = $likes['comments'] ?? [];
                        ?>

                        <form class="comment-form" id="mainCommentForm">
                            <textarea class="comment-input" id="commentText" placeholder="Yorum yaz... (Anonim)" maxlength="500" required></textarea>
                            <div class="form-footer">
                                <span class="char-counter"><span id="charCount">0</span>/500</span>
                                <button type="submit" class="submit-btn">Gönder</button>
                            </div>
                        </form>

                        <div id="commentsList">
                            <?php if (empty($fileComments)): ?>
                                <div class="no-comments">Henüz yorum yok. İlk yorumu sen yap!</div>
                            <?php else: ?>
                                <?php foreach ($fileComments as $comment): 
                                    $commentId = $comment['id'];
                                    $commentLikeCount = isset($commentLikes[$commentId]) ? $commentLikes[$commentId] : 0;
                                    
                                    // Yanıtları bul
                                    $replies = array_filter($allComments, function($c) use ($commentId) {
                                        return isset($c['parent_id']) && $c['parent_id'] === $commentId;
                                    });
                                    usort($replies, function($a, $b) {
                                        return strtotime($a['date']) - strtotime($b['date']);
                                    });
                                ?>
                                <div class="comment-card">
                                    <div class="comment-header">
                                        <span class="comment-author">Anonim</span>
                                        <span class="comment-date"><?php 
                                            $time = strtotime($comment['date']);
                                            $diff = time() - $time;
                                            if ($diff < 60) echo 'Az önce';
                                            elseif ($diff < 3600) echo floor($diff/60) . ' dk önce';
                                            elseif ($diff < 86400) echo floor($diff/3600) . ' sa önce';
                                            elseif ($diff < 604800) echo floor($diff/86400) . ' gün önce';
                                            else echo date('d.m.Y H:i', $time);
                                        ?></span>
                                    </div>
                                    <div class="comment-text"><?php echo htmlspecialchars($comment['text']); ?></div>
                                    <div class="comment-actions">
                                        <button class="comment-action-btn like-comment-btn" data-comment-id="<?php echo $commentId; ?>">
                                            <span class="like-icon">👍</span>
                                            <span class="like-count"><?php echo $commentLikeCount; ?></span>
                                        </button>
                                        <button class="comment-action-btn reply-btn" data-comment-id="<?php echo $commentId; ?>">
                                            💬 Yanıtla
                                        </button>
                                    </div>
                                    
                                    <div class="reply-form" id="replyForm-<?php echo $commentId; ?>" style="display: none;">
                                        <textarea class="comment-input" placeholder="Yanıt yaz..." maxlength="500"></textarea>
                                        <div class="form-footer">
                                            <span class="char-counter"><span class="reply-char-count">0</span>/500</span>
                                            <button class="submit-btn submit-reply-btn" data-parent-id="<?php echo $commentId; ?>">Yanıtla</button>
                                        </div>
                                    </div>

                                    <?php if (!empty($replies)): ?>
                                    <div class="replies">
                                        <?php foreach ($replies as $reply): 
                                            $replyId = $reply['id'];
                                            $replyLikeCount = isset($commentLikes[$replyId]) ? $commentLikes[$replyId] : 0;
                                        ?>
                                        <div class="reply-card">
                                            <div class="comment-header">
                                                <span class="comment-author">Anonim</span>
                                                <span class="comment-date"><?php 
                                                    $time = strtotime($reply['date']);
                                                    $diff = time() - $time;
                                                    if ($diff < 60) echo 'Az önce';
                                                    elseif ($diff < 3600) echo floor($diff/60) . ' dk önce';
                                                    elseif ($diff < 86400) echo floor($diff/3600) . ' sa önce';
                                                    else echo date('d.m.Y', $time);
                                                ?></span>
                                            </div>
                                            <div class="comment-text"><?php echo htmlspecialchars($reply['text']); ?></div>
                                            <div class="comment-actions">
                                                <button class="comment-action-btn like-comment-btn" data-comment-id="<?php echo $replyId; ?>">
                                                    <span class="like-icon">👍</span>
                                                    <span class="like-count"><?php echo $replyLikeCount; ?></span>
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <script>
                    const fileId = '<?php echo $fileId; ?>';
                    
                    // Karakter sayacı
                    document.getElementById('commentText').addEventListener('input', function() {
                        document.getElementById('charCount').textContent = this.value.length;
                    });

                    document.querySelectorAll('.reply-form textarea').forEach(textarea => {
                        textarea.addEventListener('input', function() {
                            this.closest('.reply-form').querySelector('.reply-char-count').textContent = this.value.length;
                        });
                    });

                    // Dosya beğenme
                    document.getElementById('likeBtn').addEventListener('click', async function() {
                        try {
                            const response = await fetch('like.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ type: 'file', id: fileId })
                            });
                            const result = await response.json();
                            if (result.success) {
                                document.getElementById('likeCount').textContent = result.count;
                                this.classList.toggle('liked');
                            }
                        } catch (error) {
                            console.error('Beğeni hatası:', error);
                        }
                    });

                    // Yorum gönderme
                    document.getElementById('mainCommentForm').addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const text = document.getElementById('commentText').value.trim();
                        if (!text) return;

                        try {
                            const response = await fetch('add_comment.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ file_id: fileId, text: text })
                            });
                            const result = await response.json();
                            if (result.success) location.reload();
                        } catch (error) {
                            alert('Hata: ' + error.message);
                        }
                    });

                    // Yorum beğenme
                    document.querySelectorAll('.like-comment-btn').forEach(btn => {
                        btn.addEventListener('click', async function() {
                            const commentId = this.dataset.commentId;
                            try {
                                const response = await fetch('like.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ type: 'comment', id: commentId })
                                });
                                const result = await response.json();
                                if (result.success) {
                                    this.querySelector('.like-count').textContent = result.count;
                                    this.classList.toggle('liked');
                                }
                            } catch (error) {
                                console.error('Beğeni hatası:', error);
                            }
                        });
                    });

                    // Yanıtlama
                    document.querySelectorAll('.reply-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const commentId = this.dataset.commentId;
                            const replyForm = document.getElementById('replyForm-' + commentId);
                            replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
                        });
                    });

                    document.querySelectorAll('.submit-reply-btn').forEach(btn => {
                        btn.addEventListener('click', async function() {
                            const parentId = this.dataset.parentId;
                            const textarea = this.closest('.reply-form').querySelector('textarea');
                            const text = textarea.value.trim();
                            if (!text) return;

                            try {
                                const response = await fetch('add_comment.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ file_id: fileId, text: text, parent_id: parentId })
                                });
                                const result = await response.json();
                                if (result.success) location.reload();
                            } catch (error) {
                                alert('Hata: ' + error.message);
                            }
                        });
                    });
                </script>
            </body>
            </html>
            <?php
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

// Beğeni sayılarını al
$likes = json_decode(file_get_contents($likesFile), true);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreeFiles - Özgür Dosya Paylaşımı</title>
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
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.4rem;
            font-weight: 600;
            color: #4a9eff;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            background: transparent;
            color: #e0e0e0;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
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
            max-width: 1200px;
            margin: 3rem auto 2rem;
            padding: 0 2rem;
            text-align: center;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .hero p {
            font-size: 1.1rem;
            color: #999;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto 3rem;
            padding: 0 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #3a3a3a;
        }

        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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
            height: 180px;
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
            font-size: 3rem;
        }

        .file-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
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
            font-size: 2.5rem;
            color: #fff;
        }

        .file-info {
            padding: 1rem;
        }

        .file-name {
            font-weight: 500;
            color: #e0e0e0;
            margin-bottom: 0.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .file-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.5rem;
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
            background: rgba(0, 0, 0, 0.8);
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
            max-width: 500px;
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
            font-size: 1.3rem;
            font-weight: 600;
            color: #fff;
        }

        .close-btn {
            background: none;
            border: none;
            color: #999;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            color: #fff;
        }

        .upload-area {
            border: 2px dashed #3a3a3a;
            border-radius: 4px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #1a1a1a;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: #4a9eff;
            background: #252525;
        }

        .upload-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        input[type="file"],
        input[type="text"] {
            display: none;
        }

        .search-input {
            display: block !important;
            width: 100%;
            padding: 0.8rem;
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
            <div class="logo">FreeFiles</div>
            <div class="nav-buttons">
                <button class="btn btn-primary" onclick="openUploadModal()">📤 Yükle</button>
                <button class="btn" onclick="openSearchModal()">🔍 Ara</button>
            </div>
        </div>
    </nav>

    <div class="hero">
        <h1>FreeFiles</h1>
        <p>Özgür dosya paylaşım platformu</p>
    </div>

    <div class="container">
        <h2 class="section-title">📂 Son Yüklenenler</h2>
        <div class="file-grid">
            <?php if (empty($files)): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <div class="empty-icon">📭</div>
                    <p>Henüz dosya yok. İlk sen yükle!</p>
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
                    $fileSizeMB = round($file['size'] / 1024 / 1024, 2);
                    $fileId = $file['id'];
                    $fileLikes = isset($likes['files'][$fileId]) ? $likes['files'][$fileId] : 0;
                    
                    $isImage = in_array($file['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                    $isVideo = in_array($file['extension'], ['mp4', 'm4u', 'webm', 'avi', 'mov']);
                ?>
                <a href="?id=<?php echo $file['id']; ?>" class="file-card">
                    <div class="file-preview">
                        <?php if ($isImage): ?>
                            <img src="<?php echo $file['path']; ?>" alt="<?php echo htmlspecialchars($file['original_name']); ?>">
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
                        <div class="file-name"><?php echo htmlspecialchars($file['original_name']); ?></div>
                        <div class="file-meta">
                            <span><?php echo strtoupper($file['extension']); ?></span>
                            <span><?php echo $fileSizeMB; ?> MB</span>
                        </div>
                        <div class="file-stats">
                            <span class="stat-item">
                                <span>👍</span>
                                <span><?php echo $fileLikes; ?></span>
                            </span>
                            <span class="stat-item">
                                <span>📅</span>
                                <span><?php echo date('d.m.Y', strtotime($file['upload_date'])); ?></span>
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
                <input type="file" id="fileInput" name="file">
                <div class="upload-area" id="uploadArea" onclick="triggerFileInput()">
                    <div class="upload-icon">☁️</div>
                    <p style="font-weight: 500; margin-bottom: 0.5rem;">Dosya Seç veya Sürükle</p>
                    <p style="font-size: 0.85rem; color: #666;">Tüm dosya türleri desteklenir</p>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Yükle</button>
            </form>
        </div>
    </div>

    <!-- Search Modal -->
    <div class="modal" id="searchModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Dosya Ara</div>
                <button class="close-btn" onclick="closeSearchModal()">×</button>
            </div>
            <input type="text" class="search-input" id="searchInput" placeholder="Dosya ID'si girin..." onkeypress="handleSearchKeypress(event)">
            <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="searchFile()">Ara</button>
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
                        document.getElementById('uploadArea').innerHTML = `
                            <div class="upload-icon">✅</div>
                            <p style="font-weight: 500; margin-bottom: 0.5rem;">${fileName}</p>
                            <p style="font-size: 0.85rem; color: #666;">Yüklemek için butona tıkla</p>
                        `;
                    }
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
                const fileName = files[0].name;
                uploadArea.innerHTML = `
                    <div class="upload-icon">✅</div>
                    <p style="font-weight: 500; margin-bottom: 0.5rem;">${fileName}</p>
                    <p style="font-size: 0.85rem; color: #666;">Yüklemek için butona tıkla</p>
                `;
            }
        });

        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fileInput = document.getElementById('fileInput');
            
            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                alert('Lütfen bir dosya seçin!');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Yükleniyor...';
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
                    alert('Dosya yüklendi!\nID: ' + result.fileId);
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