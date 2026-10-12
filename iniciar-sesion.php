<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function responder($ok, $dato = null)
{
    echo json_encode(['success' => $ok, 'data' => $dato], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido');
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($nombre === '' || $correo === '' || $password === '') {
    responder(false, 'Escribe tu nombre, correo y contraseña');
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    responder(false, 'Escribe un correo válido');
}

$config = require __DIR__ . '/config.php';

try {
    $conexion = new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['base'] . ';charset=utf8mb4',
        $config['usuario'],
        $config['clave'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sentencia = $conexion->prepare('SELECT id, nombre, correo, password_hash FROM usuarios WHERE correo = :correo LIMIT 1');
    $sentencia->execute([':correo' => $correo]);
    $usuario = $sentencia->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $nombreCorrecto = mb_strtolower($usuario['nombre'], 'UTF-8') === mb_strtolower($nombre, 'UTF-8');

        if (!$nombreCorrecto || !password_verify($password, $usuario['password_hash'])) {
            responder(false, 'Nombre, correo o contraseña incorrectos');
        }
    } else {
        if (strlen($password) < 6) {
            responder(false, 'La contraseña debe tener al menos 6 caracteres');
        }

        $insertar = $conexion->prepare('INSERT INTO usuarios (nombre, correo, password_hash) VALUES (:nombre, :correo, :hash)');
        $insertar->execute([
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $usuario = [
            'id' => $conexion->lastInsertId(),
            'nombre' => $nombre,
            'correo' => $correo,
        ];
    }

    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_correo'] = $usuario['correo'];

    responder(true);
} catch (PDOException $e) {
    responder(false, 'No se pudo conectar con la base de datos');
}
