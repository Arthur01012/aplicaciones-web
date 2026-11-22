<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php'; // adjust path to config from 4_alta

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../2_login/pages/login.php?error=acceso_denegado');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $destacado = isset($_POST['destacado']) ? 1 : 0;

    // Server‑side validation
    if (empty($titulo) || empty($descripcion) || $precio === '') {
        $error = 'Servidor: Todos los campos marcados son obligatorios.';
    } elseif (!is_numeric($precio)) {
        $error = 'Servidor: El precio debe ser un número válido.';
    } elseif ((float)$precio < 0) {
        $error = 'Servidor: El precio no puede ser un valor negativo.';
    } else {
        $precio_float = (float)$precio;
        $sql = "INSERT INTO cursos (titulo, descripcion, precio, destacado) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssdi", $titulo, $descripcion, $precio_float, $destacado);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                // PRG pattern
                header('Location: ../2_login/pages/dashboard.php');
                exit;
            } else {
                $error = 'Error al registrar el curso: ' . mysqli_error($conexion);
            }
        } else {
            $error = 'Error al preparar la consulta: ' . mysqli_error($conexion);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Curso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="card-title text-center fw-bold mb-4">Crear Nuevo Curso</h3>
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    <form id="formCurso" method="POST" action="" novalidate>
                        <!-- Título -->
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Curso</label>
                            <input type="text" name="titulo" id="titulo" class="form-control" value="<?= htmlspecialchars($titulo ?? '') ?>">
                            <div id="feedback-titulo" class="invalid-feedback"></div>
                        </div>
                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?= htmlspecialchars($descripcion ?? '') ?></textarea>
                            <div id="feedback-descripcion" class="invalid-feedback"></div>
                        </div>
                        <!-- Precio -->
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio ($)</label>
                            <input type="number" step="0.01" min="0" name="precio" id="precio" class="form-control" value="<?= htmlspecialchars($precio ?? '') ?>">
                            <div id="feedback-precio" class="invalid-feedback"></div>
                        </div>
                        <!-- Destacado -->
                        <div class="form-check mb-4">
                            <input type="checkbox" name="destacado" id="destacado" class="form-check-input" value="1" <?= isset($destacado) && $destacado ? 'checked' : '' ?>>
                            <label for="destacado" class="form-check-label">Marcar como Curso Destacado</label>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">Guardar Curso</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formCurso').addEventListener('submit', function(e) {
    // Reset previous errors
    const inputs = ['titulo', 'descripcion', 'precio'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        el.classList.remove('is-invalid');
        document.getElementById('feedback-' + id).textContent = '';
    });

    let esValido = true;
    const titulo = document.getElementById('titulo');
    if (titulo.value.trim() === '' || titulo.value.trim().length < 3) {
        marcarError(titulo, 'feedback-titulo', 'El título es obligatorio y debe tener al menos 3 caracteres.');
        esValido = false;
    }
    const descripcion = document.getElementById('descripcion');
    if (descripcion.value.trim() === '') {
        marcarError(descripcion, 'feedback-descripcion', 'La descripción es obligatoria.');
        esValido = false;
    }
    const precio = document.getElementById('precio');
    const valPrecio = parseFloat(precio.value);
    if (precio.value.trim() === '') {
        marcarError(precio, 'feedback-precio', 'Por favor ingresa un precio.');
        esValido = false;
    } else if (isNaN(valPrecio) || valPrecio < 0) {
        marcarError(precio, 'feedback-precio', 'El precio debe ser un número mayor o igual a 0.');
        esValido = false;
    }
    if (!esValido) {
        e.preventDefault();
    }
});
function marcarError(input, idFeedback, mensaje) {
    input.classList.add('is-invalid');
    document.getElementById(idFeedback).textContent = mensaje;
}
</script>
</body>
</html>
