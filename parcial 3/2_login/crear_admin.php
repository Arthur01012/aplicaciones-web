<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$nombre = trim((string) (getenv('ADMIN_NOMBRE') ?: ''));
$email = trim((string) (getenv('ADMIN_EMAIL') ?: ''));
$password = getenv('ADMIN_PASSWORD');

if ($nombre === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fwrite(STDERR, "Configura ADMIN_NOMBRE y un ADMIN_EMAIL válido.\n");
    exit(1);
}

if ($password === false || strlen($password) < 12) {
    fwrite(STDERR, "Configura ADMIN_PASSWORD con al menos 12 caracteres.\n");
    exit(1);
}

try {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $conexion = conectarBD();
    $consulta = $conexion->prepare(
        "INSERT INTO usuarios (nombre, email, psw, rol, activo)
         VALUES (?, ?, ?, 'admin', 1)
         ON DUPLICATE KEY UPDATE
             nombre = VALUES(nombre),
             psw = VALUES(psw),
             rol = 'admin',
             activo = 1"
    );
    $consulta->bind_param('sss', $nombre, $email, $passwordHash);
    $consulta->execute();
    $consulta->close();
} catch (mysqli_sql_exception $excepcion) {
    error_log('Parcial 3: error al crear administrador: ' . $excepcion->getMessage());
    fwrite(STDERR, "No fue posible crear el administrador. Revisa la conexión y el esquema de la base de datos.\n");
    exit(1);
} catch (RuntimeException $excepcion) {
    fwrite(STDERR, "No fue posible crear el administrador: {$excepcion->getMessage()}\n");
    exit(1);
}

fwrite(STDOUT, "Administrador creado o actualizado correctamente.\n");
