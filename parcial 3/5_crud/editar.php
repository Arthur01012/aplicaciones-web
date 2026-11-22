<?php

session_start();

if (!isset($_SESSION['admin_id'])) {

    header('Location: login.php');

    exit;
}

require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

$idcurso = $_GET['idcurso'] ?? $_POST['idcurso'] ?? null;

if (!$idcurso || !is_numeric($idcurso)) {

    header('Location: dashboard.php');

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');

    $descripcion = trim($_POST['descripcion'] ?? '');

    $precio = trim($_POST['precio'] ?? '');

    $destacado = isset($_POST['destacado']) ? 1 : 0;

    $sql = "
    UPDATE cursos
    SET
        titulo = ?,
        descripcion = ?,
        precio = ?,
        destacado = ?
    WHERE idcurso = ?
    ";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssdii",
        $titulo,
        $descripcion,
        $precio,
        $destacado,
        $idcurso
    );

    mysqli_stmt_execute($stmt);

    header('Location: dashboard.php');

    exit;
}

$sql = "SELECT * FROM cursos WHERE idcurso = ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "i", $idcurso);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$curso = mysqli_fetch_assoc($resultado);

if (!$curso) {

    header('Location: dashboard.php');

    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Curso</title>
<link rel="stylesheet" href="../navegacion.css?v=20261111-3">
</head>
<body>
<nav class="site-breadcrumbs" aria-label="Migas de pan">
    <ol>
        <li><a href="../../index.html">Inicio</a></li>
        <li><a href="../index.html">Parcial 3</a></li>
        <li><a href="dashboard.php">Práctica 5: CRUD</a></li>
        <li aria-current="page">Editar curso</li>
    </ol>
    <a class="site-home-link" href="../index.html">Volver a prácticas</a>
</nav>

<h1>Editar Curso</h1>

<a href="dashboard.php">← Volver al Dashboard</a>

<br><br>

<form method="POST" action="">

<input type="hidden" name="idcurso" value="<?= $curso['idcurso'] ?>">

<label for="titulo">Título del Curso:</label>
<input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($curso['titulo']) ?>" required>

<label for="descripcion">Descripción:</label>
<textarea name="descripcion" id="descripcion" rows="4" cols="40" required><?= htmlspecialchars($curso['descripcion']) ?></textarea>

<label for="precio">Precio ($):</label>
<input type="number" step="0.01" min="0" name="precio" id="precio" value="<?= htmlspecialchars($curso['precio']) ?>" required>

<input type="checkbox" name="destacado" id="destacado" value="1" <?= $curso['destacado'] ? 'checked' : '' ?>>
<label for="destacado">Marcar como Curso Destacado</label>

<button type="submit">Guardar Cambios</button>

</form>

</body>
</html>
