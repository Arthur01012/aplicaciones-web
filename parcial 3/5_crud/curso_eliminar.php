<?php
require_once __DIR__ . '/../config/seguridad.php';
exigirSesion(['admin'], '../2_login/pages/login.php?error=acceso_denegado');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarTokenCsrf($_POST['token_csrf'] ?? null)) {
    http_response_code(400);
    exit('Solicitud de eliminación no válida.');
}

$idEntrada = $_POST['idcurso'] ?? null;
$idCurso = is_string($idEntrada) || is_int($idEntrada)
    ? filter_var($idEntrada, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;

if ($idCurso === false) {
    http_response_code(400);
    exit('Identificador de curso no válido.');
}

require_once __DIR__ . '/../config/conexion.php';

try {
    $conexion = conectarBD();
    $consulta = $conexion->prepare('DELETE FROM cursos WHERE idcurso = ?');
    $consulta->bind_param('i', $idCurso);
    $consulta->execute();
    $eliminado = $consulta->affected_rows === 1;
    $consulta->close();

    header(
        'Location: dashboard.php' . ($eliminado ? '' : '?error=no_encontrado'),
        true,
        303
    );
    exit;
} catch (RuntimeException $excepcion) {
    http_response_code(503);
    exit(escaparHtml($excepcion->getMessage()));
} catch (mysqli_sql_exception $excepcion) {
    error_log('Parcial 3 CRUD: no fue posible eliminar el curso: ' . $excepcion->getMessage());
    if ($excepcion->getCode() === 1451) {
        header('Location: dashboard.php?error=relaciones', true, 303);
        exit;
    }
    http_response_code(500);
    exit('No fue posible eliminar el curso.');
}
