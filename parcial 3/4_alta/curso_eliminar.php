<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

// Ensure user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../2_login/pages/login.php?error=acceso_denegado');
    exit;
}

$error = '';
$id = $_GET['idcurso'] ?? null;

if ($id && is_numeric($id)) {
    $id = (int)$id;
    $sql = "DELETE FROM cursos WHERE idcurso = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            // Redirect back to dashboard after deletion
            header('Location: ../2_login/pages/dashboard.php');
            exit;
        } else {
            $error = 'Error al eliminar el curso: ' . mysqli_error($conexion);
        }
    } else {
        $error = 'Error al preparar la consulta de eliminación: ' . mysqli_error($conexion);
    }
} else {
    $error = 'ID de curso no válido.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Curso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
            <a href="../2_login/pages/dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
