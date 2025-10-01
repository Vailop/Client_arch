<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$idUsuario = (int)$_SESSION['idusuario'];
$cn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($cn->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$cn->set_charset('utf8mb4');

// Obtener totales por estatus
$sql = "SELECT 
    COUNT(CASE WHEN Estatus = 2 THEN 1 END) as pagados,
    SUM(CASE WHEN Estatus = 2 THEN Monto ELSE 0 END) as monto_pagados,
    COUNT(CASE WHEN Estatus = 1 AND FechaPago >= CURDATE() THEN 1 END) as pendientes,
    SUM(CASE WHEN Estatus = 1 AND FechaPago >= CURDATE() THEN Monto ELSE 0 END) as monto_pendientes,
    COUNT(CASE WHEN Estatus = 1 AND FechaPago < CURDATE() THEN 1 END) as vencidos,
    SUM(CASE WHEN Estatus = 1 AND FechaPago < CURDATE() THEN Monto ELSE 0 END) as monto_vencidos,
    COUNT(*) as total
FROM tbr_pagos
WHERE IdUsuario = ?";

$stmt = $cn->prepare($sql);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();
$cn->close();

echo json_encode([
    'pagados' => (int)$data['pagados'],
    'monto_pagados' => (float)$data['monto_pagados'],
    'pendientes' => (int)$data['pendientes'],
    'monto_pendientes' => (float)$data['monto_pendientes'],
    'vencidos' => (int)$data['vencidos'],
    'monto_vencidos' => (float)$data['monto_vencidos'],
    'total' => (int)$data['total']
], JSON_UNESCAPED_UNICODE);
?>