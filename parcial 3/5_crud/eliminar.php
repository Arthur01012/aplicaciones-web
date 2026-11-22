<?php

session_start();

if (!isset($_SESSION['admin_id'])) {

    header('Location: login.php');

    exit;
}

require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

$idcurso = $_GET['idcurso'] ?? null;

if ($idcurso && is_numeric($idcurso)) {

    $sql = "DELETE FROM cursos
     WHERE idcurso = ?";

    $stmt = mysqli_prepare($conexion, $sql);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "i", $idcurso);

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }
}

header('Location: dashboard.php');

exit;

?>
