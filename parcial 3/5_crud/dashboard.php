<?php
session_start();
// CANDADO DE SEGURIDAD
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

// Obtener listado de cursos
$sql = "SELECT * FROM cursos ORDER BY idcurso DESC";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
<header>
    <span>Bienvenido, <?= htmlspecialchars($_SESSION['admin_nombre']) ?></span> |
    <a href="logout.php">Cerrar Sesión</a>
</header>
<hr>
<main>
    <h2>Gestión de Cursos</h2>
    <a href="alta.php">+ Agregar Nuevo Curso</a> |
    <a href="../6_upload_index/carrusel_listado.php">Gestionar Carrusel</a>
    <br><br>
    <table border="1" cellpadding="5">
        <thead>
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
            <?php if (mysqli_num_rows($resultados ?? $resultado) > 0): ?>
                <?php while ($curso = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?= $curso['idcurso'] ?></td>
                        <td><?= htmlspecialchars($curso['titulo']) ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth($curso['descripcion'],0,40,'...')) ?></td>
                        <td>$<?= number_format($curso['precio'],2) ?></td>
                        <td><?= $curso['destacado'] ? 'Sí' : 'No' ?></td>
                        <td>
                            <a href="editar.php?idcurso=<?= $curso['idcurso'] ?>">Editar</a> |
                            <a href="eliminar.php?idcurso=<?= $curso['idcurso'] ?>" onclick="return confirm('¿Eliminar curso?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No hay cursos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>
