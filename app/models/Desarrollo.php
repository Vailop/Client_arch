<?php

// Incluye el archivo de la clase Database, que se espera que esté en el directorio core.
require_once ROOT . "app/core/Database.php";

/**
 * Clase estática (Modelo) para la gestión de desarrollos.
 * Provee métodos para interactuar con la tabla 'desarrollos' en la base de datos.
 */
class Desarrollo
{
    // Propiedad estática privada para almacenar la conexión a la base de datos.
    private static $conn;

    /**
     * Obtiene un registro de desarrollo por su identificador.
     * Utiliza una sentencia preparada para evitar inyecciones SQL.
     * * * @param int $id El ID del desarrollo.
     * @return array|false El desarrollo como un array asociativo, o false si no se encuentra.
     */
    public static function getById($id)
    {
        try {
            // Asegura que la conexión a la base de datos esté inicializada antes de usarla.
            // Esta lógica de inicialización se repite en cada método, lo que se podría mejorar.
            if (self::$conn === null) {
                $database = Database::getInstance();
                self::$conn = $database->getConnection();
            }

            // Consulta SQL para seleccionar todos los campos de un desarrollo por su ID.
            $sql = "SELECT * FROM desarrollos WHERE id_desarrollo = ?";
            // Prepara la sentencia.
            $stmt = self::$conn->prepare($sql);
            // Ejecuta la sentencia y vincula el parámetro.
            $stmt->execute([$id]);
            // Devuelve el primer (y único) registro encontrado como un array asociativo.
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En caso de error, registra el mensaje en el log.
            error_log("Error en getById de Desarrollo: " . $e->getMessage());
            // Devuelve false para indicar que la operación falló.
            return false;
        }
    }

    /**
     * Obtiene todos los desarrollos de la tabla.
     * * @return array Una lista de todos los desarrollos como arrays asociativos.
     */
    public static function getAll()
    {
        try {
            // Lógica de inicialización de conexión.
            if (self::$conn === null) {
                $database = Database::getInstance();
                self::$conn = $database->getConnection();
            }

            // Consulta SQL para seleccionar todos los desarrollos, ordenados por nombre.
            $sql = "SELECT * FROM desarrollos ORDER BY nombre ASC";
            // Ejecuta la consulta directamente, ya que no hay parámetros de usuario.
            $stmt = self::$conn->query($sql);
            // Devuelve todos los resultados.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Registra el error.
            error_log("Error en getAll de Desarrollo: " . $e->getMessage());
            // Devuelve un array vacío en caso de fallo.
            return [];
        }
    }

    /**
     * Obtiene los desarrollos a los que está asignado un usuario específico.
     * * @param int $idUsuario El ID del usuario.
     * @return array Una lista de desarrollos asignados al usuario.
     */
    public static function getByUserId($idUsuario)
    {
        try {
            // Lógica de inicialización de conexión.
            if (self::$conn === null) {
                $database = Database::getInstance();
                self::$conn = $database->getConnection();
            }

            // Consulta SQL que une las tablas 'desarrollos' (d) y 'usuarios_desarrollos' (ud)
            // para obtener los desarrollos asociados a un usuario.
            $sql = "SELECT d.id_desarrollo, d.nombre, d.descripcion, d.imagen_principal
                    FROM desarrollos d
                    INNER JOIN usuarios_desarrollos ud ON d.id_desarrollo = ud.id_desarrollo
                    WHERE ud.id_usuario = ?";
            // Prepara la sentencia.
            $stmt = self::$conn->prepare($sql);
            // Ejecuta la sentencia con el ID de usuario.
            $stmt->execute([$idUsuario]);
            // Devuelve todos los resultados como un array asociativo.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Registra el error.
            error_log("Error en getByUserId de Desarrollo: " . $e->getMessage());
            // Devuelve un array vacío en caso de fallo.
            return [];
        }
    }

    /**
     * Obtiene los números de departamento de un usuario para un desarrollo específico.
     * * @param int $idUsuario El ID del usuario.
     * @param int $idDesarrollo El ID del desarrollo.
     * @return array La lista de números de departamento.
     */
    public static function getDepartamentosByUserAndDesarrollo($idUsuario, $idDesarrollo)
    {
        try {
            // Lógica de inicialización de conexión.
            if (self::$conn === null) {
                $database = Database::getInstance();
                self::$conn = $database->getConnection();
            }
            
            // Consulta SQL para obtener los números de departamento de un usuario en un desarrollo.
            $sql = "SELECT departamento_no FROM usuarios_desarrollos 
                    WHERE id_usuario = ? AND id_desarrollo = ?";
            // Prepara la sentencia.
            $stmt = self::$conn->prepare($sql);
            // Ejecuta la sentencia con los dos parámetros.
            $stmt->execute([$idUsuario, $idDesarrollo]);
            // Devuelve todos los resultados.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Registra el error.
            error_log("Error en getDepartamentosByUserAndDesarrollo: " . $e->getMessage());
            // Devuelve un array vacío en caso de fallo.
            return [];
        }
    }
}