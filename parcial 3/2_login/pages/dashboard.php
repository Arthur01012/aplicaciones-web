<?php
session_start();
// Protección del dashboard: solo admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php?error=acceso_denegado');
    exit;
}
require_once __DIR__ . '/../../config/conexion.php';
$conexion = conectarBD();
// Consulta de lectura de cursos
$sql = "SELECT * FROM cursos ORDER BY idcurso DESC";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../navegacion.css?v=20261111-3">
</head>
<body class="bg-light">
<nav class="site-breadcrumbs" aria-label="Migas de pan">
    <ol>
        <li><a href="../../../index.html">Inicio</a></li>
        <li><a href="../../index.html">Parcial 3</a></li>
        <li><a href="login.php">Práctica 2</a></li>
        <li aria-current="page">Dashboard</li>
    </ol>
    <a class="site-home-link" href="../../index.html">Volver a prácticas</a>
</nav>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand" href="#">DevAcademy Admin</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav ms-auto">
      <li class="nav-item">
        <span class="nav-link text-white">Hola, <?= htmlspecialchars($_SESSION['admin_nombre'] ?? 'Administrador') ?></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
      </li>
    </ul>
  </div>
</nav>
<div class="container my-4">
    <h2 class="mb-4">Gestión de Cursos</h2>
    <a href="../../4_alta/alta.php" class="btn btn-success mb-3">Agregar Nuevo Curso</a>
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Destacado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($resultado) > 0): ?>
            <?php while ($curso = mysqli_fetch_assoc($resultado)): ?>
                <tr>
                    <td><?= $curso['idcurso'] ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($curso['titulo']) ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($curso['descripcion'], 0, 50, '...')) ?></td>
                    <td>$<?= number_format($curso['precio'], 2) ?></td>
                    <td>
                        <?php if ($curso['destacado']): ?>
                            <span class="badge bg-warning text-dark">Sí</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="../../5_crud/editar.php?idcurso=<?= $curso['idcurso'] ?>" class="btn btn-primary btn-sm me-1">Editar</a>
                        <a href="../../4_alta/curso_eliminar.php?idcurso=<?= $curso['idcurso'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este curso?');">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No hay cursos registrados.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
