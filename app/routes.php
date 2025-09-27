<?php
// Define la ruta base para tu aplicación
define("BASE_URL", "/");

// Arreglo que define las rutas de la aplicación con métodos HTTP
$routes = [
    // La pantalla de inicio de la aplicación es el login (método GET)
    'GET /' => 'AuthController@login',

    // La misma URL, pero para procesar el formulario de login (método POST)
    'POST /login' => 'AuthController@handleLogin',
    
    // Ruta para mostrar el formulario de login (método GET)
    'GET /login' => 'AuthController@login',

    // Ruta para cerrar sesión
    'GET /logout' => 'AuthController@logout',

    // Rutas para el dashboard de clientes
    'GET /home' => 'HomeController@index',
    'GET /departamentos' => 'DepartamentosController@index',
    
    // Rutas para el dashboard de admin
    'GET /admin/home' => 'AdminController@index',
    
    // Rutas para la API (ej. para los gráficos y el calendario)
    // El controlador ahora es PlusvaliaController
    'GET /api/plusvalia' => 'PlusvaliaController@getPlusvaliaData',
    'GET /api/eventos_pagos' => 'ApiController@getEventosCalendario',

    // La ruta del calendario
    'GET /api/plusvalia' => 'PlusvaliaController@getPlusvaliaData',
    'GET /api/eventos_pagos' => 'ApiController@getEventosCalendario',

    // --- Rutas para la gestión de pagos del cliente ---
    'POST /pagos/subir' => 'PagosController@subirComprobante',
    'GET /api/pagos_pendientes' => 'ApiController@getPagosPendientesForModal',
];

// Función para obtener la ruta refactorizada para manejar métodos HTTP
function getRoute($uri, $method) {
    global $routes;

    // Elimina cualquier query string (ej. ?IdDesarrollo=1) y guarda la URL base
    $uri_base = strtok($uri, '?');
    
    // Encuentra la ruta que coincide con la URI y el método HTTP
    $routeKey = strtoupper($method) . ' ' . $uri_base;

    // Busca una coincidencia exacta
    if (array_key_exists($routeKey, $routes)) {
        return $routes[$routeKey];
    }
    
    // Si no se encuentra la ruta, redirige a una página 404
    return 'ErrorController@notFound'; // Necesitas crear este controlador y método
}

?>