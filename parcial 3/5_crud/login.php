<?php
session_start();

// Si ya tiene sesión activa
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/conexion.php';
$conexion = conectarBD();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT idusuario, nombre, password FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            if ($user = mysqli_fetch_assoc($resultado)) {
                if ($password === $user['password']) {
                    $_SESSION['admin_id'] = $user['idusuario'];
                    $_SESSION['admin_nombre'] = $user['nombre'];
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            } else {
                $error = 'El correo no está registrado.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Error interno al preparar la consulta.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - DevAcademy</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .error { color: red; }
    </style>
</head>
<body>
<h1>Iniciar Sesión</h1>
<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="POST" action="">
    <div>
        <label for="email">Correo Electrónico:</label><br>
        <input type="email" name="email" id="email" required>
    </div>
    <br>
    <div>
        <label for="password">Contraseña:</label><br>
        <input type="password" name="password" id="password" required>
    </div>
    <br>
    <button type="submit">Ingresar</button>
</form>
</body>
</html>
