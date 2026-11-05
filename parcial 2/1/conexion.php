<?php
$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$pass = getenv("DB_PASSWORD");
$db = getenv("DB_NAME");

if ($host === false || $user === false || $pass === false || $db === false) {
    http_response_code(500);
    exit("Configura las variables DB_HOST, DB_USER, DB_PASSWORD y DB_NAME.");
}

try {
    $conexion = new mysqli($host, $user, $pass, $db);
    $conexion->set_charset("utf8mb4");
    echo "Conexión exitosa";
} catch (mysqli_sql_exception $error) {
    http_response_code(500);
    exit("Error de conexión: " . htmlspecialchars($error->getMessage(), ENT_QUOTES, "UTF-8"));
}
?>