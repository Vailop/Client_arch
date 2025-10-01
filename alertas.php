<?php
require 'config.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["idusuario"])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$idUsuario = (int)$_SESSION["idusuario"];
$conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit();
}

$conexion->set_charset('utf8mb4');

$alertas = [];

// 1. PAGOS VENCIDOS (CRÍTICO) - Agrupados por desarrollo
$sql = "SELECT 
    MIN(P.IdPago) as IdPago,
    P.FechaPago,
    SUM(P.Monto) as MontoTotal,
    GROUP_CONCAT(DISTINCT P.Dpto ORDER BY P.Dpto SEPARATOR ', ') as Dptos,
    D.Nombre_Desarrollo,
    DATEDIFF(CURDATE(), P.FechaPago) AS dias_vencido,
    COUNT(DISTINCT P.Dpto) as num_dptos
FROM tbr_pagos P
INNER JOIN tbp_desarrollos D ON P.IdDesarrollo = D.IdDesarrollo
WHERE P.IdUsuario = ?
    AND P.Estatus = 1
    AND P.FechaPago < CURDATE()
GROUP BY P.IdDesarrollo, P.FechaPago, D.Nombre_Desarrollo
ORDER BY P.FechaPago ASC
LIMIT 5";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $dptos = $row['num_dptos'] > 1 
        ? "Dptos " . $row['Dptos'] 
        : "Dpto " . $row['Dptos'];
    
    $alertas[] = [
        'tipo' => 'vencido',
        'prioridad' => 'critica',
        'icono' => '🔴',
        'titulo' => 'Pago Vencido',
        'mensaje' => sprintf(
            'Pago de $%s vencido hace %d días - %s %s',
            number_format($row['MontoTotal'], 0, '.', ','),
            $row['dias_vencido'],
            $row['Nombre_Desarrollo'],
            $dptos
        ),
        'fecha' => $row['FechaPago'],
        'id_pago' => $row['IdPago']
    ];
}
$stmt->close();

// 2. PAGOS PRÓXIMOS (7 días) - Agrupados por desarrollo
$sql = "SELECT 
    MIN(P.IdPago) as IdPago,
    P.FechaPago,
    SUM(P.Monto) as MontoTotal,
    GROUP_CONCAT(DISTINCT P.Dpto ORDER BY P.Dpto SEPARATOR ', ') as Dptos,
    D.Nombre_Desarrollo,
    DATEDIFF(P.FechaPago, CURDATE()) AS dias_restantes,
    COUNT(DISTINCT P.Dpto) as num_dptos
FROM tbr_pagos P
INNER JOIN tbp_desarrollos D ON P.IdDesarrollo = D.IdDesarrollo
WHERE P.IdUsuario = ?
    AND P.Estatus = 1
    AND P.FechaPago BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
GROUP BY P.IdDesarrollo, P.FechaPago, D.Nombre_Desarrollo
ORDER BY P.FechaPago ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $dptos = $row['num_dptos'] > 1 
        ? "Dptos " . $row['Dptos'] 
        : "Dpto " . $row['Dptos'];
    
    $alertas[] = [
        'tipo' => 'proximo',
        'prioridad' => 'alta',
        'icono' => '🟡',
        'titulo' => 'Pago Próximo',
        'mensaje' => sprintf(
            'Pago de $%s en %d días - %s %s',
            number_format($row['MontoTotal'], 0, '.', ','),
            $row['dias_restantes'],
            $row['Nombre_Desarrollo'],
            $dptos
        ),
        'fecha' => $row['FechaPago'],
        'id_pago' => $row['IdPago']
    ];
}
$stmt->close();

// 3. PLUSVALÍA DESTACADA (incremento > 5%)
$sqlPlusvalia = "SELECT 
    D.Nombre_Desarrollo,
    P.Precio_Compraventa,
    P.Precio_Actual,
    ((P.Precio_Actual - P.Precio_Compraventa) / P.Precio_Compraventa * 100) AS porcentaje_ganancia
