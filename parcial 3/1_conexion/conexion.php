<?php
require_once __DIR__ . '/../config/conexion.php';

$error = '';

try {
    $conexion = conectarBD();
    $mensaje = 'Conexión exitosa.';
    $conexion->close();
} catch (RuntimeException $excepcion) {
    http_response_code(503);
    $mensaje = $excepcion->getMessage();
    $error = 'true';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Prueba de conexión MySQLi del parcial 3.">
    <title>Conexión MySQL | Parcial 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../navegacion.css">
</head>
<body class="bg-light">
    <nav class="site-breadcrumbs" aria-label="Migas de pan">
        <ol>
            <li><a href="../../index.html">Inicio</a></li>
            <li><a href="../index.html">Parcial 3</a></li>
            <li aria-current="page">Conexión</li>
        </ol>
        <a class="site-home-link" href="../index.html">Volver a prácticas</a>
    </nav>
    <main class="container py-5">
        <section class="card mx-auto shadow-sm" style="max-width: 42rem">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Conexión a MySQL</h1>
                <p class="alert alert-<?= $error === '' ? 'success' : 'danger' ?> mb-0"
                    role="<?= $error === '' ? 'status' : 'alert' ?>">
                    <?= htmlspecialchars($mensaje, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
