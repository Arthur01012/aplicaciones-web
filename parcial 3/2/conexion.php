<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function conectarBD(): mysqli
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $usuario = getenv('DB_USER') ?: 'root';
    $contrasena = getenv('DB_PASSWORD') ?: '2007';
    $baseDatos = getenv('DB_NAME') ?: 'educontrol';

    try {
        $conexion = new mysqli($host, $usuario, $contrasena, $baseDatos);
        $conexion->set_charset('utf8mb4');

        return $conexion;
    } catch (mysqli_sql_exception $excepcion) {
        error_log('EduControl: no fue posible conectar con la base de datos: ' . $excepcion->getMessage());

        throw new RuntimeException(
            'No se pudo conectar con la base de datos local. Importa database.sql y verifica la configuración de MySQL.',
            0,
            $excepcion
        );
    }
}
