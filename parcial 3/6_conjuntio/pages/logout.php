<?php
require_once __DIR__ . '/../../config/seguridad.php';

iniciarSesionSegura();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarTokenCsrf($_POST['token_csrf'] ?? null)) {
    http_response_code(400);
    exit('Solicitud de cierre de sesión no válida.');
}

cerrarSesionSegura();
header('Location: login.php', true, 303);
exit;
