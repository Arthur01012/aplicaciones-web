<?php
require_once __DIR__ . '/seguridad.php';
iniciarSesionSegura();

$destinos = [
    'inicio' => '../../index.html',
    'parcial3' => '../index.html',
];
$destino = $_POST['destino'] ?? null;

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || !validarTokenCsrf($_POST['token_csrf'] ?? null)
    || !is_string($destino)
    || !isset($destinos[$destino])
) {
    http_response_code(400);
    exit('Solicitud de navegación no válida.');
}

cerrarSesionSegura();
header('Location: ' . $destinos[$destino], true, 303);
exit;
