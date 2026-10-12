<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => true, 'data' => $_SESSION['usuario_nombre']], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'data' => null]);
}
