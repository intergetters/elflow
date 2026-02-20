<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Eliminar canción
if (isset($_POST['action']) && $_POST['action'] === 'delete_song') {
    $id = intval($_POST['song_id']);
    if ($id > 0) {
        $conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
        if (!$conn->connect_error) {
            // Obtener ruta del archivo para eliminarlo
            $result = $conn->query("SELECT file_path FROM songs WHERE id = $id");
            if ($result && $row = $result->fetch_assoc()) {
                if (!empty($row['file_path']) && file_exists($row['file_path'])) {
                    unlink($row['file_path']);
                }
            }
            $conn->query("DELETE FROM songs WHERE id = $id");
            $conn->close();
        }
    }
    header('Location: admin-songs.php?deleted=1');
    exit;
}

// Cargar canciones
$songs = [];
$conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
if (!$conn->connect_error) {
    $result = $conn->query("SELECT * FROM songs ORDER BY uploaded_at DESC");
    while ($row = $result->fetch_assoc()) {
        $songs[] = $row;
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Canciones - DJ Flow</title>
    <style>
        body { font-family: Arial; background: #1a1a1a; color: white; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .btn { 
            background: #667eea; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 8px; 
            display: inline-block; 
            font-weight: bold; 
        }
        .songs-list { background: #2a2a2a; border-radius: 12px; overflow: hidden; }
        .song-item { 
            padding: 20px; 
            border-bottom: 1px solid #333; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .song-item:last-child { border-bottom: none; }
        .song-info { flex: 1; }
        .song-title { font-weight: bold; font-size: 1.1rem; }
        .song-meta { color: #aaa; font-size: 0.9rem; margin-top: 4px; }
        .actions { display: flex; gap: 10px; }
        .action-btn { 
            padding: 6px 12px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: bold; 
        }
        .edit-btn { background: #28a745; color: white; }
        .delete-btn { background: #dc3545; color: white; }
        .success-msg { 
            background: #2a4a2a; 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            text-align: center; 
        }
        .premium-tag { 
            background: #ffcc00; 
            color: #000; 
            padding: 2px 8px; 
            border-radius: 10px; 
            font-size: 0.8rem; 
            font-weight: bold; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎵 Gestionar Canciones</h1>
            <a href="upload.php" class="btn">➕ Subir Nueva Canción</a>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="success-msg">🗑️ Canción eliminada correctamente</div>
        <?php endif; ?>
        
        <?php if (!empty($songs)): ?>
            <div class="songs-list">
                <?php foreach ($songs as $song): ?>
                <div class="song-item">
                    <div class="song-info">
                        <div class="song-title">
                            <?php echo htmlspecialchars($song['title']); ?>
                            <?php if ($song['is_premium']): ?>
                                <span class="premium-tag">💎 Premium</span>
                            <?php endif; ?>
                        </div>
                        <div class="song-meta">
                            Artista: <?php echo htmlspecialchars($song['artist']); ?> | 
                            Tamaño: <?php echo round($song['file_size'] / 1024 / 1024, 2); ?> MB |
                            Descargas: <?php echo $song['downloads']; ?> |
                            <?php echo date('d/m/Y', strtotime($song['uploaded_at'])); ?>
                        </div>
                    </div>
                    <div class="actions">
    <a href="edit-song.php?id=<?php echo $song['id']; ?>" class="action-btn edit-btn">✏️ Editar</a>
    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar esta canción?')">
        <input type="hidden" name="action" value="delete_song">
        <input type="hidden" name="song_id" value="<?php echo $song['id']; ?>">
        <button type="submit" class="action-btn delete-btn">🗑️ Eliminar</button>
    </form>
</div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No hay canciones subidas aún.</p>
        <?php endif; ?>
        
        <a href="admin.php" style="display: inline-block; margin-top: 20px; color: #667eea; text-decoration: none;">← Volver al Panel</a>
    </div>
</body>
</html>