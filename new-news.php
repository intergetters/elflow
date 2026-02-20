<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Cargar canciones para el selector
$songs = [];
$conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
if (!$conn->connect_error) {
    $result = $conn->query("SELECT id, title, artist, is_premium, price FROM songs WHERE status = 'active' ORDER BY uploaded_at DESC");
    while ($row = $result->fetch_assoc()) {
        $songs[] = $row;
    }
    $conn->close();
}

$message = '';
if ($_POST) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $song_id = !empty($_POST['song_id']) ? intval($_POST['song_id']) : null;
    
    if ($title && $content) {
        $image_path = null;
        if (!empty($_FILES['news_image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['news_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png']) && $_FILES['news_image']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir('uploads')) mkdir('uploads', 0755, true);
                $filename = 'news_' . time() . '.' . $ext;
                $image_path = 'uploads/' . $filename;
                move_uploaded_file($_FILES['news_image']['tmp_name'], $image_path);
            }
        }

        // Insertar noticia
        $conn2 = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
        if (!$conn2->connect_error) {
            $stmt2 = $conn2->prepare("INSERT INTO news (title, content, image_path, song_id, download_type) VALUES (?, ?, ?, ?, 'free')");
            if ($stmt2) {
                $stmt2->bind_param('sssiss', $title, $content, $image_path, $song_id, 'free');
                $stmt2->execute();
                $stmt2->close();
            }
            $conn2->close();
        }
        header('Location: admin-news.php?created=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nueva Noticia</title>
    <style>
        body { background: #1a1a1a; color: white; padding: 20px; font-family: Arial; }
        .container { max-width: 600px; margin: 0 auto; background: #2a2a2a; padding: 20px; border-radius: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; background: #333; color: white; border: none; border-radius: 5px; }
        button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📝 Nueva Noticia</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Título *</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Contenido *</label>
                <textarea name="content" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Imagen (opcional)</label>
                <input type="file" name="news_image" accept=".jpg,.jpeg,.png">
            </div>
            <div class="form-group">
                <label>Adjuntar canción (opcional)</label>
                <select name="song_id">
                    <option value="">— Selecciona una canción —</option>
                    <?php foreach ($songs as $song): ?>
                    <option value="<?php echo $song['id']; ?>">
                        <?php echo htmlspecialchars($song['title'] . ' - ' . $song['artist']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">📤 Publicar Noticia</button>
        </form>
        <p><a href="admin.php" style="color:#667eea;">← Volver al panel</a></p>
    </div>
</body>
</html>