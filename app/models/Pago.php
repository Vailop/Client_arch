<?php

// Incluye el archivo de la clase Database, que se espera que esté en el directorio core.
require_once ROOT . "app/core/Database.php";

/**
 * Clase estática (Modelo) para gestionar las operaciones de la tabla de pagos.
 * Su propósito es interactuar con la base de datos para obtener información de pagos realizados.
 */
class Pago
{
    // Propiedad estática privada para mantener la conexión a la base de datos (PDO).
    private static $conn;

    /**
     * Constructor estático interno para inicializar y obtener la conexión a la base de datos.
     * Implementa un patrón de inicialización perezosa (lazy loading) para asegurar que 
     * la conexión se establezca solo una vez.
     */
    private static function __constructStatic()
    {
        // Verifica si la conexión (self::$conn) aún no ha sido establecida (es null).
        if (self::$conn === null) {
            // Obtiene la instancia única (Singleton) de la clase Database.
            $database = Database::getInstance();
            // Asigna la conexión PDO de la base de datos a la propiedad estática.
            self::$conn = $database->getConnection();
        }
    }

    /**
     * Obtiene los pagos realizados por un usuario específico dentro de un rango de fechas.
     * * @param int $idUsuario El ID del usuario cuyos pagos se quieren obtener.
     * @param string $start La fecha de inicio del rango (formato 'YYYY-MM-DD').
     * @param string $end La fecha de fin del rango (formato 'YYYY-MM-DD').
     * @return array Un array de arrays asociativos con los datos de los pagos, o un array vacío en caso de error o no encontrar resultados.
     */
    public static function getPagosByUserId($idUsuario, $start, $end)
    {
        // Inicializa la conexión a la base de datos antes de ejecutar la consulta.
        self::__constructStatic();
        
        try {
            // Consulta SQL para obtener la fecha de pago, el monto, el nombre del estatus y el nombre del desarrollo.
            // Se unen las tablas 'pagos' (p), 'catalogo_estado_pago' (cep) y 'desarrollos' (d).
            // La cláusula WHERE filtra por el ID del usuario y el rango de fechas de pago.
            // Los resultados se ordenan por la fecha de pago de forma ascendente.
            $sql = "SELECT p.fecha_pago, p.monto, cep.nombre AS estatus_nombre, d.nombre AS nombre_desarrollo
                    FROM pagos p
                    INNER JOIN catalogo_estado_pago cep ON cep.id_estado_pago = p.id_estado_pago
                    INNER JOIN desarrollos d ON d.id_desarrollo = p.id_desarrollo
                    WHERE p.id_usuario = :id_usuario AND p.fecha_pago >= :start AND p.fecha_pago <= :end
                    ORDER BY p.fecha_pago ASC";
            
            // Prepara la declaración SQL. Esto ayuda a prevenir la inyección SQL.
            $stmt = self::$conn->prepare($sql);
            
            // Vincula los parámetros de la consulta a las variables.
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':start', $start, PDO::PARAM_STR);
            $stmt->bindParam(':end', $end, PDO::PARAM_STR);
            
            // Ejecuta la consulta preparada.
            $stmt->execute();
            
            // Devuelve todos los resultados como un array asociativo.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Captura cualquier error relacionado con la base de datos (PDOException).
            // Registra el error en el log del servidor para su posterior revisión.
            error_log("Error en getPagosByUserId: " . $e->getMessage());
            // Devuelve un array vacío para indicar que la operación falló o no produjo resultados.
            return [];
        }
    }
}