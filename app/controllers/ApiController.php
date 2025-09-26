<?php
// app/controllers/ApiController.php

// Incluye los modelos necesarios para obtener los datos de la base de datos.
require_once ROOT . "app/models/Pago.php";
require_once ROOT . "app/models/Desarrollo.php";
require_once ROOT . "app/models/ProgramarPago.php"; 
require_once ROOT . "app/core/Database.php";

/**
 * Clase controladora para gestionar peticiones de API.
 * Su propósito es responder a peticiones HTTP con datos en formato JSON,
 * sirviendo como un backend para la lógica del frontend.
 */
class ApiController
{
    // Propiedad privada para la conexión a la base de datos.
    private $conn;

    /**
     * Constructor del controlador.
     * Inicializa la conexión a la base de datos usando el patrón Singleton.
     */
    public function __construct()
    {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    /**
     * Método para obtener eventos de calendario.
     * Combina los pagos realizados y los pagos programados para el usuario
     * y los devuelve como un array de eventos en formato JSON.
     */
    public function getEventosCalendario()
    {
        // Establece el encabezado para asegurar que la respuesta sea tratada como JSON.
        header('Content-Type: application/json; charset=utf-8');

        // Inicia la sesión si no está activa.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verifica si el usuario ha iniciado sesión. Si no, devuelve un error de autenticación 401.
        if (!isset($_SESSION['idusuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Acceso no autorizado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Obtiene el ID del usuario de la sesión.
        $idUsuario = (int) $_SESSION['idusuario'];

        // Captura los parámetros de fecha 'start' y 'end' de la URL.
        // Si no se proveen, usa un rango de fechas por defecto.
        $start = $_GET['start'] ?? date('Y-m-01', strtotime('-1 month'));
        $end = $_GET['end'] ?? date('Y-m-t', strtotime('+2 month'));

        try {
            // Usa los modelos para obtener los datos. Esto mantiene el controlador limpio.
            $pagosRealizados = Pago::getPagosByUserId($idUsuario, $start, $end);
            $pagosProgramados = ProgramarPago::getPagosProgramadosByUserId($idUsuario, $start, $end);

            $events = []; // Array para almacenar los eventos finales.
            $hoy = date('Y-m-d');

            // --- Procesamiento de pagos realizados ---
            foreach ($pagosRealizados as $pago) {
                // Asigna colores basados en el estatus del pago.
                $color = '#00365a'; // Color por defecto (p.ej., 'pendiente').
                if ($pago['estatus_nombre'] === 'aprobado') {
                    $color = '#28a745'; // Verde para 'aprobado'.
                } elseif ($pago['estatus_nombre'] === 'rechazado') {
                    $color = '#dc3545'; // Rojo para 'rechazado'.
                }

                // Crea el título del evento con el nombre del desarrollo y el monto.
                $titulo = "Pago - {$pago['nombre_desarrollo']} ($: " . number_format($pago['monto'], 2, '.', ',') . ")";

                // Agrega el evento al array.
                $events[] = [
                    'title'      => $titulo,
                    'start'      => $pago['fecha_pago'],
                    'color'      => $color,
                    'allDay'     => true,
                    'monto'      => number_format($pago['monto'], 2, '.', ','),
                    'estatus'    => $pago['estatus_nombre'],
                    'desarrollo' => $pago['nombre_desarrollo'],
                    'tipo'       => 'realizado'
                ];
            }

            // --- Procesamiento de pagos programados ---
            foreach ($pagosProgramados as $pago) {
                // Asigna colores y estatus para pagos programados.
                $color = '#e27b00'; // Naranja para 'programado'.
                $estatus = 'PROGRAMADO';

                // Si la fecha de vencimiento ya pasó, se considera 'vencido'.
                if ($pago['fecha_vencimiento'] < $hoy) {
                    $color = '#dc3545'; // Rojo para 'vencido'.
                    $estatus = 'VENCIDO';
                }
                
                $titulo = "Vencimiento - {$pago['nombre_desarrollo']} ($: " . number_format($pago['monto_esperado'], 2, '.', ',') . ")";

                // Agrega el evento al array.
                $events[] = [
                    'title'      => $titulo,
                    'start'      => $pago['fecha_vencimiento'],
                    'color'      => $color,
                    'allDay'     => true,
                    'monto'      => number_format($pago['monto_esperado'], 2, '.', ','),
                    'estatus'    => $estatus,
                    'desarrollo' => $pago['nombre_desarrollo'],
                    'tipo'       => 'programado'
                ];
            }

            // Devuelve la respuesta final en formato JSON.
            echo json_encode($events, JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            // Maneja errores de la base de datos, devuelve un error 500.
            http_response_code(500);
            echo json_encode(['error' => 'Error de la base de datos: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}