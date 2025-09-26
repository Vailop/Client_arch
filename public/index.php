<?php
/**
 * Index.php
 * * Este archivo actúa como el "Controlador Frontal" de la aplicación.
 * Es el único punto de entrada para todas las peticiones HTTP.
 * Se encarga de:
 * 1. Cargar la configuración principal.
 * 2. Cargar el autoloader de clases.
 * 3. Cargar el archivo de rutas.
 * 4. Analizar la URL solicitada.
 * 5. Despachar la petición al controlador y método correctos.
 * 6. Manejar errores 404 (página no encontrada).
 */

// Define la constante ROOT para tener una ruta absoluta a la raíz del proyecto.
// Esto facilita la inclusión de archivos sin importar la ubicación desde la que se ejecuta el script.
define("ROOT", dirname(__DIR__) . '/');

// Carga los archivos de configuración y autoloader.
// 'config.php' define constantes importantes (como la conexión a DB).
// 'Autoload.php' carga dinámicamente las clases cuando son necesarias.
require_once ROOT . 'app/core/config.php';
require_once ROOT . 'app/core/Autoload.php';

// Carga el archivo de rutas que contiene la lógica para mapear URLs a controladores.
require_once ROOT . 'app/routes.php';

// Obtiene la URL solicitada por el usuario (ej: /home, /departamentos?IdDesarrollo=1)
// y el método HTTP (GET, POST, etc.).
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Llama a la función 'getRoute()' (asumiendo que está en 'app/routes.php').
// Esta función debe retornar un string como "HomeController@index" o 'null' si no hay coincidencia.
$controllerAction = getRoute($request_uri, $request_method);

// Verifica si la ruta es válida (si getRoute no devolvió null).
if ($controllerAction === null) {
    // Si la ruta no se encuentra, devuelve un encabezado 404 y un mensaje de error.
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1>";
    echo "The page you requested was not found.";
    exit(); // Detiene la ejecución del script.
}

// Separa el nombre de la clase del controlador y el nombre del método usando el '@'.
// Por ejemplo, de "HomeController@index" se obtiene ['HomeController', 'index'].
list($controllerName, $methodName) = explode('@', $controllerAction);

// Construye el nombre completo de la clase del controlador.
$controllerClass = $controllerName;

// Verifica si la clase del controlador existe antes de intentar instanciarla.
if (class_exists($controllerClass)) {
    // Crea una nueva instancia del controlador de forma dinámica.
    $controller = new $controllerClass();

    // Llama al método del controlador de forma dinámica.
    if (method_exists($controller, $methodName)) {
        $controller->$methodName();
    } else {
        // Si el método no existe, devuelve un error 404.
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1>";
        echo "The requested method was not found.";
    }
} else {
    // Si la clase del controlador no existe, devuelve un error 404.
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1>";
    echo "The requested controller was not found.";
}