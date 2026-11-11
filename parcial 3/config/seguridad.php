<?php

function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (!session_start()) {
        throw new RuntimeException('No fue posible iniciar la sesión.');
    }
}

function escaparHtml(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function exigirSesion(array $roles, string $rutaLogin): void
{
    iniciarSesionSegura();

    foreach ($roles as $rol) {
        if (tieneSesionRol($rol)) {
            return;
        }
    }

    header('Location: ' . $rutaLogin, true, 303);
    exit;
}

function tieneSesionRol(string $rol): bool
{
    if (($_SESSION['rol'] ?? '') !== $rol) {
        return false;
    }

    if ($rol === 'admin' && ($_SESSION['admin_demo'] ?? false) === true) {
        return true;
    }

    $idUsuario = match ($rol) {
        'admin' => $_SESSION['admin_id'] ?? null,
        'docente' => $_SESSION['usuario_id'] ?? null,
        default => null,
    };

    return is_int($idUsuario) && $idUsuario > 0;
}

function credencialesDemoAdministrador(): ?array
{
    $usuario = getenv('DEMO_ADMIN_USERNAME');
    $contrasena = getenv('DEMO_ADMIN_PASSWORD');

    if (($usuario === false || $usuario === '') && ($contrasena === false || $contrasena === '')) {
        if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
            return ['usuario' => 'carlos.mendoza', 'contrasena' => 'EduControl2026!'];
        }

        return null;
    }

    if ($usuario === false || $usuario === '' || $contrasena === false || $contrasena === '') {
        throw new RuntimeException(
            'Configura DEMO_ADMIN_USERNAME y DEMO_ADMIN_PASSWORD para habilitar el acceso demostrativo.'
        );
    }

    return ['usuario' => $usuario, 'contrasena' => $contrasena];
}

function autenticarAdministradorDemo(string $usuario, string $contrasena, ?array $credenciales): ?array
{
    if (
        $credenciales === null
        || !isset($credenciales['usuario'], $credenciales['contrasena'])
        || !is_string($credenciales['usuario'])
        || !is_string($credenciales['contrasena'])
        || !hash_equals($credenciales['usuario'], $usuario)
        || !hash_equals($credenciales['contrasena'], $contrasena)
    ) {
        return null;
    }

    return [
        'id' => 0,
        'nombre' => 'Administrador de demostración',
        'rol' => 'admin',
        'demo' => true,
    ];
}

function tokenCsrf(): string
{
    iniciarSesionSegura();

    if (!isset($_SESSION['token_csrf']) || !is_string($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['token_csrf'];
}

function validarTokenCsrf(mixed $token): bool
{
    iniciarSesionSegura();

    return is_string($token)
        && isset($_SESSION['token_csrf'])
        && is_string($_SESSION['token_csrf'])
        && hash_equals($_SESSION['token_csrf'], $token);
}

function cerrarSesionSegura(): void
{
    iniciarSesionSegura();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametrosCookie = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametrosCookie['path'],
            $parametrosCookie['domain'],
            $parametrosCookie['secure'],
            $parametrosCookie['httponly']
        );
    }

    session_destroy();
}

function autenticarAdministrador(mysqli $conexion, string $cuenta, string $contrasena): ?array
{
    if (filter_var($cuenta, FILTER_VALIDATE_EMAIL) !== false) {
        $consulta = $conexion->prepare(
            "SELECT id, nombre, psw
             FROM usuarios
             WHERE email = ? AND rol = 'admin' AND activo = 1
             LIMIT 1"
        );
    } else {
        $consulta = $conexion->prepare(
            "SELECT id, nombre, psw
             FROM usuarios
             WHERE nombre_usuario = ? AND rol = 'admin' AND activo = 1
             LIMIT 1"
        );
    }

    $consulta->bind_param('s', $cuenta);
    $consulta->execute();
    $consulta->bind_result($id, $nombre, $hash);
    $encontrado = $consulta->fetch();
    $consulta->close();

    if (!$encontrado || !password_verify($contrasena, $hash)) {
        return null;
    }

    return ['id' => (int) $id, 'nombre' => (string) $nombre, 'rol' => 'admin'];
}

function autenticarDocente(mysqli $conexion, string $usuario, string $contrasena): ?array
{
    $consulta = $conexion->prepare(
        "SELECT id, nombre, psw
         FROM usuarios
         WHERE nombre_usuario = ? AND rol = 'docente' AND activo = 1
         LIMIT 1"
    );
    $consulta->bind_param('s', $usuario);
    $consulta->execute();
    $consulta->bind_result($id, $nombre, $hash);
    $encontrado = $consulta->fetch();
    $consulta->close();

    if (!$encontrado || !password_verify($contrasena, $hash)) {
        return null;
    }

    return ['id' => (int) $id, 'nombre' => (string) $nombre, 'rol' => 'docente'];
}
