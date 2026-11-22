<?php
session_start();
// Seguridad: solo administradores
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../5_crud/login.php');
    exit;
}
require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $orden  = is_numeric($_POST['orden'] ?? '') ? (int)$_POST['orden'] : 0;
    $imagen = $_FILES['imagen'] ?? null;

    // Validar archivo
    if (!$imagen || $imagen['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Por favor selecciona una imagen.';
    } elseif ($imagen['error'] !== UPLOAD_ERR_OK) {
        $error = 'Ocurrió un error al subir el archivo.';
    } else {
        $maxSize = 2 * 1024 * 1024; // 2 MB
        if ($imagen['size'] > $maxSize) {
            $error = 'La imagen supera el peso máximo de 2MB.';
        }
        $extPermitidas = ['jpg','jpeg','png','webp'];
        $mimePermitidos = ['image/jpeg','image/png','image/webp'];
        $ext = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $imagen['tmp_name']);
        finfo_close($finfo);
        if (!in_array($ext,$extPermitidas) || !in_array($mimeReal,$mimePermitidos)) {
            $error = 'Formato no válido. Solo JPG, PNG y WEBP.';
        }
    }

    if (empty($error)) {
        $nombreUnico = uniqid('banner_', true) . '.' . $ext;
        $dirDestino = __DIR__ . '/../uploads/carrusel/';
        if (!file_exists($dirDestino)) {
            mkdir($dirDestino, 0777, true);
        }
        $rutaFinal = $dirDestino . $nombreUnico;
        if (move_uploaded_file($imagen['tmp_name'], $rutaFinal)) {
            $sql = "INSERT INTO carrusel (titulo, imagen, orden) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ssi', $titulo, $nombreUnico, $orden);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                header('Location: carrusel_listado.php');
                exit;
            } else {
                $error = 'Error al registrar en la base de datos.';
            }
        } else {
            $error = 'No se pudo guardar la imagen en el servidor.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Imagen para Carrusel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<h1>Subir Imagen para Carrusel</h1>
<a href="carrusel_listado.php" class="btn btn-link mb-3">Ver Listado</a>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="POST" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-6">
        <label for="titulo" class="form-label">Título / Leyenda:</label>
        <input type="text" name="titulo" id="titulo" class="form-control" value="<?= htmlspecialchars($titulo ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label for="orden" class="form-label">Orden de aparición:</label>
        <input type="number" name="orden" id="orden" class="form-control" value="<?= htmlspecialchars($orden ?? 1) ?>">
    </div>
    <div class="col-md-12">
        <label for="imagen" class="form-label">Selecciona una imagen (JPG, PNG, WEBP):</label>
        <input type="file" name="imagen" id="imagen" class="form-control" accept="image/jpeg,image/png,image/webp" required>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Subir Imagen</button>
    </div>
</form>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
