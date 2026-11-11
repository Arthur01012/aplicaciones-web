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

    $rol = $_SESSION['rol'] ?? '';
    $idUsuario = $_SESSION['admin_id'] ?? $_SESSION['usuario_id'] ?? null;

    if (!in_array($rol, $roles, true) || !is_int($idUsuario) || $idUsuario < 1) {
        header('Location: ' . $rutaLogin, true, 303);
        exit;
    }
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

function autenticarAdministrador(mysqli $conexion, string $email, string $contrasena): ?array
{
    $consulta = $conexion->prepare(
        "SELECT id, nombre, psw
         FROM usuarios
         WHERE email = ? AND rol = 'admin' AND activo = 1
         LIMIT 1"
    );
    $consulta->bind_param('s', $email);
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
