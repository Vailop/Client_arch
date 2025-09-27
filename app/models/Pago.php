<?php

require_once ROOT . "app/core/Database.php";

/**
 * Clase estática (Modelo) para gestionar las operaciones relacionadas con los pagos reales.
 * 
 * Esta clase se encarga de:
 * - Consultar pagos realizados en un rango de fechas.
 * - Obtener el último recibo emitido por el administrador.
 * - Crear un nuevo registro de pago real.
 * - Vincular un pago real con una cuota programada.
 */
class Pago
{
    /** @var PDO|null Conexión estática a la base de datos */
    private static $conn;

    /**
     * Inicializa la conexión a la base de datos (si aún no está creada).
     * Implementa inicialización perezosa (lazy loading).
     */
    private static function __constructStatic()
    {
        if (self::$conn === null) {
            $database = Database::getInstance();     // Obtiene instancia única de Database
            self::$conn = $database->getConnection(); // Obtiene conexión PDO
        }
    }

    /**
     * Obtiene los pagos realizados por un usuario en un rango de fechas.
     *
     * @param int    $idUsuario ID del usuario.
     * @param string $start     Fecha de inicio (formato 'YYYY-MM-DD').
     * @param string $end       Fecha de fin (formato 'YYYY-MM-DD').
     * 
     * @return array Lista de pagos realizados (fecha, monto, estatus, desarrollo).
     */
    public static function getPagosByUserId($idUsuario, $start, $end)
    {
        self::__constructStatic();

        try {
            $sql = "SELECT 
                        p.fecha_pago, 
                        p.monto, 
                        cep.nombre AS estatus_nombre, 
                        d.nombre AS nombre_desarrollo
                    FROM pagos p
                    INNER JOIN catalogo_estado_pago cep 
                        ON cep.id_estado_pago = p.id_estado_pago
                    INNER JOIN desarrollos d 
                        ON d.id_desarrollo = p.id_desarrollo
                    WHERE p.id_usuario = :id_usuario 
                      AND p.fecha_pago >= :start 
                      AND p.fecha_pago <= :end
                    ORDER BY p.fecha_pago ASC";

            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':start', $start, PDO::PARAM_STR);
            $stmt->bindParam(':end', $end, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getPagosByUserId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la URL del último recibo de pago emitido por el administrador (recibo_admin).
     *
     * @param int    $idUsuario      ID del usuario.
     * @param int    $idDesarrollo   ID del desarrollo.
     * @param string $departamentoNo Número del departamento.
     * 
     * @return string|null Ruta completa del recibo o null si no existe.
     */
    public static function getLatestReciboAdminUrl($idUsuario, $idDesarrollo, $departamentoNo)
    {
        self::__constructStatic();

        try {
            // Obtener el ID del tipo de archivo 'recibo_admin'
            $sqlTipo = "SELECT id_tipo_archivo 
                        FROM catalogo_tipos_archivo 
                        WHERE nombre = 'recibo_admin'";
            $stmtTipo = self::$conn->query($sqlTipo);
            $idTipoRecibo = $stmtTipo->fetchColumn();

            if (!$idTipoRecibo) {
                error_log("Error: No se encontró el ID para 'recibo_admin'.");
                return null;
            }

            // Buscar la transacción aprobada más reciente con recibo del administrador
            $sql = "SELECT a.ruta 
                    FROM pagos p
                    INNER JOIN archivos a 
                        ON a.id_registro_asociado = p.id_pago 
                        AND a.tabla_asociada = 'pagos'
                    INNER JOIN catalogo_estado_pago cep 
                        ON cep.id_estado_pago = p.id_estado_pago
                    WHERE p.id_usuario = :idUsuario
                      AND p.id_desarrollo = :idDesarrollo
                      AND p.departamento_no = :departamentoNo
                      AND cep.nombre = 'aprobado'             
                      AND a.id_tipo_archivo = :idTipoRecibo   
                    ORDER BY p.fecha_modificacion DESC 
                    LIMIT 1";

            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':idDesarrollo', $idDesarrollo, PDO::PARAM_INT);
            $stmt->bindParam(':departamentoNo', $departamentoNo, PDO::PARAM_STR);
            $stmt->bindParam(':idTipoRecibo', $idTipoRecibo, PDO::PARAM_INT);
            $stmt->execute();

            $ruta = $stmt->fetchColumn();

            return $ruta !== false ? BASE_URL . $ruta : null;
        } catch (PDOException $e) {
            error_log("Error en getLatestReciboAdminUrl: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo pago real en la tabla 'pagos'.
     *
     * @param int    $idUsuario       ID del usuario que paga.
     * @param int    $idDesarrollo    ID del desarrollo.
     * @param string $departamentoNo  Número del departamento.
     * @param float  $monto           Monto del pago.
     * @param string $fechaPago       Fecha del pago.
     * @param string $comentarios     Comentarios del cliente.
     * @param int    $idEstadoPago    Estado inicial del pago (pendiente/aprobado).
     * 
     * @return int|false ID del pago creado o false en caso de error.
     */
    public static function createPagoReal($idUsuario, $idDesarrollo, $departamentoNo, $monto, $fechaPago, $comentarios, $idEstadoPago) {
        self::__constructStatic();
        try {
            $sql = "INSERT INTO pagos 
                        (id_usuario, id_desarrollo, departamento_no, monto, fecha_pago, comentarios_cliente, id_estado_pago, usuario_creacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = self::$conn->prepare($sql);
            $stmt->execute([$idUsuario, $idDesarrollo, $departamentoNo, $monto, $fechaPago, $comentarios, $idEstadoPago, $idUsuario]);
            
            return self::$conn->lastInsertId(); // Retorna el ID generado
        } catch (PDOException $e) {
            error_log("Error al crear pago real: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vincula un pago real existente con una cuota programada.
     *
     * @param int $idCronogramaPago ID de la cuota programada.
     * @param int $idPagoReal       ID del pago real.
     * 
     * @return bool True si la vinculación fue exitosa, false en caso de error.
     */
    public static function linkPagoRealizadoToCronograma($idCronogramaPago, $idPagoReal) {
        self::__constructStatic();
        try {
            $sql = "UPDATE programar_pagos 
                    SET id_pago_realizado = ? 
                    WHERE id_cronograma_pago = ?";
            $stmt = self::$conn->prepare($sql);
            return $stmt->execute([$idPagoReal, $idCronogramaPago]);
        } catch (PDOException $e) {
            error_log("Error al vincular cronograma: " . $e->getMessage());
            return false;
        }
    }
}