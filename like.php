<?php
header('Content-Type: application/json');

$likesFile = 'likes.json';

// Dosya yoksa oluştur
if (!file_exists($likesFile)) {
    file_put_contents($likesFile, json_encode([
        'files' => [],
        'comments' => []
    ]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['type']) || !isset($data['id']) || !isset($data['action'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz veri'
        ]);
        exit;
    }
    
    $type = $data['type']; // 'file' veya 'comment'
    $id = $data['id'];
    $action = $data['action']; // 'like' veya 'dislike'
    
    // Beğenileri yükle
    $likes = json_decode(file_get_contents($likesFile), true);
    
    if ($type === 'file') {
        if (!isset($likes['files'][$id])) {
            $likes['files'][$id] = ['likes' => 0, 'dislikes' => 0];
        }
        
        if ($action === 'like') {
            $likes['files'][$id]['likes']++;
        } elseif ($action === 'dislike') {
            $likes['files'][$id]['dislikes']++;
        }
        
        $likeCount = $likes['files'][$id]['likes'];
        $dislikeCount = $likes['files'][$id]['dislikes'];
        
    } elseif ($type === 'comment') {
        if (!isset($likes['comments'][$id])) {
            $likes['comments'][$id] = ['likes' => 0, 'dislikes' => 0];
        }
        
        if ($action === 'like') {
            $likes['comments'][$id]['likes']++;
        } elseif ($action === 'dislike') {
            $likes['comments'][$id]['dislikes']++;
        }
        
        $likeCount = $likes['comments'][$id]['likes'];
        $dislikeCount = $likes['comments'][$id]['dislikes'];
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz tip'
        ]);
        exit;
    }
    
    // Kaydet
    if (file_put_contents($likesFile, json_encode($likes, JSON_PRETTY_PRINT))) {
        echo json_encode([
            'success' => true,
            'likes' => $likeCount,
            'dislikes' => $dislikeCount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Kaydedilemedi'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek'
    ]);
}
?>