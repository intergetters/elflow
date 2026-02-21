<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = '';

if ($_POST && !empty($_FILES['audio_file']['name'])) {
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? 'DJ Flow');
    $is_premium = !empty($_POST['is_premium']);
    $price = $is_premium ? floatval($_POST['price'] ?? 0) : 0;

    if ($title) {
        // Validar archivo de audio
        $audio_ext = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($audio_ext, ['mp3', 'wav', 'ogg', 'm4a'])) {
            $message = '❌ Formato de audio no permitido. Usa MP3, WAV, OGG o M4A';
            $message_type = 'error';
        } elseif ($_FILES['audio_file']['error'] !== UPLOAD_ERR_OK) {
            $message = '❌ Error al subir el archivo de audio';
            $message_type = 'error';
        } else {
            // Crear directorios si no existen
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);
            if (!is_dir('uploads/covers')) mkdir('uploads/covers', 0755, true);
            if (!is_dir('uploads/songs')) mkdir('uploads/songs', 0755, true);

            // Procesar archivo de audio
            $audio_filename = 'song_' . time() . '_' . uniqid() . '.' . $audio_ext;
            $audio_file_path = 'uploads/songs/' . $audio_filename;
            
            if (!move_uploaded_file($_FILES['audio_file']['tmp_name'], $audio_file_path)) {
                $message = '❌ Error al guardar el archivo de audio';
                $message_type = 'error';
            } else {
                // Procesar imagen de portada (opcional)
                $cover_image_path = null;
                
                if (!empty($_FILES['cover_image']['name'])) {
                    $cover_ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($cover_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $message = '⚠️ Formato de imagen no válido (JPG, PNG, WEBP o GIF). Se usará imagen por defecto';
                        $message_type = 'warning';
                    } elseif ($_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                        // Validar tamaño de imagen (máximo 5MB)
                        if ($_FILES['cover_image']['size'] > 5 * 1024 * 1024) {
                            $message = '⚠️ La imagen es muy grande. Se usará imagen por defecto';
                            $message_type = 'warning';
                        } else {
                            // Validar que sea realmente una imagen
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mime = finfo_file($finfo, $_FILES['cover_image']['tmp_name']);
                            finfo_close($finfo);
                            
                            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                            
                            if (!in_array($mime, $allowed_mimes)) {
                                $message = '⚠️ El archivo no es una imagen válida. Se usará imagen por defecto';
                                $message_type = 'warning';
                            } else {
                                $cover_filename = 'cover_' . time() . '_' . uniqid() . '.' . $cover_ext;
                                $cover_image_path = 'uploads/covers/' . $cover_filename;
                                
                                if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_image_path)) {
                                    $message = '⚠️ Error al guardar la imagen. Se usará imagen por defecto';
                                    $message_type = 'warning';
                                    $cover_image_path = null;
                                }
                            }
                        }
                    }
                }

                // Insertar en base de datos
                $conn = new mysqli('sql103.infinityfree.com', 'if0_41136607', 'deromaxim2', 'if0_41136607_djflow');
                
                if ($conn->connect_error) {
                    $message = '❌ Error de conexión a la base de datos';
                    $message_type = 'error';
                    // Eliminar archivos subidos en caso de error
                    if (file_exists($audio_file_path)) unlink($audio_file_path);
                    if ($cover_image_path && file_exists($cover_image_path)) unlink($cover_image_path);
                } else {
                    $file_size = filesize($audio_file_path);
                    $file_name = $_FILES['audio_file']['name'];
                    $is_premium_int = $is_premium ? 1 : 0;

                    // CORREGIDO: 8 parámetros → 8 tipos en bind_param
                    $stmt = $conn->prepare("INSERT INTO songs (title, artist, file_path, file_name, file_size, cover_image, is_premium, price, downloads, uploaded_by, status, currency, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 1, 'active', 'MXN', NOW())");
                    
                    if ($stmt) {
                        // Tipos: s=string, i=integer, d=double
                        $stmt->bind_param('ssssisidi', 
                            $title, 
                            $artist, 
                            $audio_file_path, 
                            $file_name, 
                            $file_size, 
                            $cover_image_path, 
                            $is_premium_int, 
                            $price
                        );
                        
                        if ($stmt->execute()) {
                            $song_id = $stmt->insert_id;
                            $message = "✅ Canción subida correctamente (ID: $song_id)";
                            $message_type = 'success';
                        } else {
                            $message = '❌ Error al guardar en la base de datos';
                            $message_type = 'error';
                            // Eliminar archivos en caso de error
                            if (file_exists($audio_file_path)) unlink($audio_file_path);
                            if ($cover_image_path && file_exists($cover_image_path)) unlink($cover_image_path);
                        }
                        $stmt->close();
                    } else {
                        $message = '❌ Error al preparar la consulta';
                        $message_type = 'error';
                        // Eliminar archivos en caso de error
                        if (file_exists($audio_file_path)) unlink($audio_file_path);
                        if ($cover_image_path && file_exists($cover_image_path)) unlink($cover_image_path);
                    }
                    $conn->close();
                }
            }
        }
    } else {
        $message = '❌ El título es obligatorio';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Canción - DJ Flow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            color: white;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background: rgba(42, 42, 42, 0.8);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid rgba(102, 126, 234, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(102, 126, 234, 0.3);
        }

        .header i {
            font-size: 32px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header h2 {
            font-size: 24px;
            font-weight: bold;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .message.success {
            background: rgba(81, 207, 102, 0.2);
            border-left: 4px solid #51cf66;
            color: #51cf66;
        }

        .message.error {
            background: rgba(255, 107, 107, 0.2);
            border-left: 4px solid #ff6b6b;
            color: #ff6b6b;
        }

        .message.warning {
            background: rgba(255, 193, 7, 0.2);
            border-left: 4px solid #ffc107;
            color: #ffc107;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ccc;
        }

        .required {
            color: #ff6b6b;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 12px;
            background: rgba(51, 51, 51, 0.8);
            color: white;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="file"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(51, 51, 51, 1);
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.3);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }

        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: normal;
            text-transform: none;
            letter-spacing: normal;
            cursor: pointer;
        }

        .price-group {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            margin-top: 10px;
        }

        .price-group input {
            flex: 1;
        }

        .file-upload-area {
            border: 2px dashed rgba(102, 126, 234, 0.4);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(102, 126, 234, 0.05);
        }

        .file-upload-area:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .file-upload-area.dragover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.15);
            transform: scale(1.02);
        }

        .file-upload-area i {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 10px;
            display: block;
        }

        .file-upload-area p {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .file-name {
            margin-top: 8px;
            padding: 8px 12px;
            background: rgba(102, 126, 234, 0.2);
            border-radius: 6px;
            font-size: 13px;
            color: #667eea;
            word-break: break-all;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        button[type="submit"],
        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button[type="submit"] {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .info-box {
            background: rgba(102, 126, 234, 0.1);
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
            color: #bbb;
        }

        .info-box i {
            color: #667eea;
            margin-right: 8px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .button-group {
                flex-direction: column;
            }

            button[type="submit"],
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-music"></i>
            <h2>Subir Canción</h2>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <!-- Título -->
            <div class="form-group">
                <label>Título de la Canción <span class="required">*</span></label>
                <input type="text" name="title" placeholder="Ej: Noche de Estrellas" required>
            </div>

            <!-- Artista -->
            <div class="form-group">
                <label>Artista <span class="required">*</span></label>
                <input type="text" name="artist" placeholder="Ej: DJ Flow" value="DJ Flow">
            </div>

            <!-- Archivo de Audio -->
            <div class="form-group">
                <label>Archivo de Audio <span class="required">*</span></label>
                <div class="file-upload-area" id="audioUploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><strong>Arrastra tu canción aquí</strong></p>
                    <p>o haz clic para seleccionar</p>
                    <input type="file" name="audio_file" id="audioFile" accept=".mp3,.wav,.ogg,.m4a" style="display: none;" required>
                </div>
                <div id="audioFileName" class="file-name" style="display: none;"></div>
            </div>

            <!-- Imagen de Portada -->
            <div class="form-group">
                <label>Imagen de Portada (Opcional)</label>
                <div class="file-upload-area" id="coverUploadArea">
                    <i class="fas fa-image"></i>
                    <p><strong>Arrastra la imagen aquí</strong></p>
                    <p>o haz clic para seleccionar</p>
                    <input type="file" name="cover_image" id="coverFile" accept=".jpg,.jpeg,.png,.webp,.gif" style="display: none;">
                </div>
                <div id="coverFileName" class="file-name" style="display: none;"></div>
                <div style="margin-top: 8px; padding: 10px; background: rgba(255, 255, 255, 0.05); border-radius: 6px; font-size: 12px; color: #999;">
                    <i class="fas fa-info-circle"></i> Formatos soportados: JPG, PNG, WEBP, GIF (Máx: 5MB). Recomendado: 500x500px o cuadrado
                </div>
            </div>

            <!-- Tipo de Canción -->
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="isPremium" name="is_premium">
                    <label for="isPremium">¿Es una canción Premium?</label>
                </div>
            </div>

            <!-- Precio (si es premium) -->
            <div class="form-group" id="priceGroup" style="display: none;">
                <label>Precio (en MXN) <span class="required">*</span></label>
                <div class="price-group">
                    <input type="number" name="price" id="price" placeholder="Ej: 29.99" min="0.01" step="0.01">
                    <span style="padding: 12px 0; color: #999;">MXN</span>
                </div>
            </div>

            <!-- Botones -->
            <div class="button-group">
                <button type="submit">
                    <i class="fas fa-upload"></i> Subir Canción
                </button>
                <a href="admin-songs.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </form>

        <!-- Información -->
        <div class="info-box">
            <i class="fas fa-lightbulb"></i>
            <strong>Consejo:</strong> Usa imágenes cuadradas de al menos 300x300px para que se vean bien en el reproductor. Los formatos recomendados son PNG o JPG.
        </div>
    </div>

    <script>
        // Manejo de precio para premium
        const isPremiumCheckbox = document.getElementById('isPremium');
        const priceGroup = document.getElementById('priceGroup');
        const priceInput = document.getElementById('price');

        isPremiumCheckbox.addEventListener('change', function() {
            priceGroup.style.display = this.checked ? 'block' : 'none';
            if (this.checked) {
                priceInput.setAttribute('required', 'required');
            } else {
                priceInput.removeAttribute('required');
                priceInput.value = '';
            }
        });

        // Manejo de drag and drop para audio
        const audioUploadArea = document.getElementById('audioUploadArea');
        const audioFile = document.getElementById('audioFile');
        const audioFileName = document.getElementById('audioFileName');

        audioUploadArea.addEventListener('click', () => audioFile.click());

        audioUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            audioUploadArea.classList.add('dragover');
        });

        audioUploadArea.addEventListener('dragleave', () => {
            audioUploadArea.classList.remove('dragover');
        });

        audioUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            audioUploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                audioFile.files = files;
                updateAudioFileName();
            }
        });

        audioFile.addEventListener('change', updateAudioFileName);

        function updateAudioFileName() {
            if (audioFile.files.length > 0) {
                const file = audioFile.files[0];
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                audioFileName.textContent = `📁 ${file.name} (${sizeMB} MB)`;
                audioFileName.style.display = 'block';
            } else {
                audioFileName.style.display = 'none';
            }
        }

        // Manejo de drag and drop para portada
        const coverUploadArea = document.getElementById('coverUploadArea');
        const coverFile = document.getElementById('coverFile');
        const coverFileName = document.getElementById('coverFileName');

        coverUploadArea.addEventListener('click', () => coverFile.click());

        coverUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            coverUploadArea.classList.add('dragover');
        });

        coverUploadArea.addEventListener('dragleave', () => {
            coverUploadArea.classList.remove('dragover');
        });

        coverUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            coverUploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                coverFile.files = files;
                updateCoverFileName();
            }
        });

        coverFile.addEventListener('change', updateCoverFileName);

        function updateCoverFileName() {
            if (coverFile.files.length > 0) {
                const file = coverFile.files[0];
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                coverFileName.textContent = `🖼️ ${file.name} (${sizeMB} MB)`;
                coverFileName.style.display = 'block';
            } else {
                coverFileName.style.display = 'none';
            }
        }
    </script>
</body>
</html>