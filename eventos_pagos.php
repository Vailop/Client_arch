<?php
    /**
     * eventos_pagos.php
     * Llena los pagos de tbr_pagos por usuario 
     */

    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/config.php';

    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['idusuario'])) {
        http_response_code(401);
        echo json_encode([]);
        exit;
    }

    $cn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($cn->connect_errno) {
        http_response_code(500);
        echo json_encode(['error' => 'DB connection error']);
        exit;
    }
    $cn->set_charset('utf8mb4');

    $idUsuario = (int) $_SESSION['idusuario'];

    /* FullCalendar envía start y end (YYYY-MM-DD). Usamos un rango seguro por defecto. */
    $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01', strtotime('-1 month'));
    $end   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-t',  strtotime('+2 month'));

    /* CORREGIDO: Consulta usando campos correctos de tbr_pagos */
    $sql = "SELECT P.FechaPago, P.Monto, P.Estatus, P.Dpto, P.IdDesarrollo, D.Nombre_Desarrollo 
             FROM tbr_pagos P
             LEFT JOIN tbp_desarrollos D ON D.IdDesarrollo = P.IdDesarrollo
             WHERE P.IdUsuario = ? AND P.FechaPago >= ? AND P.FechaPago < ?
             ORDER BY P.FechaPago ASC";

    $stmt = $cn->prepare($sql);
    $stmt->bind_param('iss', $idUsuario, $start, $end);
    $stmt->execute();
    $res = $stmt->get_result();

    $hoy = date('Y-m-d');
    $events = [];

    while ($r = $res->fetch_assoc()) {
        // Título corto (para que no se corte en la vista mensual)
        $nombreDev   = $r['Nombre_Desarrollo'] ?: 'Desarrollo';
        $tituloCorto = 'Pago — ' . $nombreDev . ' (Dpto ' . $r['Dpto'] . ')';

        // Color por estatus y vencido
        $color = '#00365a';                                  // pendiente (por defecto)
        if ((int)$r['Estatus'] === 2) $color = '#28a745';    // pagado (verde)
        if ((int)$r['Estatus'] === 0) $color = '#6c757d';    // cancelado (gris)
        if ((int)$r['Estatus'] === 1 && $r['FechaPago'] < $hoy) {
            $color = '#dc3545';                                // vencido (rojo)
        }

        $events[] = [
            // mostrado en el calendario (corto)
            'title'   => $tituloCorto,
            'start'   => $r['FechaPago'],
            'allDay'  => true,
            'color'   => $color,

            // extras para el click (detalles)
            'dev'     => $r['Nombre_Desarrollo'],
            'dpto'    => (string)$r['Dpto'],
            'monto'   => number_format((float)$r['Monto'], 2, '.', ','),
            'estatus' => (int)$r['Estatus'],
        ];
    }

    $stmt->close();
    echo json_encode($events, JSON_UNESCAPED_UNICODE);