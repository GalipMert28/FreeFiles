<?php
// Bu dosya index.php'den include ediliyor
// $foundFile, $isMedia, $isAudio, $isVideo, $isImage, $isText değişkenleri mevcut
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($foundFile['title'] ?? $foundFile['original_name']); ?> - FreeFiles</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #1a1a1a; color: #e0e0e0; min-height: 100vh; line-height: 1.6; }
        .navbar { background: #2a2a2a; border-bottom: 1px solid #3a3a3a; padding: 1rem 2rem; position: sticky; top: 0; z-index: 1000; }
        .nav-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.4rem; font-weight: 600; color: #4a9eff; text-decoration: none; }
        .back-btn { color: #4a9eff; text-decoration: none; padding: 0.5rem 1rem; border: 1px solid #3a3a3a; border-radius: 4px; transition: all 0.2s; }
        .back-btn:hover { background: #3a3a3a; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .content-box { background: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 4px; padding: 2rem; margin-bottom: 1.5rem; }
        h1 { font-size: 2rem; color: #fff; margin-bottom: 1rem; font-weight: 600; }
        .file-meta { display: flex; gap: 2rem; margin: 1rem 0; font-size: 0.85rem; color: #999; flex-wrap: wrap; padding-bottom: 1rem; border-bottom: 1px solid #3a3a3a; }
        .meta-item { display: flex; align-items: center; gap: 0.4rem; }
        .file-description { color: #ccc; margin: 1rem 0; line-height: 1.6; }
        .file-keywords { display: flex; flex-wrap: wrap; gap: 0.5rem; margin: 1rem 0; }
        .keyword-tag { padding: 0.4rem 0.8rem; background: #3a3a3a; border-radius: 4px; font-size: 0.85rem; color: #4a9eff; }
        .media-player { width: 100%; margin: 1.5rem 0; background: #1a1a1a; border: 1px solid #3a3a3a; border-radius: 4px; overflow: hidden; }
        audio, video, img { width: 100%; display: block; }
        .text-viewer { background: #1a1a1a; border: 1px solid #3a3a3a; border-radius: 4px; padding: 1.5rem; margin: 1.5rem 0; max-height: 500px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 0.85rem; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; }
        .action-bar { display: flex; gap: 1rem; margin: 1.5rem 0; padding: 1rem 0; border-top: 1px solid #3a3a3a; flex-wrap: wrap; }
        .action-btn { display: flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.2rem; background: transparent; border: 1px solid #3a3a3a; border-radius: 4px; color: #e0e0e0; cursor: pointer; font-size: 0.9rem; transition: all 0.2s; }
        .action-btn:hover { background: #3a3a3a; }
        .action-btn.active { background: #4a9eff; border-color: #4a9eff; color: #fff; }
        .action-btn.dislike-active { background: #ff4a4a; border-color: #ff4a4a; color: #fff; }
        .download-btn { flex: 1; text-align: center; padding: 0.9rem; background: #4a9eff; color: #fff; text-decoration: none; border-radius: 4px; font-weight: 500; transition: all 0.2s; min-width: 150px; }
        .download-btn:hover { background: #3a8eef; }
        .section-title { font-size: 1.3rem; font-weight: 600; color: #fff; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #3a3a3a; }
        .comment-form { margin-bottom: 2rem; }
        .comment-input { width: 100%; padding: 0.9rem; background: #1a1a1a; border: 1px solid #3a3a3a; border-radius: 4px; color: #e0e0e0; font-family: inherit; font-size: 0.9rem; resize: vertical; min-height: 80px; }
        .comment-input:focus { outline: none; border-color: #4a9eff; }
        .form-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; }
        .char-counter { font-size: 0.8rem; color: #666; }
        .submit-btn { padding: 0.7rem 1.5rem; background: #4a9eff; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; transition: all 0.2s; }
        .submit-btn:hover { background: #3a8eef; }
        .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .comment-card { background: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 4px; padding: 1rem; margin-bottom: 1rem; }
        .comment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; }
        .comment-author { font-weight: 500; color: #4a9eff; font-size: 0.9rem; }
        .comment-date { font-size: 0.8rem; color: #666; }
        .comment-text { color: #e0e0e0; line-height: 1.6; margin-bottom: 0.8rem; white-space: pre-wrap; }
        .comment-actions { display: flex; gap: 1rem; }
        .comment-action-btn { background: transparent; border: none; color: #999; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 0.3rem; transition: all 0.2s; padding: 0.3rem 0.6rem; border-radius: 3px; }
        .comment-action-btn:hover { background: #3a3a3a; color: #e0e0e0; }
        .comment-action-btn.liked { color: #4a9eff; }
        .comment-action-btn.disliked { color: #ff4a4a; }
        .reply-form { margin-top: 1rem; padding-left: 1rem; border-left: 2px solid #3a3a3a; }
        .replies { margin-top: 1rem; padding-left: 2rem; border-left: 2px solid #3a3a3a; }
        .reply-card { background: #1a1a1a; border: 1px solid #3a3a3a; border-radius: 4px; padding: 0.9rem; margin-bottom: 0.8rem; }
        .no-comments { text-align: center; padding: 3rem; color: #666; }
        @media (max-width: 768px) {
            .container { padding: 0 1rem; }
            .content-box { padding: 1.5rem; }
            h1 { font-size: 1.5rem; }
            .action-bar { flex-direction: column; }
            .download-btn { width: 100%; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="/" class="logo">FreeFiles</a>
            <a href="/" class="back-btn">← Geri</a>
        </div>
    </nav>

    <div class="container">
        <div class="content-box">
            <h1><?php echo htmlspecialchars($foundFile['title'] ?? $foundFile['original_name']); ?></h1>
            
            <div class="file-meta">
                <span class="meta-item"><span>📁</span><?php echo strtoupper($foundFile['extension']); ?></span>
                <span class="meta-item"><span>💾</span><?php echo $fileSizeMB; ?> MB</span>
                <span class="meta-item"><span>👁️</span><?php echo $foundFile['views']; ?> görüntülenme</span>
                <span class="meta-item"><span>📅</span><?php echo date('d.m.Y H:i', strtotime($foundFile['upload_date'])); ?></span>
                <span class="meta-item"><span>🔑</span><?php echo $foundFile['id']; ?></span>
                <?php if (isset($foundFile['compression_ratio']) && $foundFile['compression_ratio'] > 0): ?>
                <span class="meta-item"><span>📦</span>%<?php echo $foundFile['compression_ratio']; ?> sıkıştırma</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($foundFile['description'])): ?>
            <div class="file-description"><?php echo nl2br(htmlspecialchars($foundFile['description'])); ?></div>
            <?php endif; ?>

            <?php if (!empty($foundFile['keywords'])): ?>
            <div class="file-keywords">
                <?php foreach ($foundFile['keywords'] as $keyword): ?>
                    <span class="keyword-tag"><?php echo htmlspecialchars($keyword); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($isMedia): ?>
            <div class="media-player">
                <?php if ($isAudio): ?>
                    <audio controls><source src="<?php echo $foundFile['path']; ?>" type="audio/mpeg"></audio>
                <?php elseif ($isVideo): ?>
                    <video controls><source src="<?php echo $foundFile['path']; ?>" type="video/<?php echo $foundFile['extension']; ?>"></video>
                <?php elseif ($isImage): ?>
                    <img src="<?php echo $foundFile['path']; ?>" alt="<?php echo htmlspecialchars($foundFile['title'] ?? $foundFile['original_name']); ?>">
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($isText && file_exists($foundFile['path']) && filesize($foundFile['path']) < 1000000): ?>
            <div class="text-viewer"><?php echo htmlspecialchars(file_get_contents($foundFile['path'])); ?></div>
            <?php endif; ?>

            <div class="action-bar">
                <button class="action-btn" id="likeBtn" data-file-id="<?php echo $fileId; ?>">
                    <span>👍</span><span id="likeCount"><?php echo $fileLikes; ?></span>
                </button>
                <button class="action-btn" id="dislikeBtn" data-file-id="<?php echo $fileId; ?>">
                    <span>👎</span><span id="dislikeCount"><?php echo $fileDislikes; ?></span>
                </button>
                <a href="<?php echo $foundFile['path']; ?>" class="download-btn" download="<?php echo htmlspecialchars($foundFile['original_name']); ?>">⬇️ İndir</a>
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
                <textarea class="comment-input" id="commentText" placeholder="Yorum yaz (Anonim)..." maxlength="500" required></textarea>
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
                        $commentLikeCount = isset($commentLikes[$commentId]['likes']) ? $commentLikes[$commentId]['likes'] : 0;
                        $commentDislikeCount = isset($commentLikes[$commentId]['dislikes']) ? $commentLikes[$commentId]['dislikes'] : 0;
                        
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
                            <button class="comment-action-btn like-comment-btn" data-comment-id="<?php echo $commentId; ?>" data-action="like">
                                <span>👍</span><span class="like-count"><?php echo $commentLikeCount; ?></span>
                            </button>
                            <button class="comment-action-btn dislike-comment-btn" data-comment-id="<?php echo $commentId; ?>" data-action="dislike">
                                <span>👎</span><span class="dislike-count"><?php echo $commentDislikeCount; ?></span>
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
                                $replyLikeCount = isset($commentLikes[$replyId]['likes']) ? $commentLikes[$replyId]['likes'] : 0;
                                $replyDislikeCount = isset($commentLikes[$replyId]['dislikes']) ? $commentLikes[$replyId]['dislikes'] : 0;
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
                                    <button class="comment-action-btn like-comment-btn" data-comment-id="<?php echo $replyId; ?>" data-action="like">
                                        <span>👍</span><span class="like-count"><?php echo $replyLikeCount; ?></span>
                                    </button>
                                    <button class="comment-action-btn dislike-comment-btn" data-comment-id="<?php echo $replyId; ?>" data-action="dislike">
                                        <span>👎</span><span class="dislike-count"><?php echo $replyDislikeCount; ?></span>
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

        // Dosya beğenme/beğenmeme
        document.getElementById('likeBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('like.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'file', id: fileId, action: 'like' })
                });
                const result = await response.json();
                if (result.success) {
                    document.getElementById('likeCount').textContent = result.likes;
                    document.getElementById('dislikeCount').textContent = result.dislikes;
                    this.classList.add('active');
                    document.getElementById('dislikeBtn').classList.remove('dislike-active');
                }
            } catch (error) {
                console.error('Beğeni hatası:', error);
            }
        });

        document.getElementById('dislikeBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('like.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'file', id: fileId, action: 'dislike' })
                });
                const result = await response.json();
                if (result.success) {
                    document.getElementById('likeCount').textContent = result.likes;
                    document.getElementById('dislikeCount').textContent = result.dislikes;
                    this.classList.add('dislike-active');
                    document.getElementById('likeBtn').classList.remove('active');
                }
            } catch (error) {
                console.error('Beğenmeme hatası:', error);
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
                else alert('Hata: ' + result.message);
            } catch (error) {
                alert('Hata: ' + error.message);
            }
        });

        // Yorum beğenme/beğenmeme
        document.querySelectorAll('.like-comment-btn, .dislike-comment-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const commentId = this.dataset.commentId;
                const action = this.dataset.action;
                
                try {
                    const response = await fetch('like.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ type: 'comment', id: commentId, action: action })
                    });
                    const result = await response.json();
                    if (result.success) {
                        const parent = this.closest('.comment-actions');
                        parent.querySelector('.like-count').textContent = result.likes;
                        parent.querySelector('.dislike-count').textContent = result.dislikes;
                        
                        if (action === 'like') {
                            this.classList.add('liked');
                            parent.querySelector('.dislike-comment-btn').classList.remove('disliked');
                        } else {
                            this.classList.add('disliked');
                            parent.querySelector('.like-comment-btn').classList.remove('liked');
                        }
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
                    else alert('Hata: ' + result.message);
                } catch (error) {
                    alert('Hata: ' + error.message);
                }
            });
        });
    </script>
</body>
</html>