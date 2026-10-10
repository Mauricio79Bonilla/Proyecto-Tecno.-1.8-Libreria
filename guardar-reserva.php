<?php
header('Content-Type: application/json; charset=utf-8');

function responder($ok, $dato = null)
{
    echo json_encode(['success' => $ok, 'data' => $dato], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido');
}

$campos = ['producto', 'nombre', 'apellido', 'correo', 'numero', 'horaUso', 'horaEntrega'];
$valores = [];
foreach ($campos as $campo) {
    $valor = isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
    if ($valor === '') {
        responder(false, 'Faltan datos: ' . $campo);
    }
    $valores[$campo] = $valor;
}

if (!filter_var($valores['correo'], FILTER_VALIDATE_EMAIL)) {
    responder(false, 'Correo no válido');
}

$config = require __DIR__ . '/config.php';

try {
    $conexion = new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['base'] . ';charset=utf8mb4',
        $config['usuario'],
        $config['clave'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sentencia = $conexion->prepare(
        'INSERT INTO `' . $config['tabla'] . '` (producto, nombre, apellido, correo, numero, hora_uso, hora_entrega, fecha)
         VALUES (:producto, :nombre, :apellido, :correo, :numero, :hora_uso, :hora_entrega, NOW())'
    );
    $sentencia->execute([
        ':producto' => $valores['producto'],
        ':nombre' => $valores['nombre'],
        ':apellido' => $valores['apellido'],
        ':correo' => $valores['correo'],
        ':numero' => $valores['numero'],
        ':hora_uso' => $valores['horaUso'],
        ':hora_entrega' => $valores['horaEntrega'],
    ]);

    responder(true);
} catch (PDOException $e) {
    responder(false, 'No se pudo conectar o guardar en la base de datos');
}