FROM tbr_pagos P
INNER JOIN tbp_desarrollos D ON P.IdDesarrollo = D.IdDesarrollo
WHERE P.IdUsuario = ?
    AND P.Precio_Compraventa > 0
    AND P.Precio_Actual > 0
    AND ((P.Precio_Actual - P.Precio_Compraventa) / P.Precio_Compraventa * 100) > 5
GROUP BY D.IdDesarrollo
ORDER BY porcentaje_ganancia DESC
LIMIT 3";

$stmt = $conexion->prepare($sqlPlusvalia);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $alertas[] = [
        'tipo' => 'plusvalia',
        'prioridad' => 'info',
        'icono' => '🟢',
        'titulo' => 'Plusvalía Destacada',
        'mensaje' => sprintf(
            '%s ha incrementado %.2f%% su valor',
            $row['Nombre_Desarrollo'],
            $row['porcentaje_ganancia']
        ),
        'porcentaje' => round($row['porcentaje_ganancia'], 2)
    ];
}
$stmt->close();

// 4. AVANCE DE OBRA - Bajos y completados
$sqlObra = "SELECT 
    D.Nombre_Desarrollo,
    A.Categoria,
    A.ValorActual,
    A.ValorObjetivo,
    ROUND((A.ValorActual / A.ValorObjetivo * 100), 2) AS porcentaje_avance
FROM tbr_avance_desarrollo A
INNER JOIN tbp_desarrollos D ON A.IdDesarrollo = D.IdDesarrollo
INNER JOIN tbr_usuario_desarrollos UD ON D.IdDesarrollo = UD.IdDesarrollo
WHERE UD.IdUsuario = ?
    AND UD.Estatus = 1
    AND A.ValorObjetivo > 0
    AND A.ValorActual > 0
    AND (
        (A.ValorActual / A.ValorObjetivo * 100) < 80 
        OR (A.ValorActual / A.ValorObjetivo * 100) >= 100
    )
GROUP BY D.IdDesarrollo, A.Categoria, A.ValorActual, A.ValorObjetivo, D.Nombre_Desarrollo
ORDER BY porcentaje_avance ASC
LIMIT 5";

$stmt = $conexion->prepare($sqlObra);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $porcentaje = $row['porcentaje_avance'];
    
    if ($porcentaje >= 100) {
        $prioridad = 'info';
        $icono = '🟢';
        $titulo = 'Obra Completada';
    } elseif ($porcentaje < 50) {
        $prioridad = 'media';
        $icono = '🔴';
        $titulo = 'Avance Bajo';
    } else {
        $prioridad = 'media';
        $icono = '🟡';
        $titulo = 'Avance Atrasado';
    }
    
    $alertas[] = [
        'tipo' => 'obra',
        'prioridad' => $prioridad,
        'icono' => $icono,
        'titulo' => $titulo,
        'mensaje' => sprintf(
            '%s - %s al %.1f%%',
            $row['Nombre_Desarrollo'],
            $row['Categoria'],
            $porcentaje
        ),
        'porcentaje' => $porcentaje
    ];
}
$stmt->close();

$conexion->close();

// Ordenar por prioridad
$prioridades = ['critica' => 1, 'alta' => 2, 'media' => 3, 'info' => 4];
usort($alertas, function($a, $b) use ($prioridades) {
    return $prioridades[$a['prioridad']] <=> $prioridades[$b['prioridad']];
});

// Filtrar alertas sin sentido (solo una vez)
$alertasFiltradas = array_filter($alertas, function($a) {
    // No mostrar plusvalía si es menor a 5%
    if ($a['tipo'] === 'plusvalia' && isset($a['porcentaje']) && $a['porcentaje'] < 5) {
        return false;
    }
    // No mostrar obra si está al 0%
    if ($a['tipo'] === 'obra' && isset($a['porcentaje']) && $a['porcentaje'] <= 0) {
        return false;
    }
    return true;
});

// Re-indexar el array
$alertasFiltradas = array_values($alertasFiltradas);

echo json_encode([
    'total' => count($alertasFiltradas),
    'criticas' => count(array_filter($alertasFiltradas, fn($a) => $a['prioridad'] === 'critica')),
    'alertas' => $alertasFiltradas
], JSON_UNESCAPED_UNICODE);
?>