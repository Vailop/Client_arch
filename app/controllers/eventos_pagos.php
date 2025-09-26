<?php
/**
 * Script para proveer un feed de eventos de pagos en formato JSON.
 * Este archivo actúa como un endpoint de API para ser consumido,
 * por la biblioteca de JavaScript FullCalendar.
 */

// Establece el encabezado de la respuesta HTTP para indicar que el contenido es JSON.
header('Content-Type: application/json; charset=utf-8');

// Incluye el archivo de configuración, que contiene las constantes de la base de datos.
require_once __DIR__ . '/config.php';

// Inicia la sesión si no está activa.
if (session_status() === PHP_SESSION_NONE) session_start();

// Verifica si el usuario ha iniciado sesión. Si no hay una sesión activa,
// devuelve un error de autenticación (401 Unauthorized) y termina el script.
if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

// Conexión a la base de datos usando la API de MySQLi.
// Se asume que las constantes de conexión están definidas en 'config.php'.
$cn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($cn->connect_errno) {
    // Si la conexión falla, devuelve un error de servidor (500 Internal Server Error).
    http_response_code(500);
    echo json_encode(['error' => 'DB connection error']);
    exit;
}
// Establece el conjunto de caracteres de la conexión para manejar tildes y caracteres especiales.
$cn->set_charset('utf8mb4');

// Obtiene el ID del usuario de la sesión y lo convierte a entero para mayor seguridad.
$idUsuario = (int) $_SESSION['idusuario'];

// Captura los parámetros 'start' y 'end' enviados por FullCalendar a través de GET.
// Si no están presentes, establece un rango seguro por defecto.
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01', strtotime('-1 month'));
$end   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-t',  strtotime('+2 month'));

// Consulta SQL para obtener los pagos programados.
// Se usa un LEFT JOIN para incluir el nombre del desarrollo, incluso si no existe.
// Se filtra por el ID de usuario y el rango de fechas.
$sql = "SELECT P.FechaPago, P.Monto, P.Estatus, P.Dpto, P.IdDesarrollo, D.Nombre_Desarrollo 
          FROM tbr_pagos P
          LEFT JOIN tbp_desarrollos D ON D.IdDesarrollo = P.IdDesarrollo
          WHERE P.IdUsuario = ? AND P.FechaPago >= ? AND P.FechaPago < ?
          ORDER BY P.FechaPago ASC";

// Prepara la sentencia SQL para prevenir inyecciones.
$stmt = $cn->prepare($sql);
// Vincula los parámetros a la consulta: 'i' para entero, 's' para cadena, 's' para cadena.
$stmt->bind_param('iss', $idUsuario, $start, $end);
// Ejecuta la sentencia.
$stmt->execute();
// Obtiene el conjunto de resultados.
$res = $stmt->get_result();

$hoy = date('Y-m-d');
$events = []; // Array donde se almacenarán los eventos del calendario.

// Itera sobre cada fila del resultado de la consulta.
while ($r = $res->fetch_assoc()) {
    // Crea un título corto para el evento, usando el nombre del desarrollo y el departamento.
    $nombreDev   = $r['Nombre_Desarrollo'] ?: 'Desarrollo'; // Usa un valor por defecto si no hay nombre.
    $tituloCorto = 'Pago — ' . $nombreDev . ' (Dpto ' . $r['Dpto'] . ')';

    // Lógica para asignar un color al evento según su estatus.
    $color = '#00365a'; // Color por defecto para 'pendiente'.
    if ((int)$r['Estatus'] === 2) {
        $color = '#28a745'; // Color verde para 'pagado'.
    }
    if ((int)$r['Estatus'] === 0) {
        $color = '#6c757d'; // Color gris para 'cancelado'.
    }
    // Si el estatus es 'pendiente' (1) y la fecha de pago ya pasó, se considera 'vencido'.
    if ((int)$r['Estatus'] === 1 && $r['FechaPago'] < $hoy) {
        $color = '#dc3545'; // Color rojo para 'vencido'.
    }

    // Agrega el evento al array de eventos con el formato requerido por FullCalendar.
    $events[] = [
        // Propiedades requeridas para mostrar el evento.
        'title'   => $tituloCorto,
        'start'   => $r['FechaPago'],
        'allDay'  => true,
        'color'   => $color,

        // Propiedades adicionales para mostrar más detalles al hacer clic.
        'dev'     => $r['Nombre_Desarrollo'],
        'dpto'    => (string)$r['Dpto'],
        'monto'   => number_format((float)$r['Monto'], 2, '.', ','),
        'estatus' => (int)$r['Estatus'],
    ];
}

// Cierra la sentencia para liberar recursos.
$stmt->close();

// Codifica el array de eventos a una cadena JSON y la envía al cliente.
// JSON_UNESCAPED_UNICODE asegura que los caracteres como tildes se muestren correctamente.
echo json_encode($events, JSON_UNESCAPED_UNICODE);