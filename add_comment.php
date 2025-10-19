<?php
header('Content-Type: application/json');

// JSON dosyası
$commentsFile = 'comments.json';

// Dosya yoksa oluştur
if (!file_exists($commentsFile)) {
    file_put_contents($commentsFile, json_encode([]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // JSON verisini al
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['file_id']) || !isset($data['text'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz veri'
        ]);
        exit;
    }
    
    $fileId = trim($data['file_id']);
    $text = trim($data['text']);
    
    // Validasyon
    if (empty($text)) {
        echo json_encode([
            'success' => false,
            'message' => 'Yorum boş olamaz'
        ]);
        exit;
    }
    
    if (strlen($text) > 500) {
        echo json_encode([
            'success' => false,
            'message' => 'Yorum 500 karakterden uzun olamaz'
        ]);
        exit;
    }
    
    // Yorumları yükle
    $comments = json_decode(file_get_contents($commentsFile), true);
    if (!is_array($comments)) {
        $comments = [];
    }
    
    // Yeni yorum
    $newComment = [
        'id' => uniqid(),
        'file_id' => $fileId,
        'text' => $text,
        'date' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] // Opsiyonel: Spam kontrolü için
    ];
    
    // Yorumu ekle
    array_unshift($comments, $newComment);
    
    // Kaydet
    if (file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT))) {
        echo json_encode([
            'success' => true,
            'message' => 'Yorum eklendi',
            'comment' => $newComment
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Yorum kaydedilemedi'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek metodu'
    ]);
}
?>