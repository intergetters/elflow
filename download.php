<?php
session_start();
$id = intval($_GET['id'] ?? 0);
if (!$id) exit;

$conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
$song = null;

if (!$conn->connect_error) {
    $result = $conn->query("SELECT * FROM songs WHERE id = $id AND status = 'active'");
    if ($result && $row = $result->fetch_assoc()) {
        $song = $row;
    }
    $conn->close();
}

if (!$song) {
    http_response_code(404);
    exit;
}

// Aquí iría la lógica de pago para premium
// Por ahora, permitimos descarga directa

$file = $song['file_path'];
if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($song['file_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}
?>