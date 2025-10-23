<?php
// PHP ayarlarını runtime'da değiştir
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('memory_limit', '256M');

header('Content-Type: application/json');

// Dosya yükleme dizini
$uploadDir = __DIR__ . '/uploads/';
$dbFile = __DIR__ . '/files.json';

// Dizin yoksa oluştur
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'Upload dizini oluşturulamadı: ' . $uploadDir
        ]);
        exit;
    }
}

// JSON dosyası yoksa oluştur
if (!file_exists($dbFile)) {
    file_put_contents($dbFile, json_encode([]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Başlık ve anahtar kelimeler
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $keywords = isset($_POST['keywords']) ? trim($_POST['keywords']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    // Validasyon
    if (empty($title)) {
        echo json_encode([
            'success' => false,
            'message' => 'Başlık gerekli'
        ]);
        exit;
    }
    
    if (strlen($title) > 200) {
        echo json_encode([
            'success' => false,
            'message' => 'Başlık 200 karakterden uzun olamaz'
        ]);
        exit;
    }
    
    // Dosya yükleme hatası kontrolü
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false,
            'message' => 'Dosya yükleme hatası kodu: ' . $file['error']
        ]);
        exit;
    }
    
    // Benzersiz rastgele Base64 ID oluştur
    $randomBytes = random_bytes(8);
    $fileId = rtrim(strtr(base64_encode($randomBytes), '+/', '-_'), '=');
    
    // Dosya bilgileri
    $originalName = basename($file['name']);
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $originalSize = $file['size'];
    $newFileName = $fileId . '.' . $fileExtension;
    $filePath = $uploadDir . $newFileName;
    
    // Dosyayı taşı
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Sıkıştırma dene (sadece resimler için)
        $compressedSize = $originalSize;
        if (in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
            $compressed = compressImage($filePath, $fileExtension);
            if ($compressed) {
                $compressedSize = filesize($filePath);
            }
        }
        
        // Veritabanına kaydet
        $filesContent = file_get_contents($dbFile);
        $files = json_decode($filesContent, true);
        
        if (!is_array($files)) {
            $files = [];
        }
        
        // Anahtar kelimeleri işle
        $keywordsArray = array_filter(array_map('trim', explode(',', $keywords)));
        
        $fileData = [
            'id' => $fileId,
            'title' => $title,
            'description' => $description,
            'keywords' => $keywordsArray,
            'original_name' => $originalName,
            'file_name' => $newFileName,
            'extension' => $fileExtension,
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'compression_ratio' => $originalSize > 0 ? round((1 - $compressedSize / $originalSize) * 100, 2) : 0,
            'upload_date' => date('Y-m-d H:i:s'),
            'path' => 'uploads/' . $newFileName,
            'views' => 0
        ];
        
        array_unshift($files, $fileData);
        file_put_contents($dbFile, json_encode($files, JSON_PRETTY_PRINT));
        
        echo json_encode([
            'success' => true,
            'message' => 'Dosya başarıyla yüklendi',
            'fileId' => $fileId,
            'title' => $title,
            'compressionRatio' => $fileData['compression_ratio']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Dosya taşıma hatası'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek'
    ]);
}

// Resim sıkıştırma fonksiyonu
function compressImage($filePath, $extension) {
    try {
        $quality = 85; // Kalite seviyesi (0-100)
        
        if ($extension === 'jpg' || $extension === 'jpeg') {
            $image = imagecreatefromjpeg($filePath);
            if ($image) {
                imagejpeg($image, $filePath, $quality);
                imagedestroy($image);
                return true;
            }
        } elseif ($extension === 'png') {
            $image = imagecreatefrompng($filePath);
            if ($image) {
                // PNG için compression level 0-9
                imagepng($image, $filePath, 6);
                imagedestroy($image);
                return true;
            }
        }
    } catch (Exception $e) {
        // Sıkıştırma başarısız olursa orijinal dosyayı koru
        return false;
    }
    return false;
}
?>