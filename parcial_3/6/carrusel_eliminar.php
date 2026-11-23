<?php
session_start();
// Seguridad: solo administradores
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../5/login.php');
    exit;
}
require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

$id = $_GET['id'] ?? null;
if ($id && is_numeric($id)) {
    $id = (int)$id;
    // Obtener nombre de archivo
    $sqlBuscar = "SELECT imagen FROM carrusel WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sqlBuscar);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $dato = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if ($dato) {
            $rutaArchivo = __DIR__ . '/../uploads/carrusel/' . $dato['imagen'];
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
            $sqlDel = "DELETE FROM carrusel WHERE id = ?";
            $stmtDel = mysqli_prepare($conexion, $sqlDel);
            if ($stmtDel) {
                mysqli_stmt_bind_param($stmtDel, "i", $id);
                mysqli_stmt_execute($stmtDel);
                mysqli_stmt_close($stmtDel);
            }
        }
    }
}
header('Location: carrusel_listado.php');
exit;
?>