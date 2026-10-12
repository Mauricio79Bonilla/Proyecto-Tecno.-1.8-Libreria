<?php
header('Content-Type: text/plain; charset=utf-8');
$config = require __DIR__ . '/config.php';
try {
    $conexion = new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['base'] . ';charset=utf8mb4',
        $config['usuario'],
        $config['clave'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Conexion correcta\n";
    $total = $conexion->query('SELECT COUNT(*) FROM `' . $config['tabla'] . '`')->fetchColumn();
    echo "Tabla encontrada. Filas: " . $total;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
