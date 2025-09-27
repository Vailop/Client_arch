<?php

require_once ROOT . "app/models/Pago.php";
require_once ROOT . "app/models/Desarrollo.php";
require_once ROOT . "app/models/ProgramarPago.php";
require_once ROOT . "app/core/Database.php";

/**
 * Controlador de API
 * 
 * Este controlador maneja las peticiones AJAX/HTTP desde el frontend
 * y responde con datos en formato JSON. 
 * Ideal para integrarse con librerías JS como FullCalendar.
 */
class ApiController
{
    // Propiedad privada que guarda la conexión a la base de datos
    private $conn;

    /**
     * Constructor
     * 
     * Establece la conexión a la base de datos usando el patrón Singleton
     * para que toda la app comparta la misma conexión.
     */
    public function __construct()
    {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene los eventos del calendario (para FullCalendar).
     * 
     * - Junta pagos programados y pagos realizados.
     * - Define colores y estatus según la situación de cada pago.
     * - Devuelve un JSON con la lista de eventos.
     */
    public function getEventosCalendario()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Verificar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si el usuario no está logueado → error 401 (No autorizado)
        if (!isset($_SESSION['idusuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Acceso no autorizado'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $idUsuario = (int) $_SESSION['idusuario'];

        // Rango de fechas que pide FullCalendar (si no viene, se pone un rango por defecto)
        $start = $_GET['start'] ?? date('Y-m-01', strtotime('-1 month')); // inicio → hace un mes
        $end   = $_GET['end']   ?? date('Y-m-t', strtotime('+2 month')); // fin → 2 meses adelante

        try {
            // Consulta de pagos programados (incluye info si ya se pagó o no)
            $pagosProgramados = ProgramarPago::getPagosProgramadosByUserId($idUsuario, $start, $end);

            $events = [];
            $hoy = date('Y-m-d');

            // Recorremos todos los pagos programados
            foreach ($pagosProgramados as $pago) {
                $fecha_vencimiento = $pago['fecha_vencimiento'];
                $estatus_real = $pago['estatus_real'] ?? ''; // Estado real del pago (aprobado, pendiente, rechazado)
                $isSubmitted = !empty($pago['id_pago_realizado']); // Si tiene comprobante cargado
                $monto = number_format($pago['monto_esperado'], 2, '.', ',');

                // Estado por defecto: programado
                $color = '#00365a'; 
                $estatus = 'PROGRAMADO';
                $titulo = "Vencimiento - {$pago['nombre_desarrollo']} ($: {$monto})";

                // ----- LÓGICA DE ESTADOS -----
                if ($isSubmitted) {
                    // Hay comprobante subido
                    if ($estatus_real === 'aprobado') {
                        $color = '#28a745'; // Verde
                        $estatus = 'CUBIERTO';
                        $titulo = "CUBIERTO - " . $pago['nombre_desarrollo'];
                    } elseif ($estatus_real === 'pendiente') {
                        $color = '#ffc107'; // Amarillo
                        $estatus = 'SUBIDO (En Revisión)';
                        $titulo = "EN REVISIÓN - " . $pago['nombre_desarrollo'];
                    } elseif ($estatus_real === 'rechazado') {
                        $color = '#dc3545'; // Rojo
                        $estatus = 'RECHAZADO';
                        $titulo = "RECHAZADO - " . $pago['nombre_desarrollo'];
                    } else {
                        // Si no tiene un estado definido, pero existe el pago
                        $color = '#007bff'; // Azul claro
                        $estatus = 'SUBIDO (Sin Estado)';
                    }
                } elseif ($fecha_vencimiento < $hoy) {
                    // Si la fecha ya venció y no se subió nada
                    $color = '#dc3545'; // Rojo
                    $estatus = 'VENCIDO';
                    $titulo = "VENCIDO - " . $pago['nombre_desarrollo'];
                }

                // Construcción del evento para FullCalendar
                $events[] = [
                    'title'   => $titulo,
                    'start'   => $fecha_vencimiento,
                    'color'   => $color,
                    'allDay'  => true, // Evento ocupa todo el día
                    'monto'   => $monto,
                    'estatus' => $estatus,
                    'tipo'    => 'vencimiento' // Tipo de evento
                ];
            }

            // Se responde con los eventos en formato JSON
            echo json_encode($events, JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            // Error de base de datos → 500
            http_response_code(500);
            error_log("API Error (eventos_pagos): " . $e->getMessage());
            echo json_encode(['error' => 'Error al procesar la consulta de eventos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obtiene los pagos pendientes para mostrarlos en un modal (ventana emergente).
     * 
     * - Requiere idUsuario, idDesarrollo y numero de departamento.
     * - Devuelve en JSON las cuotas aún no cubiertas.
     */
    public function getPagosPendientesForModal()
    {
        header('Content-Type: application/json; charset=utf-8');
        session_start();

        // Parámetros necesarios
        $idUsuario = (int)($_SESSION['idusuario'] ?? 0);
        $idDesarrollo = (int)($_GET['idDesarrollo'] ?? 0);
        $departamentoNo = trim($_GET['departamentoNo'] ?? '');

        // Validación de parámetros
        if ($idUsuario === 0 || $idDesarrollo === 0 || empty($departamentoNo)) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Faltan parámetros de usuario/desarrollo.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Consulta al modelo para obtener pagos pendientes
        $pagos = ProgramarPago::getPagosPendientesParaModal($idUsuario, $idDesarrollo, $departamentoNo);

        // Respuesta en JSON
        echo json_encode($pagos, JSON_UNESCAPED_UNICODE);
    }
}