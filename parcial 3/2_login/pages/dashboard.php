<?php
require_once __DIR__ . '/../../config/seguridad.php';
require_once __DIR__ . '/../../config/navegacion.php';
exigirSesion(['admin'], 'login.php?error=acceso_denegado');

$nombreAdmin = htmlspecialchars(
    (string) ($_SESSION['admin_nombre'] ?? 'Administrador'),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Gestión de cursos de DevAcademy.">
    <title>Gestión de Cursos | DevAcademy Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../navegacion.css" rel="stylesheet">
    <link href="../../styles/admin.css" rel="stylesheet">
</head>
<body>
    <?php renderizarMigasPan([
        ['texto' => 'Inicio', 'url' => '../../../index.html', 'destino_sesion' => 'inicio'],
        ['texto' => 'Parcial 3', 'url' => '../../index.html', 'destino_sesion' => 'parcial3'],
        ['texto' => 'Práctica 2', 'url' => '../index.php'],
        ['texto' => 'Panel'],
    ], '../../config/logout.php'); ?>
    <header>
        <nav class="navbar navbar-dark admin-navbar">
            <div class="container-fluid px-3 px-md-4">
                <a class="navbar-brand fw-bold fs-6" href="dashboard.php">DevAcademy Admin</a>
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <span class="navbar-text small text-white">Hola, <?= $nombreAdmin ?></span>
                    <form action="logout.php" method="post">
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
                <a class="btn btn-primary btn-sm fw-semibold" href="../../5_crud/dashboard.php">
                    Abrir altas, bajas y cambios
                </a>
            </div>

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
                        <tr>
                            <td>1</td>
                            <td class="fw-semibold">Desarrollo Web con PHP</td>
                            <td>Aprende PHP desde cero y...</td>
                            <td>$199.99</td>
                            <td><span class="badge text-bg-warning">Sí</span></td>
                            <td class="text-nowrap">
                                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#course-modal">Editar</button>
                                <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#delete-modal">Eliminar</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="fw-semibold">Bootstrap 5 Avanzado</td>
                            <td>Domina los layouts de forma rápido con...</td>
                            <td>$49.99</td>
                            <td><span class="badge text-bg-secondary">No</span></td>
                            <td class="text-nowrap">
                                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#course-modal">Editar</button>
                                <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#delete-modal">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modal fade" id="course-modal" tabindex="-1" aria-labelledby="course-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="course-modal-title">Gestión de cursos</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 text-secondary">Esta pantalla es la vista de la práctica 2. Para registrar, editar o eliminar cursos en la base de datos, abre la gestión CRUD.</p>
                    <a class="btn btn-primary btn-sm" href="../../5_crud/dashboard.php">Ir a gestión CRUD</a>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" type="button" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete-modal" tabindex="-1" aria-labelledby="delete-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="delete-modal-title">Gestión de cursos</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 text-secondary">Las altas, bajas y cambios reales están disponibles en la práctica CRUD.</p>
                    <a class="btn btn-primary btn-sm" href="../../5_crud/dashboard.php">Ir a gestión CRUD</a>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" type="button" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
