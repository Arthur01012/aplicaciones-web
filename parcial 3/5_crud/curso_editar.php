<?php
require_once __DIR__ . '/../config/seguridad.php';
exigirSesion(['admin'], '../2_login/pages/login.php?error=acceso_denegado');
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/cursos.php';

$idEntrada = $_POST['idcurso'] ?? $_GET['idcurso'] ?? null;
$idCurso = is_string($idEntrada) || is_int($idEntrada)
    ? filter_var($idEntrada, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;

if ($idCurso === false) {
    http_response_code(400);
    exit('Identificador de curso no válido.');
}

$datos = ['titulo' => '', 'descripcion' => '', 'precio' => ''];
$destacado = false;
$errores = [];
$errorBaseDatos = '';
$cursoEncontrado = false;

try {
    $conexion = conectarBD();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
            $errores['formulario'] = 'La sesión del formulario venció. Actualiza la página e intenta de nuevo.';
        } else {
            $validacion = validarDatosCurso($_POST);
            $datos = $validacion['datos'];
            $destacado = $validacion['destacado'];
            $errores = $validacion['errores'];
        }

        if ($errores === []) {
            $precio = (float) $datos['precio'];
            $valorDestacado = $destacado ? 1 : 0;
            $consulta = $conexion->prepare(
                'UPDATE cursos
                 SET titulo = ?, descripcion = ?, precio = ?, destacado = ?
                 WHERE idcurso = ?'
            );
            $consulta->bind_param('ssdii', $datos['titulo'], $datos['descripcion'], $precio, $valorDestacado, $idCurso);
            $consulta->execute();
            $cursoEncontrado = $consulta->affected_rows > 0;
            $consulta->close();

            if ($cursoEncontrado) {
                header('Location: dashboard.php', true, 303);
                exit;
            }
        }
    }

    $consulta = $conexion->prepare(
        'SELECT titulo, descripcion, precio, destacado
         FROM cursos
         WHERE idcurso = ?
         LIMIT 1'
    );
    $consulta->bind_param('i', $idCurso);
    $consulta->execute();
    $consulta->bind_result($titulo, $descripcion, $precioCurso, $destacadoCurso);
    $cursoEncontrado = $consulta->fetch();
    $consulta->close();

    if (!$cursoEncontrado) {
        http_response_code(404);
        $errorBaseDatos = 'El curso solicitado no existe.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $errores === []) {
        header('Location: dashboard.php', true, 303);
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'POST' || $errores !== []) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || isset($errores['formulario'])) {
            $datos = [
                'titulo' => (string) $titulo,
                'descripcion' => (string) $descripcion,
                'precio' => number_format((float) $precioCurso, 2, '.', ''),
            ];
            $destacado = (int) $destacadoCurso === 1;
        }
    }
} catch (RuntimeException $excepcion) {
    http_response_code(503);
    $errorBaseDatos = $excepcion->getMessage();
} catch (mysqli_sql_exception $excepcion) {
    error_log('Parcial 3 CRUD: error al editar el curso: ' . $excepcion->getMessage());
    http_response_code(500);
    $errorBaseDatos = 'No fue posible guardar los cambios del curso.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Curso | Parcial 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles/admin.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar navbar-dark admin-navbar">
            <div class="container-fluid px-3 px-md-4">
                <a class="navbar-brand fw-bold fs-6" href="dashboard.php">DevAcademy Admin</a>
                <a class="btn btn-outline-light btn-sm" href="dashboard.php">Volver a cursos</a>
            </div>
        </nav>
    </header>
    <main class="container py-4">
        <section class="card mx-auto shadow-sm" style="max-width: 46rem" aria-labelledby="edit-title">
            <div class="card-body p-4">
                <h1 class="h3 mb-4" id="edit-title">Editar Curso</h1>
                <?php if ($errorBaseDatos !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= escaparHtml($errorBaseDatos) ?></div>
                <?php endif; ?>
                <?php if (isset($errores['formulario'])): ?>
                    <div class="alert alert-danger" role="alert"><?= escaparHtml($errores['formulario']) ?></div>
                <?php endif; ?>
                <?php if ($cursoEncontrado): ?>
                    <form id="curso-form" action="curso_editar.php?idcurso=<?= (int) $idCurso ?>" method="post" novalidate>
                        <input type="hidden" name="idcurso" value="<?= (int) $idCurso ?>">
                        <input type="hidden" name="token_csrf" value="<?= escaparHtml(tokenCsrf()) ?>">
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
                            <button class="btn btn-primary fw-semibold" type="submit">Actualizar Curso</button>
                            <a class="btn btn-outline-secondary" href="dashboard.php">Cancelar</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script>
        document.getElementById('curso-form')?.addEventListener('submit', (evento) => {
            let valido = true;
            for (const [id, mensaje] of Object.entries({
                titulo: 'El título es obligatorio y debe tener entre 3 y 150 caracteres.',
                descripcion: 'La descripción es obligatoria y no debe exceder 5000 caracteres.',
                precio: 'Escribe un precio entre 0 y 99,999,999.99 con hasta dos decimales.'
            })) {
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
                document.querySelector('.is-invalid')?.focus();
            }
        });
    </script>
</body>
</html>
