<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Eliminar noticia
if (isset($_POST['action']) && $_POST['action'] === 'delete_news') {
    $id = intval($_POST['news_id']);
    if ($id > 0) {
        $conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
        if (!$conn->connect_error) {
            $conn->query("DELETE FROM news WHERE id = $id");
            $conn->close();
        }
    }
    header('Location: admin-news.php?deleted=1');
    exit;
}

// Cargar noticias
$news = [];
$conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
if (!$conn->connect_error) {
    $result = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
    while ($row = $result->fetch_assoc()) {
        $news[] = $row;
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestionar Noticias</title>
    <style>
        body { background: #1a1a1a; color: white; font-family: Arial; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .btn { background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: bold; }
        .news-list { background: #2a2a2a; border-radius: 12px; overflow: hidden; }
        .news-item { padding: 20px; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center; }
        .news-item:last-child { border-bottom: none; }
        .news-title { font-weight: bold; font-size: 1.1rem; }
        .news-meta { color: #aaa; font-size: 0.9rem; margin-top: 4px; }
        .actions { display: flex; gap: 10px; }
        .action-btn { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .edit-btn { background: #28a745; color: white; }
        .delete-btn { background: #dc3545; color: white; }
        .success-msg { background: #2a4a2a; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📰 Gestionar Noticias</h1>
            <a href="new-news.php" class="btn">➕ Nueva Noticia</a>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="success-msg">🗑️ Noticia eliminada correctamente</div>
        <?php endif; ?>
        
        <?php if (!empty($news)): ?>
            <div class="news-list">
                <?php foreach ($news as $item): ?>
                <div class="news-item">
                    <div>
                        <div class="news-title"><?php echo htmlspecialchars($item['title']); ?></div>
                        <div class="news-meta"><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></div>
                    </div>
                    <div class="actions">
                        <a href="edit-news.php?id=<?php echo $item['id']; ?>" class="action-btn edit-btn">✏️ Editar</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar esta noticia?')">
                            <input type="hidden" name="action" value="delete_news">
                            <input type="hidden" name="news_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="action-btn delete-btn">🗑️ Eliminar</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No hay noticias creadas aún.</p>
        <?php endif; ?>
        
        <a href="admin.php" style="display: inline-block; margin-top: 20px; color: #667eea; text-decoration: none;">← Volver al Panel</a>
    </div>
</body>
</html>