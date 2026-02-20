<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
if ($_POST && !empty($_FILES['audio_file']['name'])) {
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? 'DJ Flow');
    $is_premium = !empty($_POST['is_premium']);
    $price = $is_premium ? floatval($_POST['price'] ?? 0) : 0;

    if ($title) {
        $ext = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['mp3', 'wav']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);
            
            $filename = 'song_' . time() . '.' . $ext;
            $file_path = 'uploads/' . $filename;
            move_uploaded_file($_FILES['audio_file']['tmp_name'], $file_path);
            
            $file_size = filesize($file_path);
            $file_name = $_FILES['audio_file']['name'];
            $is_premium_int = $is_premium ? 1 : 0;

            // Insertar en songs
            $conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
            if (!$conn->connect_error) {
                $stmt = $conn->prepare("INSERT INTO songs (title, artist, file_path, file_name, file_size, cover_image, is_premium, price, downloads, uploaded_by, status, currency) VALUES (?, ?, ?, ?, ?, NULL, ?, ?, 0, 1, 'active', 'MXN')");
                if ($stmt) {
                    $stmt->bind_param('ssssidid', $title, $artist, $file_path, $file_name, $file_size, $is_premium_int, $price);
                    $stmt->execute();
                    $stmt->close();
                }
                $conn->close();
            }
            $message = '✅ Canción subida correctamente';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Subir Canción</title>
    <style>
        body { background: #1a1a1a; color: white; padding: 20px; font-family: Arial; }
        .container { max-width: 600px; margin: 0 auto; background: #2a2a2a; padding: 20px; border-radius: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; background: #333; color: white; border: none; border-radius: 5px; }
        button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🎵 Subir Canción</h2>
        <?php if ($message): ?>
            <div style="background: #2a4a2a; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Título *</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Artista</label>
                <input type="text" name="artist" value="DJ Flow">
            </div>
            <div class="form-group">
                <label>Archivo de audio (.mp3, .wav) *</label>
                <input type="file" name="audio_file" accept=".mp3,.wav" required>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="is_premium"> ¿Es premium?</label>
            </div>
            <div class="form-group">
                <label>Precio (MXN)</label>
                <input type="number" name="price" step="0.01" min="0" value="50.00">
            </div>
            <button type="submit">📤 Subir Canción</button>
        </form>
        <p><a href="admin.php" style="color:#667eea;">← Volver al panel</a></p>
    </div>
</body>
</html>