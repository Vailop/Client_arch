<?php
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/config.php';

    $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $conexion->set_charset('utf8mb4');

    $idDesarrollo = isset($_GET['IdDesarrollo']) ? (int)$_GET['IdDesarrollo'] : 0;
    $anio         = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
    if ($anio < 2000 || $anio > 2100) { $anio = (int)date('Y'); }

    $labels  = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $valorM2 = array_fill(0, 12, null);

    // Valores por mes
    if ($idDesarrollo > 0) {
    $sql = "SELECT Mes, M2Mensual
            FROM tbr_desarrollos_costo_mensual
            WHERE IdDesarrollo = ? AND Anio = ?
            ORDER BY Mes ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('ii', $idDesarrollo, $anio);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $i = max(1, min(12, (int)$r['Mes'])) - 1; // 0..11
        $valorM2[$i] = (float)$r['M2Mensual'];
    }
    $stmt->close();
    }

    // Plusvalía % vs mes anterior
    $varPct = [];
    $prev = null;
    for ($i = 0; $i < 12; $i++) {
    $val = $valorM2[$i];
    if ($prev !== null && $val !== null && $prev > 0) {
        $varPct[$i] = round((($val - $prev) / $prev) * 100, 2);
    } else {
        $varPct[$i] = null;
    }
    if ($val !== null) $prev = $val;
    }

    // Nombre del desarrollo
    $nombre = null;
    if ($idDesarrollo > 0) {
    $q = $conexion->prepare("SELECT Nombre_Desarrollo FROM tbp_desarrollos WHERE IdDesarrollo = ?");
    $q->bind_param('i', $idDesarrollo);
    $q->execute();
    $q->bind_result($nombre);
    $q->fetch();
    $q->close();
    }

    echo json_encode([
    'labels'  => $labels,
    'valorM2' => $valorM2,
    'varPct'  => $varPct,
    'anio'    => $anio,
    'id'      => $idDesarrollo,
    'nombre'  => $nombre
    ], JSON_UNESCAPED_UNICODE);
?>