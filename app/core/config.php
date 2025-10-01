<?php
// Define las constantes de configuración para la conexión a la base de datos (DB).

/**
 * Sección de Configuración de la Base de Datos
 *
 * Estas constantes son utilizadas por la clase 'Database' (Singleton)
 * para establecer una conexión PDO con el servidor MySQL.
 */

// Define la dirección IP o el nombre del host donde se encuentra el servidor de base de datos.
define("DB_HOST", "127.0.0.1");
define("DB_PORT", "3307");
define("DB_NAME", "admon_arch2"); 
define("DB_USER", "root"); 
define("DB_PASS", ""); 

/**
 * Sección de Constantes de Ruta
 *
 * Define la ruta absoluta raíz del proyecto para facilitar la inclusión de archivos
 * desde cualquier parte de la aplicación.
 */

// Verifica si la constante ROOT no ha sido definida previamente.
if (!defined("ROOT")) {
    // Si no está definida, la define como la ruta del directorio padre (uno arriba) 
    // del directorio donde se encuentra este archivo (asumiendo que está en 'config').
    define("ROOT", dirname(__DIR__) . '/');
}
?>