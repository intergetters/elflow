<?php
session_start();

// Protección anti-duplicado de comentarios
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    if (!isset($_SESSION['last_comment']) || $_SESSION['last_comment'] !== md5($_POST['name'] . $_POST['message'])) {
        $name = trim($_POST['name']);
        $message = trim($_POST['message']);
        if (!empty($name) && !empty($message)) {
            $conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
            if (!$conn->connect_error) {
                $stmt = $conn->prepare("INSERT INTO comments (name, message) VALUES (?, ?)");
                $stmt->bind_param('ss', $name, $message);
                $stmt->execute();
                $stmt->close();
                $conn->close();
                $_SESSION['last_comment'] = md5($name . $message);
            }
        }
    }
    header('Location: index.php?comment=1');
    exit;
}

// Cargar datos
$conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
$logo_path = 'images/default-logo.png';
$news = [];
$comments = [];

if (!$conn->connect_error) {
    $config = $conn->query("SELECT logo_path FROM site_config WHERE id = 1");
    if ($config && $row = $config->fetch_assoc()) {
        $logo_path = $row['logo_path'] ?: 'images/default-logo.png';
    }

    $news_result = $conn->query("SELECT id, title, image_path, created_at FROM news WHERE status = 'active' ORDER BY created_at DESC");
    while ($row = $news_result->fetch_assoc()) {
        $news[] = $row;
    }

    $comments_result = $conn->query("SELECT name, message, created_at FROM comments WHERE status = 'active' ORDER BY created_at DESC LIMIT 10");
    while ($row = $comments_result->fetch_assoc()) {
        $comments[] = $row;
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DJ Flow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0c0c0c;
            color: #f0f0f0;
            line-height: 1.6;
            padding: 24px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
        }
        @media (max-width: 900px) {
            .container { grid-template-columns: 1fr; }
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            grid-column: 1 / -1;
        }
        .logo {
            width: 180px;
            height: auto;
            margin: 0 auto 16px;
            filter: drop-shadow(0 4px 12px rgba(102, 126, 234, 0.3));
        }
        .page-title {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Mosaico de noticias */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }
        @media (max-width: 900px) { .news-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .news-grid { grid-template-columns: 1fr; } }
        .news-item { cursor: pointer; transition: transform 0.2s; }
        .news-item:hover { transform: translateY(-4px); }
        .news-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.2);
            font-size: 3rem;
        }
        .news-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 12px;
            color: #fff;
            text-align: center;
        }

        /* Comentarios */
        .comments-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 24px;
            text-align: center;
            color: #fff;
        }
        .comment {
            background: rgba(25, 25, 25, 0.6);
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 16px;
            border-left: 3px solid #667eea;
        }
        .comment-name { font-weight: 600; color: #667eea; margin-bottom: 6px; font-size: 1rem; }
        .comment-message { color: #ddd; line-height: 1.5; }
        .comment-date { font-size: 0.8rem; color: #777; margin-top: 8px; text-align: right; }
        .comment-form {
            background: rgba(25, 25, 25, 0.6);
            padding: 24px;
            border-radius: 16px;
            margin-top: 24px;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            background: #1a1a1a;
            color: white;
            border: 1px solid #333;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 8px;
        }
        .success-msg {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid #28a745;
            color: #4ade80;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="DJ Flow" class="logo" onerror="this.style.display='none'">
            <h1 class="page-title">Últimas Noticias</h1>
        </div>

        <div class="news-grid">
            <?php if (!empty($news)): ?>
                <?php foreach ($news as $item): ?>
                <div class="news-item" onclick="window.location='news-detail.php?id=<?php echo $item['id']; ?>'">
                    <?php if (!empty($item['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="news-image">
                    <?php else: ?>
                        <div class="news-image">📰</div>
                    <?php endif; ?>
                    <h3 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; color: #777; text-align: center;">No hay noticias publicadas aún.</p>
            <?php endif; ?>
        </div>

        <div class="comments-section">
            <h2>💬 Comentarios</h2>
            
            <?php if (isset($_GET['comment'])): ?>
                <div class="success-msg">✅ ¡Gracias por tu comentario!</div>
            <?php endif; ?>

            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-name"><?php echo htmlspecialchars($comment['name']); ?></div>
                    <div class="comment-message"><?php echo nl2br(htmlspecialchars($comment['message'])); ?></div>
                    <div class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #777; text-align: center;">Sé el primero en comentar.</p>
            <?php endif; ?>

            <div class="comment-form">
                <form method="POST" onsubmit="disableSubmit(this)">
                    <input type="hidden" name="action" value="add_comment">
                    <input type="text" name="name" class="form-control" placeholder="Tu nombre *" required>
                    <textarea name="message" class="form-control" rows="4" placeholder="Tu comentario *" required></textarea>
                    <button type="submit" class="submit-btn">Enviar Comentario</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function disableSubmit(form) {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.textContent = 'Enviando...';
            }
        }
    </script>
</body>
</html>