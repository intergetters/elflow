<?php
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
$news_item = null;
$song_info = null;

if (!$conn->connect_error) {
    $result = $conn->query("SELECT n.*, s.id as song_id, s.title as song_title, s.is_premium, s.price FROM news n LEFT JOIN songs s ON n.song_id = s.id WHERE n.id = $id AND n.status = 'active'");
    if ($result && $row = $result->fetch_assoc()) {
        $news_item = $row;
    }
    $conn->close();
}

if (!$news_item) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news_item['title']); ?> - DJ Flow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0c0c0c;
            color: #f0f0f0;
            line-height: 1.7;
            padding: 24px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 24px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .news-header {
            margin-bottom: 32px;
        }
        .news-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.2);
            font-size: 5rem;
        }
        .news-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 24px 0 16px;
            color: #fff;
        }
        .news-date {
            color: #aaa;
            font-size: 0.95rem;
            margin-bottom: 24px;
        }
        .news-content {
            color: #ddd;
            font-size: 1.05rem;
        }
        .download-section {
            margin-top: 32px;
            padding: 24px;
            background: rgba(25, 25, 25, 0.6);
            border-radius: 16px;
            border-left: 4px solid #667eea;
        }
        .download-btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 12px;
        }
        .download-btn.premium {
            background: linear-gradient(135deg, #ffcc00, #ff9500);
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Volver al inicio</a>
        
        <article class="news-header">
            <?php if (!empty($news_item['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($news_item['image_path']); ?>" alt="<?php echo htmlspecialchars($news_item['title']); ?>" class="news-image">
            <?php else: ?>
                <div class="news-image">📰</div>
            <?php endif; ?>
            
            <h1 class="news-title"><?php echo htmlspecialchars($news_item['title']); ?></h1>
            <div class="news-date"><?php echo date('d F Y', strtotime($news_item['created_at'])); ?></div>
        </article>
        
        <div class="news-content">
            <?php echo nl2br(htmlspecialchars($news_item['content'])); ?>
        </div>
        
        <?php if (!empty($news_item['song_id'])): ?>
            <div class="download-section">
                <h3>🎧 Canción adjunta</h3>
                <p><strong><?php echo htmlspecialchars($news_item['song_title']); ?></strong></p>
                <?php if ($news_item['download_type'] === 'premium' && $news_item['is_premium']): ?>
                    <a href="download.php?id=<?php echo $news_item['song_id']; ?>" class="download-btn premium">
                        💎 Comprar por $<?php echo number_format($news_item['price'], 2); ?> MXN
                    </a>
                <?php else: ?>
                    <a href="download.php?id=<?php echo $news_item['song_id']; ?>" class="download-btn">
                        ⬇️ Descargar Gratis
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>