<?php
/**
 * Registra una función de autocarga de clases (autoloader).
 *
 * `spl_autoload_register()` es una función que registra una o más funciones
 * como implementaciones de `__autoload()`. Esto permite que el código
 * cargue automáticamente archivos de clases cuando se intentan usar.
 */
spl_autoload_register(function ($className) {
    // El parámetro `$className` contiene el nombre de la clase, incluyendo su namespace si lo tuviera.
    // Por ejemplo, para la clase 'Database', $className es 'Database'.
    // Para una clase como 'App\Core\Database', $className sería 'App\Core\Database'.

    // Convierte el nombre de la clase a la ruta del archivo.
    // Por ejemplo, 'App\Core\Database' se convierte en 'App/Core/Database.php'.
    // La función `str_replace()` reemplaza las barras invertidas '\' por barras normales '/'.
    $file = str_replace('\\', '/', $className) . '.php';

    // Rutas de búsqueda para tus clases.
    // Aquí defines los directorios donde tu autoloader buscará archivos de clase.
    // El orden en este array determina la prioridad de búsqueda.
    $paths = [
        'app/core/',
        'app/controllers/',
        'app/models/',
    ];

    // Itera sobre cada ruta de búsqueda para encontrar el archivo de la clase.
    foreach ($paths as $path) {
        // Concatena la constante ROOT, la ruta de búsqueda y el nombre del archivo.
        $fullPath = ROOT . $path . $file;
        
        // Comprueba si el archivo existe en la ruta completa.
        if (file_exists($fullPath)) {
            // Si el archivo existe, lo incluye en el script usando `require_once`.
            require_once $fullPath;
            
            // `return` detiene la ejecución de la función y sale del bucle.
            // Esto es crucial para no intentar cargar el mismo archivo varias veces.
            return;
        }
    }
    // Si el bucle termina y la clase no se encontró en ninguna ruta, el autoloader no hará nada más.
    // PHP generará un error fatal si la clase se intenta usar después.
});