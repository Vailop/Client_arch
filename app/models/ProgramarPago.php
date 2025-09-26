<?php

// Incluye el archivo de la clase Database, que se espera que esté en el directorio core.
require_once ROOT . "app/core/Database.php";

/**
 * Clase estática para gestionar la programación de pagos.
 * Se encarga de interactuar con la base de datos para obtener información de pagos programados.
 */
class ProgramarPago
{
    // Propiedad estática para almacenar la conexión a la base de datos.
    private static $conn;

    /**
     * Constructor estático privado para inicializar la conexión a la base de datos.
     * Se llama internamente por otros métodos estáticos para asegurar que la conexión
     * esté disponible antes de cualquier operación de base de datos.
     * Utiliza el patrón Singleton de la clase Database para obtener la conexión.
     */
    private static function __constructStatic()
    {
        // Verifica si la conexión aún no ha sido establecida.
        if (self::$conn === null) {
            // Obtiene la única instancia de la clase Database.
            $database = Database::getInstance();
            // Almacena el objeto de conexión PDO.
            self::$conn = $database->getConnection();
        }
    }

    /**
     * Obtiene los pagos programados para un usuario específico dentro de un rango de fechas.
     * * @param int $idUsuario El ID del usuario.
     * @param string $start La fecha de inicio del rango (formato 'YYYY-MM-DD').
     * @param string $end La fecha de fin del rango (formato 'YYYY-MM-DD').
     * @return array Un array de objetos (PDO::FETCH_ASSOC) con los pagos, o un array vacío en caso de error.
     */
    public static function getPagosProgramadosByUserId($idUsuario, $start, $end)
    {
        // Asegura que la conexión a la base de datos esté inicializada.
        self::__constructStatic();
        
        try {
            // Consulta SQL para seleccionar el monto esperado, fecha de vencimiento y el nombre del desarrollo.
            // Une las tablas 'programar_pagos' (p) y 'desarrollos' (d).
            // Filtra por el ID del usuario y el rango de fechas de vencimiento.
            // Ordena los resultados por la fecha de vencimiento de forma ascendente.
            $sql = "SELECT p.monto_esperado, p.fecha_vencimiento, d.nombre AS nombre_desarrollo
                    FROM programar_pagos p
                    INNER JOIN desarrollos d ON d.id_desarrollo = p.id_desarrollo
                    WHERE p.id_usuario = :id_usuario AND p.fecha_vencimiento >= :start AND p.fecha_vencimiento <= :end
                    ORDER BY p.fecha_vencimiento ASC";
            
            // Prepara la declaración SQL para evitar inyecciones SQL.
            $stmt = self::$conn->prepare($sql);
            
            // Vincula los parámetros con los valores de las variables.
            // PDO::PARAM_INT para idUsuario, PDO::PARAM_STR para las fechas.
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':start', $start, PDO::PARAM_STR);
            $stmt->bindParam(':end', $end, PDO::PARAM_STR);
            
            // Ejecuta la consulta.
            $stmt->execute();
            
            // Devuelve todos los resultados como un array asociativo.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Registra el error en el log del servidor para depuración.
            error_log("Error en getPagosProgramadosByUserId: " . $e->getMessage());
            // Devuelve un array vacío en caso de que ocurra una excepción de base de datos.
            return [];
        }
    }
}