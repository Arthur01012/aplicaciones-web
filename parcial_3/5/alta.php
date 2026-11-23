<?php

session_start();

if (!isset($_SESSION['admin_id'])) {

    header('Location: login.php');

    exit;
}

require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');

    $descripcion = trim($_POST['descripcion'] ?? '');

    $precio = trim($_POST['precio'] ?? '');

    $destacado = isset($_POST['destacado']) ? 1 : 0;

    if (empty($titulo) || empty($descripcion) || $precio === '') {

        $error = 'Todos los campos son obligatorios.';

    } elseif (!is_numeric($precio) || (float)$precio < 0) {

        $error = 'El precio debe ser un número válido mayor o igual a 0.';

    } else {

        $precio_float = (float)$precio;

        $sql = "
        INSERT INTO cursos
        (
            titulo,
            descripcion,
            precio,
            destacado
        )
        VALUES (?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($conexion, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssdi",
                $titulo,
                $descripcion,
                $precio_float,
                $destacado
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header('Location: dashboard.php');

                exit;

            } else {

                $error = 'Error al registrar el curso.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Curso</title>
<link rel="stylesheet" href="../navegacion.css?v=20261111-3">
</head>
<body>
<nav class="site-breadcrumbs" aria-label="Migas de pan">
    <ol>
        <li><a href="../../index.html">Inicio</a></li>
        <li><a href="../index.html">Parcial 3</a></li>
        <li><a href="dashboard.php">Práctica 5: CRUD</a></li>
        <li aria-current="page">Agregar curso</li>
    </ol>
    <a class="site-home-link" href="../index.html">Volver a prácticas</a>
</nav>

<h1>Agregar Nuevo Curso</h1>

<a href="dashboard.php">← Volver al Dashboard</a>

<br><br>

<?php if ($error): ?>
<p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="">

<label for="titulo">Título del Curso:</label>
<input type="text" name="titulo" id="titulo" required>

<label for="descripcion">Descripción:</label>
<textarea name="descripcion" id="descripcion" rows="4" cols="40" required></textarea>

<label for="precio">Precio ($):</label>
<input type="number" step="0.01" min="0" name="precio" id="precio" required>

<input type="checkbox" name="destacado" id="destacado" value="1">
<label for="destacado">Marcar como Curso Destacado</label>

<button type="submit">Guardar Curso</button>

</form>

</body>
</html>
