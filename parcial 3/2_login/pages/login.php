<?php
require_once __DIR__ . '/../../config/seguridad.php';
require_once __DIR__ . '/../../config/navegacion.php';
iniciarSesionSegura();

$siguienteEntrada = $_POST['siguiente'] ?? $_GET['siguiente'] ?? '';
$siguiente = is_string($siguienteEntrada) && in_array($siguienteEntrada, ['alta', 'crud'], true)
    ? $siguienteEntrada
    : '';
$destinoAdministrador = match ($siguiente) {
    'alta' => '../../4_alta/alta.php',
    'crud' => '../../5_crud/dashboard.php',
    default => 'dashboard.php',
};

if (tieneSesionRol('admin')) {
    header('Location: ' . $destinoAdministrador, true, 303);
    exit;
} elseif (tieneSesionRol('docente')) {
    header('Location: ../../6_conjuntio/pages/dashboard.php', true, 303);
    exit;
}

$error = isset($_GET['error']) && $_GET['error'] === 'acceso_denegado'
    ? 'Debes iniciar sesión para acceder al panel de administración.'
    : '';
$errorBaseDatos = '';
$cuenta = '';
$credencialesDemo = null;

try {
    $credencialesDemo = credencialesDemoAdministrador();
} catch (RuntimeException $excepcion) {
    http_response_code(500);
    $errorBaseDatos = $excepcion->getMessage();
}

require_once __DIR__ . '/../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorBaseDatos === '') {
    $cuentaIngresada = $_POST['cuenta'] ?? '';
    $passwordIngresada = $_POST['password'] ?? '';
    $cuenta = is_string($cuentaIngresada) ? trim($cuentaIngresada) : '';
    $password = is_string($passwordIngresada) ? $passwordIngresada : '';

    if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
        $error = 'La sesión del formulario venció. Intenta de nuevo.';
    } elseif ($cuenta === '' || $password === '') {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            $usuario = autenticarAdministradorDemo($cuenta, $password, $credencialesDemo);
            if ($usuario === null) {
                $conexion = conectarBD();
                $usuario = autenticarAdministrador($conexion, $cuenta, $password);
                if ($usuario === null && filter_var($cuenta, FILTER_VALIDATE_EMAIL) === false) {
                    $usuario = autenticarDocente($conexion, $cuenta, $password);
                }
            }

            if ($usuario === null) {
                $error = 'Credenciales incorrectas o sin permisos de administrador.';
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
                $destino = $usuario['rol'] === 'admin'
                    ? $destinoAdministrador
                    : '../../6_conjuntio/pages/dashboard.php';
                header('Location: ' . $destino, true, 303);
                exit;
            }
        } catch (mysqli_sql_exception $excepcion) {
            error_log('DevAcademy: no fue posible validar el inicio de sesión: ' . $excepcion->getMessage());
            http_response_code(500);
            $errorBaseDatos = 'No fue posible validar el acceso. Revisa la configuración y el esquema de la base de datos.';
        } catch (RuntimeException $excepcion) {
            http_response_code(503);
            $errorBaseDatos = $excepcion->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acceso al panel administrativo de DevAcademy.">
    <title>Acceso Admin | DevAcademy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../navegacion.css?v=20261111-3" rel="stylesheet">
    <link href="../../styles/admin.css?v=20261111-3" rel="stylesheet">
</head>
<body class="pagina-login">
    <?php renderizarMigasPan([
        ['texto' => 'Inicio', 'url' => '../../../index.html', 'destino_sesion' => 'inicio'],
        ['texto' => 'Parcial 3', 'url' => '../../index.html', 'destino_sesion' => 'parcial3'],
        ['texto' => 'Práctica 2', 'url' => '../index.php'],
        ['texto' => 'Iniciar sesión'],
    ], '../../config/logout.php'); ?>
    <main class="login-page">
        <section class="card login-card" aria-labelledby="login-title">
            <div class="card-body">
                <h1 class="text-center mb-4" id="login-title">Acceso Admin</h1>

                <?php if ($errorBaseDatos !== ''): ?>
                    <div class="alert alert-danger py-2" role="alert">
                        <?= escaparHtml($errorBaseDatos) ?>
                    </div>
                <?php elseif ($error !== ''): ?>
                    <div class="alert alert-danger py-2" role="alert">
                        <?= escaparHtml($error) ?>
                    </div>
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
                    <input type="hidden" name="siguiente" value="<?= escaparHtml($siguiente) ?>">
                    <div class="mb-3">
                        <label class="form-label small mb-1" for="cuenta">Correo Electrónico o nombre de usuario</label>
                        <input class="form-control form-control-sm" id="cuenta" name="cuenta" type="text"
                            autocomplete="username" maxlength="190" required
                            value="<?= escaparHtml($cuenta) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small mb-1" for="password">Contraseña</label>
                        <input class="form-control form-control-sm" id="password" name="password" type="password"
                            autocomplete="current-password" required>
                    </div>
                    <button class="btn btn-primary btn-sm w-100 fw-semibold" type="submit">Ingresar</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
