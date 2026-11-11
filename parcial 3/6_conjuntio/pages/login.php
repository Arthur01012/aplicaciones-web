<?php
require_once __DIR__ . '/../../config/seguridad.php';
require_once __DIR__ . '/../conexion.php';

iniciarSesionSegura();

if (in_array($_SESSION['rol'] ?? '', ['admin', 'docente'], true)) {
    header('Location: dashboard.php', true, 303);
    exit;
}

$error = '';
$errorBaseDatos = '';
$cuenta = trim((string) ($_POST['cuenta'] ?? ''));

try {
    $conexion = conectarBD();
} catch (RuntimeException $excepcion) {
    $errorBaseDatos = $excepcion->getMessage();
    http_response_code(503);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorBaseDatos === '') {
    $contrasena = (string) ($_POST['contrasena'] ?? '');

    if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
        $error = 'La sesión del formulario venció. Actualiza la página e intenta de nuevo.';
    } elseif ($cuenta === '' || $contrasena === '') {
        $error = 'Escribe tu correo o usuario y contraseña.';
    } else {
        try {
            $usuario = filter_var($cuenta, FILTER_VALIDATE_EMAIL) !== false
                ? autenticarAdministrador($conexion, $cuenta, $contrasena)
                : autenticarDocente($conexion, $cuenta, $contrasena);

            if ($usuario === null) {
                $error = 'Cuenta o contraseña incorrectas.';
            } else {
                session_regenerate_id(true);
                unset($_SESSION['token_csrf']);
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION[$usuario['rol'] === 'admin' ? 'admin_id' : 'usuario_id'] = $usuario['id'];
                $_SESSION[$usuario['rol'] === 'admin' ? 'admin_nombre' : 'usuario_nombre'] = $usuario['nombre'];
                header('Location: dashboard.php', true, 303);
                exit;
            }
        } catch (mysqli_sql_exception $excepcion) {
            error_log('Parcial 3: error al iniciar sesión: ' . $excepcion->getMessage());
            http_response_code(500);
            $errorBaseDatos = 'No fue posible validar el acceso. Verifica que la migración de la base de datos esté aplicada.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <a class="site-home-link" href="../../index.html">Volver a prácticas</a>
    </nav>
    <main class="formulario">
        <p class="nombre-sitio">EduControl</p>
        <h1>Iniciar sesión</h1>
        <p>Accede al panel académico.</p>

        <?php if ($errorBaseDatos !== ''): ?>
            <p class="mensaje error" role="alert"><?= escaparHtml($errorBaseDatos) ?></p>
        <?php elseif ($error !== ''): ?>
            <p class="mensaje error" role="alert"><?= escaparHtml($error) ?></p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="hidden" name="token_csrf" value="<?= escaparHtml(tokenCsrf()) ?>">
            <div class="entradas">
                <label for="cuenta">Correo de administrador o usuario docente</label>
                <input type="text" id="cuenta" name="cuenta" autocomplete="username"
                    maxlength="190" required value="<?= escaparHtml($cuenta) ?>">
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
    </main>
</body>
</html>
