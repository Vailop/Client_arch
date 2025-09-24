<?php
// Configuración de base de datos para 'admon_arch2'
define("DB_HOST", "127.0.0.1");
define("DB_PORT", "3307");
define("DB_NAME", "admon_arch2"); // Asegúrate de que el nombre sea el correcto
define("DB_USER", "root");
define("DB_PASS", ""); // Deja la contraseña en blanco para tu entorno local

// Esta constante ya estaba en tu código y se mantiene
if (!defined("ROOT")) {
    define("ROOT", dirname(__DIR__) . '/');
}
?>