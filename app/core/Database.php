<?php
/**
 * Clase Database que implementa el patrón Singleton.
 * Asegura que solo exista una única instancia de la conexión a la base de datos (PDO)
 * en toda la aplicación, proporcionando un punto de acceso global a dicha conexión.
 */
class Database {
    // Propiedad estática para almacenar la única instancia de la clase Database (el Singleton).
    private static $instance = null;
    
    // Propiedad para almacenar el objeto de conexión a la base de datos (PDO).
    private $conn;

    /**
     * Constructor privado. Esto previene la creación directa de la clase
     * desde el exterior usando 'new Database()', forzando el uso de getInstance().
     */
    private function __construct() {
        try {
            // Construye la cadena DSN (Data Source Name) usando constantes de configuración.
            // Se espera que DB_HOST, DB_PORT, DB_NAME, DB_USER, y DB_PASS estén definidas globalmente.
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            // Crea una nueva conexión PDO (PHP Data Objects).
            $this->conn = new PDO($dsn, DB_USER, DB_PASS);
            
            // Establece el modo de error de PDO a EXCEPTION.
            // Esto permite que los errores de SQL sean capturados como excepciones de PHP (PDOException).
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Establece el modo de obtención predeterminado a FETCH_ASSOC.
            // Esto asegura que los resultados de las consultas se devuelvan como arrays asociativos.
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En caso de fallo en la conexión a la base de datos,
            // detiene la ejecución del script y muestra un mensaje de error.
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Método estático público para obtener la única instancia de la clase Database (Singleton).
     * Este es el único punto de entrada para obtener la conexión.
     * @return Database La instancia única de la clase Database.
     */
    public static function getInstance() {
        // Verifica si la instancia aún no ha sido creada (es null).
        if (!self::$instance) {
            // Si no existe, crea una nueva instancia (llama al constructor privado).
            self::$instance = new Database();
        }
        // Devuelve la instancia existente o la recién creada.
        return self::$instance;
    }

    /**
     * Método público para obtener el objeto de conexión PDO.
     * Es utilizado por otras clases (Modelos) para ejecutar consultas SQL.
     * @return PDO El objeto de conexión a la base de datos.
     */
    public function getConnection() {
        return $this->conn;
    }
    
    // El método mágico __clone() y __wakeup() deberían ser declarados privados 
    // para prevenir la clonación y deserialización del objeto, reforzando el Singleton.
    // private function __clone() {}
    // private function __wakeup() {}
}
?>