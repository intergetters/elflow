<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: admin-songs.php');
    exit;
}

// Cargar canción existente
$conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
$song = null;

if (!$conn->connect_error) {
    $result = $conn->query("SELECT * FROM songs WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $song = $row;
    }
    $conn->close();
}

if (!$song) {
    header('Location: admin-songs.php');
    exit;
}

// Procesar actualización
if ($_POST) {
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? 'DJ Flow');
    $is_premium = !empty($_POST['is_premium']);
    $price = $is_premium ? floatval($_POST['price'] ?? 0) : 0;
    
    if ($title) {
        $conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
        if (!$conn->connect_error) {
            $stmt = $conn->prepare("UPDATE songs SET title = ?, artist = ?, is_premium = ?, price = ? WHERE id = ?");
            if ($stmt) {
                $is_premium_int = $is_premium ? 1 : 0;
                $stmt->bind_param('ssidi', $title, $artist, $is_premium_int, $price, $id);
                $stmt->execute();
                $stmt->close();
            }
            $conn->close();
        }
        header('Location: admin-songs.php?edited=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Canción - DJ Flow</title>
    <style>
        body { font-family: Arial; background: #1a1a1a; color: white; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #2a2a2a; padding: 25px; border-radius: 12px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; background: #333; color: white; border: none; border-radius: 6px; }
        .btn { 
            background: #667eea; 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            margin-right: 10px; 
        }
        .back { 
            display: inline-block; 
            margin-top: 20px; 
            color: #667eea; 
            text-decoration: none; 
        }
        .file-info { 
            background: rgba(102, 126, 234, 0.1); 
            padding: 10px; 
            border-radius: 6px; 
            margin-bottom: 15px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>✏️ Editar Canción</h2>
        
        <div class="file-info">
            <strong>Archivo:</strong> <?php echo htmlspecialchars($song['file_name']); ?><br>
            <strong>Tamaño:</strong> <?php echo round($song['file_size'] / 1024 / 1024, 2); ?> MB
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Título *</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($song['title']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Artista</label>
                <input type="text" name="artist" value="<?php echo htmlspecialchars($song['artist']); ?>">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_premium" <?php echo $song['is_premium'] ? 'checked' : ''; ?>>
                    ¿Es premium?
                </label>
            </div>
            
            <div class="form-group">
                <label>Precio (MXN)</label>
                <input type="number" name="price" step="0.01" min="0" value="<?php echo number_format($song['price'], 2, '.', ''); ?>">
            </div>
            
            <button type="submit" class="btn">💾 Guardar Cambios</button>
            <a href="admin-songs.php" class="btn" style="background: #6c757d;">Cancelar</a>
        </form>
    </div>
</body>
</html>