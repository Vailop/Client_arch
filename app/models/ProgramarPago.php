<?php

require_once ROOT . "app/core/Database.php";

/**
 * Clase ProgramarPago
 * 
 * Maneja la lógica relacionada con pagos programados de los usuarios:
 * - Consultar pagos en un rango de fechas.
 * - Obtener pagos pendientes.
 * - Obtener historial detallado con comprobantes y recibos.
 */
class ProgramarPago
{
    /** @var PDO|null Conexión a la base de datos */
    private static $conn;

    /**
     * Inicializa la conexión estática a la base de datos (si no existe).
     * 
     * Se llama automáticamente en cada método antes de ejecutar queries.
     */
    private static function __constructStatic()
    {
        if (self::$conn === null) {
            $database = Database::getInstance(); // Singleton de la clase Database
            self::$conn = $database->getConnection(); // Obtenemos la conexión PDO
        }
    }

    /**
     * Obtiene los pagos programados de un usuario en un rango de fechas.
     *
     * @param int    $idUsuario ID del usuario.
     * @param string $start     Fecha de inicio del rango (YYYY-MM-DD).
     * @param string $end       Fecha de fin del rango (YYYY-MM-DD).
     * 
     * @return array Lista de pagos programados con datos del desarrollo, estatus y fecha de pago real.
     */
    public static function getPagosProgramadosByUserId($idUsuario, $start, $end)
    {
        self::__constructStatic();
        try {
            $sql = "SELECT 
                        pp.monto_esperado, 
                        pp.fecha_vencimiento, 
                        pp.id_pago_realizado,
                        d.nombre AS nombre_desarrollo,
                        
                        -- Información de la transacción real (si existe)
                        cep.nombre AS estatus_real, /* APROBADO / PENDIENTE / RECHAZADO */
                        p.fecha_pago AS fecha_realizada /* Fecha del pago realizado */
                    FROM programar_pagos pp
                    
                    LEFT JOIN desarrollos d 
                        ON d.id_desarrollo = pp.id_desarrollo 
                    
                    LEFT JOIN pagos p 
                        ON p.id_pago = pp.id_pago_realizado
                    
                    LEFT JOIN catalogo_estado_pago cep 
                        ON cep.id_estado_pago = p.id_estado_pago 

                    WHERE pp.id_usuario = :id_usuario 
                      AND pp.fecha_vencimiento >= :start 
                      AND pp.fecha_vencimiento <= :end
                    ORDER BY pp.fecha_vencimiento ASC";

            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':start', $start, PDO::PARAM_STR);
            $stmt->bindParam(':end', $end, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna array asociativo
        } catch (PDOException $e) {
            error_log("Error en getPagosProgramadosByUserId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las cuotas pendientes de un usuario (no han sido cubiertas).
     * Un pago está pendiente si `id_pago_realizado` es NULL.
     *
     * @param int    $idUsuario      ID del usuario.
     * @param int    $idDesarrollo   ID del desarrollo.
     * @param string $departamentoNo Número del departamento.
     * 
     * @return array Lista de pagos pendientes.
     */
    public static function getPagosPendientesParaModal($idUsuario, $idDesarrollo, $departamentoNo)
    {
        self::__constructStatic();
        try {
            $sql = "SELECT 
                        pp.id_cronograma_pago,
                        pp.monto_esperado, 
                        pp.fecha_vencimiento
                    FROM programar_pagos pp
                    WHERE pp.id_usuario = :idUsuario 
                      AND pp.id_desarrollo = :idDesarrollo 
                      AND pp.departamento_no = :departamentoNo 
                      AND pp.id_pago_realizado IS NULL -- Solo pagos pendientes
                    ORDER BY pp.fecha_vencimiento ASC";

            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':idDesarrollo', $idDesarrollo, PDO::PARAM_INT);
            $stmt->bindParam(':departamentoNo', $departamentoNo, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener pagos pendientes para modal: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el historial detallado de cuotas programadas,
     * incluyendo:
     * - Monto esperado vs monto realizado.
     * - Estado real del pago (aprobado, pendiente, rechazado).
     * - URLs de recibos del administrador y comprobantes del cliente.
     *
     * @param int    $idUsuario      ID del usuario.
     * @param int    $idDesarrollo   ID del desarrollo.
     * @param string $departamentoNo Número del departamento.
     * 
     * @return array Historial de pagos con información detallada.
     */
    public static function getHistorialPagosDetallado($idUsuario, $idDesarrollo, $departamentoNo)
    {
        self::__constructStatic();
        try {
            // Obtenemos el ID del tipo de archivo "recibo_admin"
            $sqlTipoRecibo = "SELECT id_tipo_archivo 
                              FROM catalogo_tipos_archivo 
                              WHERE nombre = 'recibo_admin'";
            $idTipoRecibo = self::$conn->query($sqlTipoRecibo)->fetchColumn();

            $sql = "SELECT 
                        pp.id_cronograma_pago,
                        pp.monto_esperado, 
                        pp.fecha_vencimiento,
                        pp.id_pago_realizado,
                        
                        -- Detalles de la transacción real
                        p.monto AS monto_realizado,
                        p.fecha_pago AS fecha_transaccion,
                        cep.nombre AS estatus_real, 

                        -- Recibo del administrador
                        a_admin.ruta AS recibo_admin_url,

                        -- Comprobante del cliente
                        a_cliente.ruta AS comprobante_cliente_url

                    FROM programar_pagos pp

                    LEFT JOIN pagos p 
                        ON p.id_pago = pp.id_pago_realizado

                    LEFT JOIN catalogo_estado_pago cep 
                        ON cep.id_estado_pago = p.id_estado_pago

                    LEFT JOIN archivos a_admin 
                        ON a_admin.id_registro_asociado = p.id_pago 
                        AND a_admin.tabla_asociada = 'pagos'
                        AND a_admin.id_tipo_archivo = :idTipoRecibo 

                    LEFT JOIN archivos a_cliente
                        ON a_cliente.id_registro_asociado = p.id_pago 
                        AND a_cliente.tabla_asociada = 'pagos'
                        AND a_cliente.id_tipo_archivo = (
                            SELECT id_tipo_archivo 
                            FROM catalogo_tipos_archivo 
                            WHERE nombre = 'comprobante_cliente'
                        )
                    
                    WHERE pp.id_usuario = :idUsuario 
                      AND pp.id_desarrollo = :idDesarrollo 
                      AND pp.departamento_no = :departamentoNo 
                    
                    ORDER BY pp.fecha_vencimiento ASC";

            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':idDesarrollo', $idDesarrollo, PDO::PARAM_INT);
            $stmt->bindParam(':departamentoNo', $departamentoNo, PDO::PARAM_STR);
            $stmt->bindParam(':idTipoRecibo', $idTipoRecibo, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getHistorialPagosDetallado: " . $e->getMessage());
            return [];
        }
    }
}