<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../conexion.php';

try {
    $conexion = conectarBD();

    $consultaUsuario = $conexion->prepare(
        'SELECT full_name FROM users WHERE user_id = ? AND is_active = 1 LIMIT 1'
    );
    $userId = (int) $_SESSION['user_id'];
    $consultaUsuario->bind_param('i', $userId);
    $consultaUsuario->execute();
    $consultaUsuario->bind_result($nombreDocente);

    if (!$consultaUsuario->fetch()) {
        $consultaUsuario->close();
        $_SESSION = [];
        session_destroy();
        header('Location: login.php');
        exit;
    }
    $consultaUsuario->close();

    $totalAlumnos = (int) $conexion->query(
        'SELECT COUNT(*) FROM students WHERE is_active = 1'
    )->fetch_row()[0];
    $cursosActivos = (int) $conexion->query(
        'SELECT COUNT(*) FROM courses WHERE is_active = 1'
    )->fetch_row()[0];
    $tareasPendientes = (int) $conexion->query(
        "SELECT COUNT(*) FROM submissions WHERE status = 'pendiente'"
    )->fetch_row()[0];

    $cursos = $conexion->query(
        'SELECT name FROM courses WHERE is_active = 1 ORDER BY name'
    )->fetch_all(MYSQLI_ASSOC);
    $entregas = $conexion->query(
        "SELECT students.full_name AS alumno, courses.name AS curso,
                assignments.title AS tarea, submissions.status AS estado
         FROM submissions
         INNER JOIN students ON students.student_id = submissions.student_id
         INNER JOIN assignments ON assignments.assignment_id = submissions.assignment_id
         INNER JOIN courses ON courses.course_id = assignments.course_id
         WHERE students.is_active = 1
         ORDER BY submissions.updated_at DESC, submissions.submission_id DESC
         LIMIT 8"
    )->fetch_all(MYSQLI_ASSOC);
    $avisos = $conexion->query(
        'SELECT title, message FROM notices
         WHERE is_active = 1
         ORDER BY published_at DESC, notice_id DESC'
    )->fetch_all(MYSQLI_ASSOC);
} catch (RuntimeException $excepcion) {
    http_response_code(503);
    $errorBaseDatos = $excepcion->getMessage();
} catch (mysqli_sql_exception $excepcion) {
    error_log('EduControl: error al cargar el dashboard: ' . $excepcion->getMessage());
    http_response_code(500);
    $errorBaseDatos = 'No fue posible cargar el dashboard. Verifica que database.sql se haya importado correctamente.';
}

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel docente de cursos y entregas de EduControl.">
    <title>Dashboard | EduControl</title>
    <link rel="stylesheet" href="../../navegacion.css">
    <link rel="stylesheet" href="../styles/dashboard_style.css">
</head>
<body>
    <header class="header">
        <h1>Edu<span>Control</span></h1>
        <div class="header-usuario">
            <p>Bienvenido: <?= escapar($nombreDocente ?? 'Docente') ?></p>
            <a class="cerrar-sesion" href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <nav class="nav" aria-label="Navegación del panel">
        <ul>
            <li><a href="#inicio">Inicio</a></li>
            <li><a href="#cursos">Mis cursos</a></li>
            <li><a href="#calificaciones">Calificaciones</a></li>
            <li><a href="#avisos">Avisos</a></li>
        </ul>
    </nav>

    <main class="main" id="inicio">
        <?php if (isset($errorBaseDatos)): ?>
            <p class="mensaje-dashboard" role="alert"><?= escapar($errorBaseDatos) ?></p>
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
                <h2>Mis cursos</h2>
                <?php if ($cursos === []): ?>
                    <p class="estado-vacio">No hay cursos activos.</p>
                <?php else: ?>
                    <ul class="lista-cursos">
                        <?php foreach ($cursos as $curso): ?>
                            <li><?= escapar($curso['name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

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
                                        <td><?= escapar($entrega['alumno']) ?></td>
                                        <td><?= escapar($entrega['curso']) ?></td>
                                        <td><?= escapar($entrega['tarea']) ?></td>
                                        <td>
                                            <span class="estado <?= $entrega['estado'] === 'entregado' ? 'entregado' : 'pendiente' ?>">
                                                <?= $entrega['estado'] === 'entregado' ? 'Entregado' : 'Pendiente' ?>
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
    </main>

    <aside class="aside" id="avisos">
        <h2>Avisos de dirección</h2>
        <?php if (!isset($errorBaseDatos) && $avisos === []): ?>
            <p class="estado-vacio">No hay avisos publicados.</p>
        <?php elseif (!isset($errorBaseDatos)): ?>
            <ul>
                <?php foreach ($avisos as $aviso): ?>
                    <li>
                        <strong><?= escapar($aviso['title']) ?>:</strong>
                        <?= escapar($aviso['message']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </aside>

    <footer class="footer">
        <p>&copy;2026 EduControl System - Estado: En línea</p>
    </footer>
</body>
</html>
