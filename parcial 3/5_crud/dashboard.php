<?php
require_once __DIR__ . '/../config/seguridad.php';
exigirSesion(['admin'], '../2_login/pages/login.php?error=acceso_denegado');
require_once __DIR__ . '/../config/conexion.php';

$cursos = [];
$errorBaseDatos = '';
$mensaje = '';

if (($_GET['error'] ?? '') === 'relaciones') {
    $mensaje = 'No se puede eliminar el curso porque tiene tareas relacionadas.';
} elseif (($_GET['error'] ?? '') === 'no_encontrado') {
    $mensaje = 'El curso solicitado ya no existe.';
}

try {
    $conexion = conectarBD();
    $cursos = $conexion->query(
        'SELECT idcurso, titulo, descripcion, precio, destacado
         FROM cursos
         ORDER BY idcurso DESC'
    )->fetch_all(MYSQLI_ASSOC);
} catch (RuntimeException $excepcion) {
    http_response_code(503);
    $errorBaseDatos = $excepcion->getMessage();
} catch (mysqli_sql_exception $excepcion) {
    error_log('Parcial 3 CRUD: error al consultar cursos: ' . $excepcion->getMessage());
    http_response_code(500);
    $errorBaseDatos = 'No fue posible cargar los cursos. Revisa el esquema de la base de datos.';
}

$nombreAdmin = escaparHtml((string) ($_SESSION['admin_nombre'] ?? 'Administrador'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Administración de cursos de DevAcademy.">
    <title>CRUD de Cursos | Parcial 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles/admin.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar navbar-dark admin-navbar">
            <div class="container-fluid px-3 px-md-4">
                <a class="navbar-brand fw-bold fs-6" href="dashboard.php">DevAcademy Admin</a>
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <span class="navbar-text small text-white">Hola, <?= $nombreAdmin ?></span>
                    <form action="../2_login/pages/logout.php" method="post">
                        <input type="hidden" name="token_csrf" value="<?= escaparHtml(tokenCsrf()) ?>">
                        <button class="btn btn-outline-light btn-sm" type="submit">Cerrar Sesión</button>
                    </form>
                </div>
            </div>
        </nav>
    </header>
    <main class="dashboard-shell">
        <section class="admin-content" aria-labelledby="courses-title">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <h1 class="mb-0" id="courses-title">Gestión de Cursos</h1>
                <a class="btn btn-success btn-sm fw-semibold" href="../4_alta/alta.php?volver=crud">+ Agregar Nuevo Curso</a>
            </div>

            <?php if ($errorBaseDatos !== ''): ?>
                <div class="alert alert-danger" role="alert"><?= escaparHtml($errorBaseDatos) ?></div>
            <?php endif; ?>
            <?php if ($mensaje !== ''): ?>
                <div class="alert alert-warning" role="status"><?= escaparHtml($mensaje) ?></div>
            <?php endif; ?>

            <?php if ($errorBaseDatos === '' && $cursos === []): ?>
                <p class="alert alert-info" role="status">No hay cursos registrados.</p>
            <?php elseif ($cursos !== []): ?>
                <div class="table-responsive course-table bg-white">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Título</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Precio</th>
                                <th scope="col">Destacado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cursos as $curso): ?>
                                <?php
                                $idCurso = (int) $curso['idcurso'];
                                $titulo = (string) $curso['titulo'];
                                ?>
                                <tr>
                                    <td><?= $idCurso ?></td>
                                    <td class="fw-semibold"><?= escaparHtml($titulo) ?></td>
                                    <td><?= escaparHtml(mb_strimwidth((string) $curso['descripcion'], 0, 50, '...')) ?></td>
                                    <td>$<?= number_format((float) $curso['precio'], 2) ?></td>
                                    <td>
                                        <?php if ((int) $curso['destacado'] === 1): ?>
                                            <span class="badge text-bg-warning">Sí</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <a class="btn btn-primary btn-sm" href="curso_editar.php?idcurso=<?= $idCurso ?>">Editar</a>
                                        <form class="d-inline" action="curso_eliminar.php" method="post"
                                            onsubmit="return confirm('¿Eliminar el curso seleccionado?');">
                                            <input type="hidden" name="token_csrf" value="<?= escaparHtml(tokenCsrf()) ?>">
                                            <input type="hidden" name="idcurso" value="<?= $idCurso ?>">
                                            <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
