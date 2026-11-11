<?php
require_once __DIR__ . '/../../config/seguridad.php';
require_once __DIR__ . '/../../config/navegacion.php';
exigirSesion(['admin', 'docente'], 'login.php');
require_once __DIR__ . '/../conexion.php';

$esAdministrador = $_SESSION['rol'] === 'admin';
$nombreUsuario = (string) ($_SESSION[$esAdministrador ? 'admin_nombre' : 'usuario_nombre'] ?? 'Usuario');
$cursos = [];
$entregas = [];
$avisos = [];
$totalAlumnos = 0;
$cursosActivos = 0;
$tareasPendientes = 0;
$errorBaseDatos = '';

try {
    $conexion = conectarBD();
    $totalAlumnos = (int) $conexion->query(
        'SELECT COUNT(*) FROM estudiantes WHERE activo = 1'
    )->fetch_row()[0];
    $cursosActivos = (int) $conexion->query(
        'SELECT COUNT(*) FROM cursos WHERE activo = 1'
    )->fetch_row()[0];
    $tareasPendientes = (int) $conexion->query(
        "SELECT COUNT(*) FROM entregas WHERE estado = 'pendiente'"
    )->fetch_row()[0];

    $cursos = $conexion->query(
        'SELECT idcurso, codigo, titulo, precio, destacado
         FROM cursos WHERE activo = 1 ORDER BY titulo'
    )->fetch_all(MYSQLI_ASSOC);

    if (!$esAdministrador) {
        $entregas = $conexion->query(
            "SELECT estudiantes.nombre AS alumno, cursos.titulo AS curso,
                    tareas.titulo AS tarea, entregas.estado
             FROM entregas
             INNER JOIN estudiantes ON estudiantes.id = entregas.idestudiante
             INNER JOIN tareas ON tareas.idtarea = entregas.idtarea
             INNER JOIN cursos ON cursos.idcurso = tareas.idcurso
             WHERE estudiantes.activo = 1
             ORDER BY entregas.actualizado_en DESC, entregas.identrega DESC
             LIMIT 8"
        )->fetch_all(MYSQLI_ASSOC);
    }

    $avisos = $conexion->query(
        'SELECT titulo, mensaje FROM avisos
         WHERE activo = 1
         ORDER BY publicado_en DESC, idaviso DESC'
    )->fetch_all(MYSQLI_ASSOC);
} catch (RuntimeException $excepcion) {
    http_response_code(503);
    $errorBaseDatos = $excepcion->getMessage();
} catch (mysqli_sql_exception $excepcion) {
    error_log('Parcial 3: error al cargar EduControl: ' . $excepcion->getMessage());
    http_response_code(500);
    $errorBaseDatos = 'No fue posible cargar el panel. Verifica que la migración de la base de datos esté aplicada.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Panel académico de EduControl.">
    <title>Dashboard | EduControl</title>
    <link rel="stylesheet" href="../../navegacion.css">
    <link rel="stylesheet" href="../styles/dashboard_style.css">
</head>
<body>
    <?php renderizarMigasPan([
        ['texto' => 'Inicio', 'url' => '../../../index.html', 'destino_sesion' => 'inicio'],
        ['texto' => 'Parcial 3', 'url' => '../../index.html', 'destino_sesion' => 'parcial3'],
        ['texto' => 'Práctica 6', 'url' => '../index.php'],
        ['texto' => 'EduControl'],
    ], '../../config/logout.php'); ?>
    <header class="header">
        <h1>Edu<span>Control</span></h1>
        <div class="header-usuario">
            <p>Bienvenido: <?= escaparHtml($nombreUsuario) ?></p>
            <form action="logout.php" method="post">
                <input type="hidden" name="token_csrf" value="<?= escaparHtml(tokenCsrf()) ?>">
                <button class="cerrar-sesion" type="submit">Cerrar sesión</button>
            </form>
        </div>
    </header>

    <nav class="nav" aria-label="Navegación del panel">
        <ul>
            <li><a href="#inicio">Inicio</a></li>
            <li><a href="#cursos"><?= $esAdministrador ? 'Administrar cursos' : 'Cursos activos' ?></a></li>
            <?php if (!$esAdministrador): ?>
                <li><a href="#calificaciones">Entregas</a></li>
            <?php endif; ?>
            <li><a href="#avisos">Avisos</a></li>
            <li>
                <form action="../../config/logout.php" method="post">
                    <input type="hidden" name="token_csrf" value="<?= escaparHtml(tokenCsrf()) ?>">
                    <input type="hidden" name="destino" value="parcial3">
                    <button class="nav-link nav-logout" type="submit">Prácticas</button>
                </form>
            </li>
        </ul>
    </nav>

    <main class="main" id="inicio">
        <?php if ($errorBaseDatos !== ''): ?>
            <p class="mensaje-dashboard" role="alert"><?= escaparHtml($errorBaseDatos) ?></p>
        <?php else: ?>
            <section class="contenedor-cards" aria-label="Resumen académico">
                <article class="card">
                    <h2>Total de alumnos</h2>
                    <span><?= $totalAlumnos ?></span>
                </article>
                <article class="card">
                    <h2>Cursos activos</h2>
                    <span><?= $cursosActivos ?></span>
                </article>
                <article class="card">
                    <h2>Tareas pendientes</h2>
                    <span><?= $tareasPendientes ?></span>
                </article>
            </section>

            <section class="main-card cursos-card" id="cursos">
                <h2><?= $esAdministrador ? 'Cursos disponibles' : 'Cursos activos' ?></h2>
                <?php if ($cursos === []): ?>
                    <p class="estado-vacio">No hay cursos activos.</p>
                <?php else: ?>
                    <ul class="lista-cursos">
                        <?php foreach ($cursos as $curso): ?>
                            <li>
                                <?= escaparHtml($curso['titulo']) ?>
                                <?php if ($esAdministrador): ?>
                                    <small> · <?= escaparHtml((string) $curso['codigo']) ?> · $<?= number_format((float) $curso['precio'], 2) ?></small>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($esAdministrador): ?>
                    <p class="estado-vacio"><a href="../../5_crud/dashboard.php">Abrir administración CRUD de cursos</a></p>
                <?php endif; ?>
            </section>

            <?php if (!$esAdministrador): ?>
                <section class="main-card entregas-card" id="calificaciones">
                    <h2>Entregas recientes</h2>
                    <?php if ($entregas === []): ?>
                        <p class="estado-vacio">Todavía no hay entregas registradas.</p>
                    <?php else: ?>
                        <div class="tabla-contenedor">
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col">Alumno</th>
                                        <th scope="col">Curso</th>
                                        <th scope="col">Tarea</th>
                                        <th scope="col">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($entregas as $entrega): ?>
                                        <tr>
                                            <td><?= escaparHtml($entrega['alumno']) ?></td>
                                            <td><?= escaparHtml($entrega['curso']) ?></td>
                                            <td><?= escaparHtml($entrega['tarea']) ?></td>
                                            <td>
                                                <span class="estado <?= $entrega['estado'] === 'entregada' ? 'entregado' : 'pendiente' ?>">
                                                    <?= $entrega['estado'] === 'entregada' ? 'Entregada' : 'Pendiente' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <aside class="aside" id="avisos">
        <h2>Avisos de dirección</h2>
        <?php if ($errorBaseDatos === ''): ?>
            <?php if ($avisos === []): ?>
                <p class="estado-vacio">No hay avisos publicados.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($avisos as $aviso): ?>
                        <li><strong><?= escaparHtml($aviso['titulo']) ?>:</strong> <?= escaparHtml($aviso['mensaje']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </aside>

    <footer class="footer">
        <p>&copy;2026 EduControl System - Parcial 3</p>
    </footer>
</body>
</html>
