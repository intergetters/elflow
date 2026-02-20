<?php
$id = intval($_GET['id'] ?? 0);
if (!$id) exit;

$conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
$file_path = null;

if (!$conn->connect_error) {
    $result = $conn->query("SELECT file_path FROM songs WHERE id = $id AND status = 'active'");
    if ($result && $row = $result->fetch_assoc()) {
        $file_path = $row['file_path'];
    }
    $conn->close();
}

if ($file_path && file_exists($file_path)) {
    $mime = mime_content_type($file_path);
    header('Content-Type: ' . $mime);
    
    // Solo primeros 60 segundos (aproximado)
    $size = filesize($file_path);
    $partial_size = min($size, 1024 * 1024); // ~1MB ≈ 60s MP3
    
    readfile($file_path);
} else {
    http_response_code(404);
}
?>