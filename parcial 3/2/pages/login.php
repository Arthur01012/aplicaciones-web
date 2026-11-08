<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$errorBaseDatos = '';

try {
    require_once __DIR__ . '/../conexion.php';
    $conexion = conectarBD();
} catch (RuntimeException $excepcion) {
    $errorBaseDatos = $excepcion->getMessage();
    http_response_code(503);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorBaseDatos === '') {
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $contrasena = (string) ($_POST['contrasena'] ?? '');

    if ($usuario === '' || $contrasena === '') {
        $error = 'Escribe tu usuario y contraseña.';
    } else {
        try {
            $consulta = $conexion->prepare(
                'SELECT user_id, password_hash, full_name
                 FROM users
                 WHERE username = ? AND is_active = 1
                 LIMIT 1'
            );
            $consulta->bind_param('s', $usuario);
            $consulta->execute();
            $consulta->bind_result($userId, $passwordHash, $fullName);

            if ($consulta->fetch() && password_verify($contrasena, $passwordHash)) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $userId;
                header('Location: dashboard.php', true, 303);
                exit;
            }

            $consulta->close();
            $error = 'Usuario o contraseña incorrectos.';
        } catch (mysqli_sql_exception $excepcion) {
            error_log('EduControl: no fue posible validar el inicio de sesión: ' . $excepcion->getMessage());
            http_response_code(500);
            $errorBaseDatos = 'No fue posible validar el acceso. Verifica que database.sql se haya importado correctamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inicio de sesión de EduControl.">
    <title>Iniciar sesión | EduControl</title>
    <link rel="stylesheet" href="../../navegacion.css">
    <link rel="stylesheet" href="../styles/login_style.css">
</head>
<body class="pagina-login">
    <nav class="site-breadcrumbs" aria-label="Migas de pan">
        <ol>
            <li><a href="../../../index.html">Inicio</a></li>
            <li><a href="../../index.html">Parcial 3</a></li>
            <li aria-current="page">EduControl</li>
        </ol>
        <a class="site-home-link" href="../../../index.html">Volver al inicio</a>
    </nav>
    <main class="formulario">
        <p class="nombre-sitio">EduControl</p>
        <h1>Iniciar sesión</h1>
        <p>Accede al panel de control docente.</p>

        <?php if ($errorBaseDatos !== ''): ?>
            <p class="mensaje error" role="alert"><?= htmlspecialchars($errorBaseDatos, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif ($error !== ''): ?>
            <p class="mensaje error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div class="entradas">
                <label for="usuario">Nombre de usuario</label>
                <input type="text" id="usuario" name="usuario" autocomplete="username"
                    maxlength="80" required value="<?= htmlspecialchars((string) ($_POST['usuario'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="entradas">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena"
                    autocomplete="current-password" required>
            </div>
            <div class="entrar">
                <button type="submit">Entrar al dashboard</button>
            </div>
        </form>

        <p class="credenciales-demo">
            Acceso local de demostración:<br>
            <strong>carlos.mendoza</strong> / <strong>EduControl2026!</strong>
        </p>
    </main>
</body>
</html>
