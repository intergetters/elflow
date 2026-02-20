<?php
session_start();
$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Credenciales fijas (mejor usar BD en producción)
    if ($username === 'admin' && $password === 'tu_contraseña_segura') {
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - DJ Flow</title>
    <style>
        body { background: #1a1a1a; color: white; font-family: Arial; padding: 50px; }
        .container { max-width: 400px; margin: 0 auto; background: #2a2a2a; padding: 30px; border-radius: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border-radius: 5px; border: none; background: #333; color: white; }
        .btn { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .error { background: #442222; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔐 Iniciar Sesión</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>
</body>
</html>