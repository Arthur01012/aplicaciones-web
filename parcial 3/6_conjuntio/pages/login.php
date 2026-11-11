<?php
require_once __DIR__ . '/../../config/seguridad.php';
require_once __DIR__ . '/../../config/navegacion.php';
require_once __DIR__ . '/../conexion.php';

iniciarSesionSegura();

if (tieneSesionRol('admin') || tieneSesionRol('docente')) {
    header('Location: dashboard.php', true, 303);
    exit;
}

$error = '';
$errorBaseDatos = '';
$credencialesDemo = null;
$cuenta = trim((string) ($_POST['cuenta'] ?? ''));

try {
    $credencialesDemo = credencialesDemoAdministrador();
} catch (RuntimeException $excepcion) {
    $errorBaseDatos = $excepcion->getMessage();
    http_response_code(500);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorBaseDatos === '') {
    $contrasena = (string) ($_POST['contrasena'] ?? '');

    if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
        $error = 'La sesión del formulario venció. Actualiza la página e intenta de nuevo.';
    } elseif ($cuenta === '' || $contrasena === '') {
        $error = 'Escribe tu correo o usuario y contraseña.';
    } else {
        try {
            $usuario = autenticarAdministradorDemo($cuenta, $contrasena, $credencialesDemo);
            if ($usuario === null) {
                $conexion = conectarBD();
                $usuario = autenticarAdministrador($conexion, $cuenta, $contrasena);
                if ($usuario === null && filter_var($cuenta, FILTER_VALIDATE_EMAIL) === false) {
                    $usuario = autenticarDocente($conexion, $cuenta, $contrasena);
                }
            }

            if ($usuario === null) {
                $error = 'Cuenta o contraseña incorrectas.';
            } else {
                session_regenerate_id(true);
                unset($_SESSION['token_csrf']);
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION[$usuario['rol'] === 'admin' ? 'admin_id' : 'usuario_id'] = $usuario['id'];
                $_SESSION[$usuario['rol'] === 'admin' ? 'admin_nombre' : 'usuario_nombre'] = $usuario['nombre'];
                if (($usuario['demo'] ?? false) === true) {
                    $_SESSION['admin_demo'] = true;
                } else {
                    unset($_SESSION['admin_demo']);
                }
                header('Location: dashboard.php', true, 303);
                exit;
            }
        } catch (mysqli_sql_exception $excepcion) {
            error_log('Parcial 3: error al iniciar sesión: ' . $excepcion->getMessage());
            http_response_code(500);
            $errorBaseDatos = 'No fue posible validar el acceso. Verifica que la migración de la base de datos esté aplicada.';
        } catch (RuntimeException $excepcion) {
            $errorBaseDatos = $excepcion->getMessage();
            http_response_code(503);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../navegacion.css">
    <link rel="stylesheet" href="../../styles/admin.css?v=20261111-2">
</head>
<body class="pagina-login">
    <?php renderizarMigasPan([
        ['texto' => 'Inicio', 'url' => '../../../index.html', 'destino_sesion' => 'inicio'],
        ['texto' => 'Parcial 3', 'url' => '../../index.html', 'destino_sesion' => 'parcial3'],
        ['texto' => 'Práctica 6', 'url' => '../index.php'],
        ['texto' => 'Iniciar sesión'],
    ], '../../config/logout.php'); ?>
    <main class="login-page">
        <section class="card login-card" aria-labelledby="login-title">
            <div class="card-body">
                <h1 class="text-center" id="login-title">Acceso Admin</h1>
                <?php if ($errorBaseDatos !== ''): ?>
                    <div class="alert alert-danger py-2" role="alert"><?= escaparHtml($errorBaseDatos) ?></div>
                <?php elseif ($error !== ''): ?>
                    <div class="alert alert-danger py-2" role="alert"><?= escaparHtml($error) ?></div>
                <?php endif; ?>
                <?php if ($credencialesDemo !== null): ?>
                    <div class="alert alert-info py-2 small" role="note">
                        <strong>Acceso demostrativo de administrador</strong><br>
                        Usuario: <code><?= escaparHtml($credencialesDemo['usuario']) ?></code><br>
                        Contraseña: <code><?= escaparHtml($credencialesDemo['contrasena']) ?></code>
                    </div>
                <?php endif; ?>
                <form action="login.php" method="post">
                    <input type="hidden" name="token_csrf" value="<?= escaparHtml(tokenCsrf()) ?>">
                    <div class="mb-3">
                        <label class="form-label small mb-1" for="cuenta">Correo Electrónico o nombre de usuario</label>
                        <input class="form-control form-control-sm" type="text" id="cuenta" name="cuenta"
                            autocomplete="username" maxlength="190" required value="<?= escaparHtml($cuenta) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small mb-1" for="contrasena">Contraseña</label>
                        <input class="form-control form-control-sm" type="password" id="contrasena" name="contrasena"
                            autocomplete="current-password" required>
                    </div>
                    <button class="btn btn-primary btn-sm w-100 fw-semibold" type="submit">Ingresar</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
