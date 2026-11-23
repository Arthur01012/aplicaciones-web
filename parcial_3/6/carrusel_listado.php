<?php
session_start();
// Seguridad: solo administradores
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../5/login.php');
    exit;
}
require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

$sql = "SELECT * FROM carrusel ORDER BY orden ASC, id DESC";
try {
    $resultado = mysqli_query($conexion, $sql);
} catch (mysqli_sql_exception) {
    $resultado = false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Imágenes Carrusel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../navegacion.css?v=20261111-3">
</head>
<body class="bg-light p-4">
<nav class="site-breadcrumbs" aria-label="Migas de pan">
    <ol>
        <li><a href="../../index.html">Inicio</a></li>
        <li><a href="../index.html">Parcial 3</a></li>
        <li aria-current="page">Práctica 6: Carrusel</li>
    </ol>
    <a class="site-home-link" href="../index.html">Volver a prácticas</a>
</nav>
<h1>Gestión del Carrusel</h1>
<a href="../5/dashboard.php" class="btn btn-link me-3">Volver al Dashboard</a>
<a href="carrusel_agregar.php" class="btn btn-success">+ Subir Nueva Imagen</a>
<br><br>
<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Vista Previa</th>
            <th>Nombre del Archivo</th>
            <th>Título</th>
            <th>Orden</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><img src="../uploads/carrusel/<?= htmlspecialchars($row['imagen']) ?>" width="100" alt="Vista previa"></td>
                    <td><?= htmlspecialchars($row['imagen']) ?></td>
                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                    <td><?= $row['orden'] ?></td>
                    <td><?= $row['activo'] ? 'Activo' : 'Inactivo' ?></td>
                    <td>
                        <a href="carrusel_eliminar.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta imagen del carrusel?');">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center">No hay imágenes registradas en el carrusel.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
