<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';
$conexion = conectarBD();

// Redirigir si ya está logueado
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
// Mostrar mensaje de error por falta de autenticación
if (isset($_GET['error']) && $_GET['error'] === 'acceso_denegado') {
    $error = 'Debes iniciar sesión para poder acceder al panel de administración.';
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email && $password) {
        $sql = "SELECT id, nombre, email, psw FROM usuarios WHERE (email = ? OR nombre_usuario = ?) AND rol = 'admin'";
        $stmt = mysqli_prepare($conexion, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $email, $email);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $usuario = mysqli_fetch_assoc($resultado);
            mysqli_stmt_close($stmt);

            if ($usuario && ($password === $usuario['psw'] || password_verify($password, $usuario['psw']))) {
                // Credenciales válidas, crear sesión
                $_SESSION['admin_id'] = (int) $usuario['id'];
                $_SESSION['admin_nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = 'admin';
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Credenciales incorrectas o no tienes permisos de administrador.';
            }
        } else {
            $error = 'Error interno al preparar la consulta.';
        }
    } else {
        $error = 'Completa ambos campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../navegacion.css?v=20261111-3">
</head>
<body class="bg-light">
<nav class="site-breadcrumbs" aria-label="Migas de pan">
    <ol>
        <li><a href="../../../index.html">Inicio</a></li>
        <li><a href="../../index.html">Parcial 3</a></li>
        <li aria-current="page">Práctica 2: Login</li>
    </ol>
    <a class="site-home-link" href="../../index.html">Volver a prácticas</a>
</nav>
<div class="container d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 56px);">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">Acceso Administrador</h4>
                <?php if ($error): ?>
                <div class="alert alert-danger py-2">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
