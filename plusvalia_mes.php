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

    // Obtener precio de compra y actual
    $precioCompra = null;
    $precioActual = null;
    $plusvaliaTotalPct = null;

    if ($idDesarrollo > 0) {
        // Verificar si hay sesión activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['idusuario'])) {
            $idUsuario = (int)$_SESSION['idusuario'];
            
            // Intentar obtener de tbr_pagos
            $q = $conexion->prepare(
                "SELECT 
                    SUM(Precio_Compraventa) as total_compra,
                    SUM(Precio_Actual) as total_actual,
                    COUNT(*) as num_pagos
                FROM tbr_pagos 
                WHERE IdUsuario = ? 
                    AND IdDesarrollo = ?
                    AND Precio_Compraventa > 0
                    AND Precio_Actual > 0"
            );
            $q->bind_param('ii', $idUsuario, $idDesarrollo);
            $q->execute();
            $result = $q->get_result();
            
            if ($row = $result->fetch_assoc()) {
                if ($row['num_pagos'] > 0) {
                    $precioCompra = (float)$row['total_compra'];
                    $precioActual = (float)$row['total_actual'];
                    
                    if ($precioCompra > 0) {
                        $plusvaliaTotalPct = (($precioActual - $precioCompra) / $precioCompra) * 100;
                    }
                }
            }
            $q->close();
            
            // Si no hay datos en tbr_pagos, buscar en tbr_usuario_desarrollos
            if ($precioCompra === null || $precioCompra == 0) {
                $q = $conexion->prepare(
                    "SELECT 
                        M2Inicial,
                        (SELECT AVG(M2Mensual) 
                        FROM tbr_desarrollos_costo_mensual 
                        WHERE IdDesarrollo = ? AND Anio = ? AND M2Mensual > 0
                        ORDER BY Mes DESC LIMIT 1) as valor_m2_actual
                    FROM tbr_usuario_desarrollos
                    WHERE IdUsuario = ? AND IdDesarrollo = ? AND Estatus = 1
                    LIMIT 1"
                );
                $q->bind_param('iiii', $idDesarrollo, $anio, $idUsuario, $idDesarrollo);
                $q->execute();
                $result = $q->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $m2 = (float)$row['M2Inicial'];
                    $valorM2Actual = (float)$row['valor_m2_actual'];
                    
                    // Buscar valor m2 del primer mes registrado
                    $stmtInicial = $conexion->prepare(
                        "SELECT M2Mensual 
                        FROM tbr_desarrollos_costo_mensual 
                        WHERE IdDesarrollo = ? AND M2Mensual > 0
                        ORDER BY Anio ASC, Mes ASC 
                        LIMIT 1"
                    );
                    $stmtInicial->bind_param('i', $idDesarrollo);
                    $stmtInicial->execute();
                    $resInicial = $stmtInicial->get_result();
                    
                    if ($rowInicial = $resInicial->fetch_assoc()) {
                        $valorM2Inicial = (float)$rowInicial['M2Mensual'];
                        
                        if ($m2 > 0 && $valorM2Inicial > 0 && $valorM2Actual > 0) {
                            $precioCompra = $m2 * $valorM2Inicial;
                            $precioActual = $m2 * $valorM2Actual;
                            $plusvaliaTotalPct = (($precioActual - $precioCompra) / $precioCompra) * 100;
                        }
                    }
                    $stmtInicial->close();
                }
                $q->close();
            }
        }
    }

    // Calcular estadísticas
    $valoresValidos = array_filter($valorM2, fn($v) => $v !== null);

    $estadisticas = [
        'min' => count($valoresValidos) > 0 ? min($valoresValidos) : null,
        'max' => count($valoresValidos) > 0 ? max($valoresValidos) : null,
        'promedio' => count($valoresValidos) > 0 ? array_sum($valoresValidos) / count($valoresValidos) : null,
        'ultimo' => null,
        'primero' => null,
        'tendencia' => null,
        'variacionTotal' => null,
        'variacionTotalPct' => null
    ];

    // Obtener primer y último valor
    foreach ($valorM2 as $v) {
        if ($v !== null) {
            if ($estadisticas['primero'] === null) $estadisticas['primero'] = $v;
            $estadisticas['ultimo'] = $v;
        }
    }

    // Calcular variación total del año
    if ($estadisticas['primero'] !== null && $estadisticas['ultimo'] !== null && $estadisticas['primero'] > 0) {
        $estadisticas['variacionTotal'] = $estadisticas['ultimo'] - $estadisticas['primero'];
        $estadisticas['variacionTotalPct'] = round(
            ($estadisticas['variacionTotal'] / $estadisticas['primero']) * 100, 
            2
        );
        
        if ($estadisticas['variacionTotalPct'] > 2) {
            $estadisticas['tendencia'] = 'alcista';
        } elseif ($estadisticas['variacionTotalPct'] < -2) {
            $estadisticas['tendencia'] = 'bajista';
        } else {
            $estadisticas['tendencia'] = 'estable';
        }
    }

    echo json_encode([
        'labels'  => $labels,
        'valorM2' => $valorM2,
        'varPct'  => $varPct,
        'anio'    => $anio,
        'id'      => $idDesarrollo,
        'nombre'  => $nombre,
        'precioCompra' => $precioCompra,
        'precioActual' => $precioActual,
        'plusvaliaTotalPct' => $plusvaliaTotalPct ? round($plusvaliaTotalPct, 2) : null,
        'estadisticas' => $estadisticas
    ], JSON_UNESCAPED_UNICODE);
?>