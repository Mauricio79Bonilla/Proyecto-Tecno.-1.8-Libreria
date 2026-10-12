<?php
header('Content-Type: text/html; charset=utf-8');

$config = require __DIR__ . '/config.php';
$mensaje = '';

try {
    $conexion = new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['base'] . ';charset=utf8mb4',
        $config['usuario'],
        $config['clave'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $total = (int) $conexion->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();

    if ($total > 0) {
        $mensaje = 'Ya existe un usuario. Borra este archivo del servidor.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            $mensaje = 'Escribe un nombre, un correo válido y una contraseña de al menos 6 caracteres.';
        } else {
            $sentencia = $conexion->prepare('INSERT INTO usuarios (nombre, correo, password_hash) VALUES (:nombre, :correo, :hash)');
            $sentencia->execute([':nombre' => $nombre, ':correo' => $correo, ':hash' => password_hash($password, PASSWORD_DEFAULT)]);
            $mensaje = 'Usuario creado. Borra ahora este archivo del servidor.';
            $total = 1;
        }
    }
} catch (PDOException $e) {
    $mensaje = 'No se pudo conectar o falta la tabla usuarios.';
    $total = 1;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear usuario</title>
</head>
<body>
    <h1>Crear usuario</h1>
    <?php if ($mensaje !== '') { echo '<p>' . htmlspecialchars($mensaje) . '</p>'; } ?>
    <?php if ($total === 0) { ?>
    <form method="post">
        <p><label>Nombre<br><input type="text" name="nombre" required></label></p>
        <p><label>Correo<br><input type="email" name="correo" required></label></p>
        <p><label>Contraseña<br><input type="password" name="password" required></label></p>
        <button type="submit">Crear</button>
    </form>
    <?php } ?>
</body>
</html>
