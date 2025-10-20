<?php
header('Content-Type: application/json');

$likesFile = 'likes.json';

// Dosya yoksa oluştur
if (!file_exists($likesFile)) {
    file_put_contents($likesFile, json_encode(['files' => [], 'comments' => []]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['type']) || !isset($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz veri'
        ]);
        exit;
    }
    
    $type = $data['type']; // 'file' veya 'comment'
    $id = $data['id'];
    
    // Beğenileri yükle
    $likes = json_decode(file_get_contents($likesFile), true);
    
    if ($type === 'file') {
        if (!isset($likes['files'][$id])) {
            $likes['files'][$id] = 0;
        }
        $likes['files'][$id]++;
        $count = $likes['files'][$id];
    } elseif ($type === 'comment') {
        if (!isset($likes['comments'][$id])) {
            $likes['comments'][$id] = 0;
        }
        $likes['comments'][$id]++;
        $count = $likes['comments'][$id];
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
            'count' => $count
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