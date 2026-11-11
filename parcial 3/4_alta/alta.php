<?php
require_once __DIR__ . '/../config/seguridad.php';
require_once __DIR__ . '/../config/navegacion.php';
exigirSesion(['admin'], '../2_login/pages/login.php?siguiente=alta&error=acceso_denegado');
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/cursos.php';

$datos = [
    'titulo' => '',
    'descripcion' => '',
    'precio' => '',
];
$destacado = false;
$errores = [];
$errorBaseDatos = '';
$destino = ($_POST['volver'] ?? $_GET['volver'] ?? '') === 'crud'
    ? '../5_crud/dashboard.php'
    : '../3_logout/pages/dashboard.php';
$conexion = null;

try {
    $conexion = conectarBD();
} catch (RuntimeException $excepcion) {
    http_response_code(503);
    $errorBaseDatos = $excepcion->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorBaseDatos === '') {
    if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
        $errores['formulario'] = 'La sesión del formulario venció. Actualiza la página e intenta de nuevo.';
    } else {
        $validacion = validarDatosCurso($_POST);
        $datos = $validacion['datos'];
        $destacado = $validacion['destacado'];
        $errores = $validacion['errores'];
    }

    if ($errores === []) {
        try {
            $precio = (float) $datos['precio'];
            $valorDestacado = $destacado ? 1 : 0;
            $consulta = $conexion->prepare(
                'INSERT INTO cursos (titulo, descripcion, precio, destacado)
                 VALUES (?, ?, ?, ?)'
            );
            $consulta->bind_param('ssdi', $datos['titulo'], $datos['descripcion'], $precio, $valorDestacado);
            $consulta->execute();
            $consulta->close();
            header('Location: ' . $destino, true, 303);
            exit;
        } catch (mysqli_sql_exception $excepcion) {
            error_log('Parcial 3: no fue posible registrar el curso: ' . $excepcion->getMessage());
            http_response_code(500);
            $errorBaseDatos = 'No fue posible guardar el curso. Revisa el esquema de la base de datos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Formulario de alta de cursos.">
    <title>Agregar Curso | Parcial 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../navegacion.css" rel="stylesheet">
    <link href="../styles/admin.css" rel="stylesheet">
</head>
<body>
    <?php renderizarMigasPan([
        ['texto' => 'Inicio', 'url' => '../../index.html', 'destino_sesion' => 'inicio'],
        ['texto' => 'Parcial 3', 'url' => '../index.html', 'destino_sesion' => 'parcial3'],
        ['texto' => 'Práctica 4: Alta', 'url' => 'index.php'],
        ['texto' => 'Registrar curso'],
    ], '../config/logout.php'); ?>
    <header>
        <nav class="navbar navbar-dark admin-navbar">
            <div class="container-fluid px-3 px-md-4">
                <a class="navbar-brand fw-bold fs-6" href="../3_logout/pages/dashboard.php">DevAcademy Admin</a>
                <a class="btn btn-outline-light btn-sm" href="<?= escaparHtml($destino) ?>">Volver al panel</a>
            </div>
        </nav>
    </header>
    <main class="container py-4">
        <section class="card mx-auto shadow-sm" style="max-width: 46rem" aria-labelledby="form-title">
            <div class="card-body p-4">
                <h1 class="h3 mb-4" id="form-title">Agregar Nuevo Curso</h1>
                <?php if ($errorBaseDatos !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= escaparHtml($errorBaseDatos) ?></div>
                <?php endif; ?>
                <?php if (isset($errores['formulario'])): ?>
                    <div class="alert alert-danger" role="alert"><?= escaparHtml($errores['formulario']) ?></div>
                <?php endif; ?>

                <form id="curso-form" action="alta.php?volver=<?= $destino === '../5_crud/dashboard.php' ? 'crud' : 'dashboard' ?>"
                    method="post" novalidate>
                    <input type="hidden" name="token_csrf" value="<?= escaparHtml(tokenCsrf()) ?>">
                    <input type="hidden" name="volver" value="<?= $destino === '../5_crud/dashboard.php' ? 'crud' : 'dashboard' ?>">
                    <div class="mb-3">
                        <label class="form-label" for="titulo">Título del Curso</label>
                        <input class="form-control<?= isset($errores['titulo']) ? ' is-invalid' : '' ?>"
                            id="titulo" name="titulo" type="text" maxlength="150" required
                            value="<?= escaparHtml($datos['titulo']) ?>">
                        <div class="invalid-feedback"><?= escaparHtml($errores['titulo'] ?? '') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="descripcion">Descripción</label>
                        <textarea class="form-control<?= isset($errores['descripcion']) ? ' is-invalid' : '' ?>"
                            id="descripcion" name="descripcion" rows="4" maxlength="5000" required><?= escaparHtml($datos['descripcion']) ?></textarea>
                        <div class="invalid-feedback"><?= escaparHtml($errores['descripcion'] ?? '') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="precio">Precio ($)</label>
                        <input class="form-control<?= isset($errores['precio']) ? ' is-invalid' : '' ?>"
                            id="precio" name="precio" type="number" min="0" max="99999999.99" step="0.01" required
                            value="<?= escaparHtml($datos['precio']) ?>">
                        <div class="invalid-feedback"><?= escaparHtml($errores['precio'] ?? '') ?></div>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input<?= isset($errores['destacado']) ? ' is-invalid' : '' ?>"
                            id="destacado" name="destacado" type="checkbox" value="1"<?= $destacado ? ' checked' : '' ?>>
                        <label class="form-check-label" for="destacado">Marcar como Curso Destacado</label>
                        <?php if (isset($errores['destacado'])): ?>
                            <div class="invalid-feedback d-block"><?= escaparHtml($errores['destacado']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary fw-semibold" type="submit">Guardar Curso</button>
                        <a class="btn btn-outline-secondary" href="<?= escaparHtml($destino) ?>">Cancelar</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <script>
        const formularioCurso = document.getElementById('curso-form');
        const mensajes = {
            titulo: 'El título es obligatorio y debe tener entre 3 y 150 caracteres.',
            descripcion: 'La descripción es obligatoria y no debe exceder 5000 caracteres.',
            precio: 'Escribe un precio entre 0 y 99,999,999.99 con hasta dos decimales.'
        };

        formularioCurso.addEventListener('submit', (evento) => {
            let valido = true;
            for (const [id, mensaje] of Object.entries(mensajes)) {
                const campo = document.getElementById(id);
                const valor = campo.value.trim();
                let campoValido = valor !== '';
                if (id === 'titulo') campoValido &&= valor.length >= 3 && valor.length <= 150;
                if (id === 'descripcion') campoValido &&= valor.length <= 5000;
                if (id === 'precio') campoValido &&= /^\d+(?:\.\d{1,2})?$/.test(valor) && Number(valor) <= 99999999.99;
                campo.classList.toggle('is-invalid', !campoValido);
                campo.nextElementSibling.textContent = campoValido ? '' : mensaje;
                valido &&= campoValido;
            }
            if (!valido) {
                evento.preventDefault();
                formularioCurso.querySelector('.is-invalid')?.focus();
            }
        });
    </script>
</body>
</html>
