<?php

require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

// Recibir ID

$idcurso = $_GET['idcurso'] ?? null;

$curso = null;

// Validar ID

if ($idcurso && is_numeric($idcurso)) {

    $sql = "SELECT *
     FROM cursos
     WHERE idcurso = ?";

    $stmt = mysqli_prepare($conexion, $sql);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "i", $idcurso);

        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        $curso = mysqli_fetch_assoc($resultado);

        mysqli_stmt_close($stmt);
    }
}

// Si el ID es inválido

if (!$curso) {

    header('Location: index.php');

    exit;
}

?>
<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($curso['titulo']) ?> - DevAcademy</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../navegacion.css?v=20261111-3">

</head>

<body class="container py-3">
<nav class="site-breadcrumbs" aria-label="Migas de pan">
    <ol>
        <li><a href="../../index.html">Inicio</a></li>
        <li><a href="../index.html">Parcial 3</a></li>
        <li><a href="index.php">Práctica 7: Catálogo</a></li>
        <li aria-current="page">Detalle</li>
    </ol>
    <a class="site-home-link" href="../index.html">Volver a prácticas</a>
</nav>

<header>

    <h1>DevAcademy</h1>

    <nav>

        <a href="index.php#cursos">← Volver al Catálogo</a>

    </nav>

</header>

<hr>

<main>

<div class="card" style="max-width: 600px;">

<div class="card-body">

    <h2><?= htmlspecialchars($curso['titulo']) ?></h2>

    <hr>

    <h4>Descripción del Curso</h4>

    <p><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>

    <p>
        <strong>
            Precio: $<?= number_format($curso['precio'], 2) ?>
        </strong>
    </p>

    <br>

    <a href="index.php#cursos" class="btn btn-secondary btn-sm">Regresar</a>

</div>

</div>

</main>

<hr>

<footer>

<p>© 2026 DevAcademy.</p>

</footer>

</body>

</html>
