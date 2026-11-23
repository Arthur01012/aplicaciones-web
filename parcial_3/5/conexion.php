<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "devacademy";

$conexion = mysqli_connect(
    $host,
    $user,
    $pass,
    $db
);

if (!$conexion) {
    die("Error de conexion: " . mysqli_connect_error());
}
?>