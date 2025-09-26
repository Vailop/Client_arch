<?php

// Incluye los archivos de los modelos necesarios para las operaciones de base de datos.
require_once ROOT . "app/models/Desarrollo.php";
require_once ROOT . "app/models/User.php";
require_once ROOT . "app/core/Database.php";

/**
 * Clase controladora para la página de departamentos.
 * Se encarga de gestionar la lógica de negocio y la preparación de la
 * vista que muestra los departamentos de un desarrollo específico para un usuario.
 */
class DepartamentosController
{
    // Propiedad privada para almacenar la conexión a la base de datos.
    private $conn;

    /**
     * Constructor del controlador.
     * Inicializa la conexión a la base de datos utilizando la clase Database.
     */
    public function __construct()
    {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    /**
     * Método principal para la página de departamentos.
     * Gestiona la sesión, valida los parámetros de la URL, consulta los datos
     * de los modelos y pasa la información a la vista.
     */
    public function index()
    {
        // Inicia la sesión si aún no está activa.
        session_start();
        
        // Si el usuario no ha iniciado sesión, lo redirige a la página de login.
        if (!isset($_SESSION["idusuario"])) {
            header("Location: /login");
            exit(); // Detiene la ejecución del script.
        }

        // Obtiene la información del usuario de la sesión.
        $idUsuario = $_SESSION["idusuario"];
        $nombreUsuario = $_SESSION["nombre"];
        $urlAvatar = isset($_SESSION["url_avatar"]) ? $_SESSION["url_avatar"] : '/img/default-avatar.png';
        
        // Calcula el mes anterior.
        $mesAnterior = (int)date('n') - 1;
        if ($mesAnterior === 0) {
            $mesAnterior = 12;
        }

        // Obtiene el ID del desarrollo de los parámetros de la URL.
        $idDesa = isset($_GET['IdDesarrollo']) ? (int)$_GET['IdDesarrollo'] : null;

        // --- CÓDIGO CORREGIDO ---
        // Define la variable $mesSeleccionado ANTES de la validación del ID.
        // Esto asegura que la variable siempre esté disponible, sin importar la ruta de ejecución.
        $mesSeleccionado = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date("m");
        // ------------------------

        // Si el ID del desarrollo no es válido, redirige a la página de inicio.
        if (empty($idDesa)) {
            header("Location: /home");
            exit();
        }

        // Obtiene la lista de todos los desarrollos asignados al usuario
        // y el desarrollo específico por su ID, utilizando la clase Desarrollo.
        $desarrollos_list = Desarrollo::getByUserId($idUsuario);
        $desarrolloObj = Desarrollo::getById($idDesa);
        
        // Obtiene el nombre del desarrollo o un mensaje por defecto si no se encuentra.
        $desarrolloNombre = $desarrolloObj['nombre'] ?? "Desarrollo no encontrado";
        
        // Obtiene la lista de departamentos para el usuario y desarrollo específicos.
        $departamentos_list = Desarrollo::getDepartamentosByUserAndDesarrollo($idUsuario, $idDesa);
        
        // Almacena todas las variables en un solo array para pasarlas a la vista.
        $data = [
            'idUsuario' => $idUsuario,
            'nombreUsuario' => $nombreUsuario,
            'urlAvatar' => $urlAvatar,
            'idDesarrollo' => $idDesa,
            'mesSeleccionado' => $mesSeleccionado,
            'desarrolloNombre' => $desarrolloNombre,
            'departamentos_list' => $departamentos_list,
            'desarrollos_list' => $desarrollos_list,
            'mesAnterior' => $mesAnterior
        ];

        // Extrae las claves del array $data en variables locales para un acceso más fácil en la vista.
        extract($data);
        
        // Incluye la vista para renderizar el HTML.
        require_once ROOT . 'app/views/user/departamentos.php';
    }
}