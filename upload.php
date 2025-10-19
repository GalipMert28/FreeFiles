<?php
// PHP ayarlarını runtime'da değiştir
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('memory_limit', '256M');

// Hata raporlamayı aç (debug için)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("Upload.php çalıştırıldı");

header('Content-Type: application/json');

// Dosya yükleme dizini
$uploadDir = __DIR__ . '/uploads/';
$dbFile = __DIR__ . '/files.json';

error_log("Upload dizini: " . $uploadDir);
error_log("DB dosyası: " . $dbFile);

// Dizin yoksa oluştur
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        error_log("Dizin oluşturulamadı: " . $uploadDir);
        echo json_encode([
            'success' => false,
            'message' => 'Upload dizini oluşturulamadı: ' . $uploadDir
        ]);
        exit;
    }
    error_log("Dizin oluşturuldu: " . $uploadDir);
}

// JSON dosyası yoksa oluştur
if (!file_exists($dbFile)) {
    file_put_contents($dbFile, json_encode([]));
    error_log("JSON dosyası oluşturuldu");
}

error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("FILES var mı: " . (isset($_FILES['file']) ? 'Evet' : 'Hayır'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST isteği alındı");
    error_log("FILES içeriği: " . print_r($_FILES, true));
    
    if (!isset($_FILES['file'])) {
        error_log("File parametresi yok!");
        echo json_encode([
            'success' => false,
            'message' => 'Dosya gönderilmedi',
            'debug' => $_FILES
        ]);
        exit;
    }
    
    $file = $_FILES['file'];
    error_log("Dosya bilgileri: " . print_r($file, true));
    
    // Dosya yükleme hatası kontrolü
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("Dosya yükleme hatası: " . $file['error']);
        echo json_encode([
            'success' => false,
            'message' => 'Dosya yükleme hatası kodu: ' . $file['error']
        ]);
        exit;
    }
    
    // Benzersiz rastgele Base64 ID oluştur
    $randomBytes = random_bytes(8);
    $fileId = rtrim(strtr(base64_encode($randomBytes), '+/', '-_'), '=');
    error_log("Oluşturulan ID: " . $fileId);
    
    // Dosya bilgileri
    $originalName = basename($file['name']);
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $fileSize = $file['size'];
    $newFileName = $fileId . '.' . $fileExtension;
    $filePath = $uploadDir . $newFileName;
    
    error_log("Hedef dosya yolu: " . $filePath);
    error_log("Kaynak dosya: " . $file['tmp_name']);
    
    // Dosyayı taşı
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        error_log("Dosya başarıyla taşındı!");
        
        // Veritabanına kaydet
        $filesContent = file_get_contents($dbFile);
        $files = json_decode($filesContent, true);
        
        if (!is_array($files)) {
            $files = [];
        }
        
        $fileData = [
            'id' => $fileId,
            'original_name' => $originalName,
            'file_name' => $newFileName,
            'extension' => $fileExtension,
            'size' => $fileSize,
            'upload_date' => date('Y-m-d H:i:s'),
            'path' => 'uploads/' . $newFileName
        ];
        
        array_unshift($files, $fileData);
        file_put_contents($dbFile, json_encode($files, JSON_PRETTY_PRINT));
        error_log("JSON dosyası güncellendi");
        
        echo json_encode([
            'success' => true,
            'message' => 'Dosya başarıyla yüklendi',
            'fileId' => $fileId,
            'fileName' => $originalName
        ]);
    } else {
        error_log("Dosya taşıma BAŞARISIZ!");
        echo json_encode([
            'success' => false,
            'message' => 'Dosya taşıma hatası. Kaynak: ' . $file['tmp_name'] . ' Hedef: ' . $filePath
        ]);
    }
} else {
    error_log("POST dışı istek: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek metodu: ' . $_SERVER['REQUEST_METHOD']
    ]);
}
?>