<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_cursos";

$mensaje = "Conexión exitosa.";
$error = false;


try {
    $conexion = new mysqli($host, $user, $pass, $db);
    $conexion->set_charset("utf8mb4");
} catch (mysqli_sql_exception $excepcion) {
    http_response_code(500);
    $mensaje = "Error de conexión: " . $excepcion->getMessage();
    $error = true;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conexión a base de datos | Parcial 3</title>
    <link rel="stylesheet" href="../navegacion.css">
</head>

<body>
    <nav class="site-breadcrumbs" aria-label="Migas de pan">
        <ol>
            <li><a href="../../index.html">Inicio</a></li>
            <li><a href="../index.html">Parcial 3</a></li>
            <li aria-current="page">Conexión a base de datos</li>
        </ol>
        <a class="site-home-link" href="../../index.html">Volver al inicio</a>
    </nav>
    <main>
        <h1>Conexión a base de datos</h1>
        <p role="<?= $error ? 'alert' : 'status' ?>"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
    </main>
</body>

</html>