<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function conectarBD(): mysqli
{
    $host = getenv('DB_HOST') ?: 'medicdate.innego.net';
    $usuario = getenv('DB_USER') ?: 'innegone_u_devacademic';
    $contrasena = getenv('DB_PASSWORD');
    $baseDatos = getenv('DB_NAME') ?: 'innegone_bd_devacademic';

    if ($contrasena === false || $contrasena === '') {
        throw new RuntimeException('Falta configurar la variable DB_PASSWORD.');
    }

    try {
        $conexion = new mysqli($host, $usuario, $contrasena, $baseDatos);
        $conexion->set_charset('utf8mb4');

        return $conexion;
    } catch (mysqli_sql_exception $excepcion) {
        error_log('Parcial 3: error de conexión MySQL: ' . $excepcion->getMessage());

        throw new RuntimeException(
            'No fue posible conectar con la base de datos. Verifica la configuración de acceso.',
            0,
            $excepcion
        );
    }
}
