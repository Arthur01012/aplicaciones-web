<?php
require_once __DIR__ . '/../../config/seguridad.php';
iniciarSesionSegura();

if (isset($_SESSION['admin_id']) && ($_SESSION['rol'] ?? '') === 'admin') {
    header('Location: dashboard.php', true, 303);
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';

$error = isset($_GET['error']) && $_GET['error'] === 'acceso_denegado'
    ? 'Debes iniciar sesión para acceder al panel de administración.'
    : '';
$errorBaseDatos = '';
$email = '';
$conexion = null;

try {
    $conexion = conectarBD();
} catch (RuntimeException $excepcion) {
    http_response_code(503);
    $errorBaseDatos = $excepcion->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorBaseDatos === '') {
    $emailIngresado = $_POST['email'] ?? '';
    $passwordIngresada = $_POST['password'] ?? '';
    $email = is_string($emailIngresado) ? trim($emailIngresado) : '';
    $password = is_string($passwordIngresada) ? $passwordIngresada : '';

    if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
        $error = 'La sesión del formulario venció. Intenta de nuevo.';
    } elseif ($email === '' || $password === '') {
        $error = 'Por favor, completa todos los campos.';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $error = 'Escribe un correo electrónico válido.';
    } else {
        try {
            $administrador = autenticarAdministrador($conexion, $email, $password);

            if ($administrador === null) {
                $error = 'Credenciales incorrectas o sin permisos de administrador.';
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $administrador['id'];
                $_SESSION['admin_nombre'] = $administrador['nombre'];
                $_SESSION['rol'] = 'admin';
                header('Location: dashboard.php', true, 303);
                exit;
            }
        } catch (mysqli_sql_exception $excepcion) {
            error_log('DevAcademy: no fue posible validar el inicio de sesión: ' . $excepcion->getMessage());
            http_response_code(500);
            $errorBaseDatos = 'No fue posible validar el acceso. Revisa la configuración y el esquema de la base de datos.';
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
    <link href="../../styles/admin.css" rel="stylesheet">
</head>
<body>
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

                <form action="login.php" method="post">
                    <input type="hidden" name="token_csrf" value="<?= escaparHtml(tokenCsrf()) ?>">
                    <div class="mb-3">
                        <label class="form-label small mb-1" for="email">Correo Electrónico</label>
                        <input class="form-control form-control-sm" id="email" name="email" type="email"
                            autocomplete="username" maxlength="190" required
                            value="<?= escaparHtml($email) ?>">
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
