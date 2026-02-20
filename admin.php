<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - DJ Flow</title>
    <style>
        body { background: #1a1a1a; color: white; font-family: Arial; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .btn { 
            display: inline-block; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 12px 25px; 
            text-decoration: none; 
            border-radius: 25px; 
            margin: 10px; 
            font-weight: bold; 
        }
        .section { margin: 30px 0; }
        h2 { margin-bottom: 20px; color: #667eea; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎛️ Panel de Administración</h1>
        
        <div class="section">
            <h2>Noticias</h2>
            <a href="admin-news.php" class="btn">📰 Gestionar Noticias</a>
            <a href="new-news.php" class="btn">➕ Nueva Noticia</a>
        </div>
        
        <div class="section">
            <h2>Canciones</h2>
            <a href="upload.php" class="btn">🎵 Subir Canción</a>
            <a href="admin-songs.php" class="btn">🗂️ Gestionar Canciones</a>
        </div>
        
        <a href="index.php" style="display: inline-block; margin-top: 30px; color: #667eea;">← Volver al sitio</a>
    </div>
</body>
</html>