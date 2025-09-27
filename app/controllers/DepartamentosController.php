<?php

require_once ROOT . "app/models/Desarrollo.php";
require_once ROOT . "app/models/User.php";
require_once ROOT . "app/models/Archivo.php";
require_once ROOT . "app/models/Pago.php";
require_once ROOT . "app/models/ProgramarPago.php";
require_once ROOT . "app/core/Database.php";

class DepartamentosController
{
    private $conn; // Conexión a la base de datos

    public function __construct()
    {
        // Obtiene la instancia única de la base de datos (patrón Singleton)
        $database = Database::getInstance();
        // Guarda la conexión activa en la propiedad $conn
        $this->conn = $database->getConnection();
    }

    public function index()
    {
        // Inicia la sesión (para manejar datos del usuario logueado)
        session_start();

        // Verifica que el usuario esté logueado, si no → redirige al login
        if (!isset($_SESSION["idusuario"])) {
            header("Location: " . BASE_URL . "login");
            exit();
        }

        // Se obtienen datos básicos del usuario desde la sesión
        $idUsuario = $_SESSION["idusuario"];
        $nombreUsuario = $_SESSION["nombre"];
        // Si no tiene avatar cargado, se asigna uno por defecto
        $urlAvatar = isset($_SESSION["url_avatar"]) ? $_SESSION["url_avatar"] : '/img/default-avatar.png';

        // Se calcula el número del mes anterior al actual
        $mesAnterior = (int)date('n') - 1;
        // Si el mes anterior resulta ser 0 (enero → diciembre)
        if ($mesAnterior === 0) {
            $mesAnterior = 12;
        }

        // Obtiene el ID del desarrollo desde la URL (GET) o null si no viene
        $idDesa = isset($_GET['IdDesarrollo']) ? (int)$_GET['IdDesarrollo'] : null;
        // Obtiene el mes seleccionado desde la URL (GET), si no viene → mes actual
        $mesSeleccionado = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date("m");

        // Si no hay desarrollo seleccionado → redirige al home
        if (empty($idDesa)) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        // ----------------- CONSULTAS PRINCIPALES -----------------

        // Lista de desarrollos a los que tiene acceso el usuario
        $desarrollos_list = Desarrollo::getByUserId($idUsuario);

        // Datos del desarrollo actual
        $desarrolloObj = Desarrollo::getById($idDesa);
        // Nombre del desarrollo (o mensaje si no existe)
        $desarrolloNombre = $desarrolloObj['nombre'] ?? "Desarrollo no encontrado";
        
        // Lista de departamentos del usuario en ese desarrollo
        // Ejemplo: [['departamento_no' => 'A-101'], ['departamento_no' => 'B-205']]
        $departamentos_list = Desarrollo::getDepartamentosByUserAndDesarrollo($idUsuario, $idDesa);

        // Archivos generales del desarrollo (ejemplo: plano arquitectónico, manual de usuario)
        $plano_url_general = Archivo::getUrlByTipoAndRegistro('plano_arquitectonico', $idDesa, 'desarrollos');
        $manual_url_general = Archivo::getUrlByTipoAndRegistro('manual_usuario', $idDesa, 'desarrollos');

        // ----------------- HISTORIAL DE PAGOS -----------------

        // Obtiene el número de departamento (primer elemento de la lista de departamentos)
        // Si la lista está vacía, se asigna un string vacío para evitar error
        $deptoNoParaHistorial = $departamentos_list[0]['departamento_no'] ?? ''; 
        
        $historial_pagos = []; // Se inicializa vacío
        if (!empty($deptoNoParaHistorial)) {
            // Trae las cuotas programadas + comprobantes de pago asociados
            $historial_pagos = ProgramarPago::getHistorialPagosDetallado(
                $idUsuario, 
                $idDesa, 
                $deptoNoParaHistorial
            );
        }

        // ----------------- PREPARAR DATOS PARA LA VISTA -----------------

        // Se empaquetan todos los datos que se pasarán a la vista
        $data = [
            'idUsuario' => $idUsuario,
            'nombreUsuario' => $nombreUsuario,
            'urlAvatar' => $urlAvatar,
            'mesAnterior' => $mesAnterior,
            'mesSeleccionado' => $mesSeleccionado,
            'idDesarrollo' => $idDesa,
            'desarrolloNombre' => $desarrolloNombre,
            'desarrollos_list' => $desarrollos_list,
            'plano_url_general' => $plano_url_general,
            'manual_url_general' => $manual_url_general,
            'historial_pagos' => $historial_pagos,
            'departamentos_list' => $departamentos_list 
        ];

        // Extrae cada clave de $data como variable individual
        // (ej: $idUsuario, $nombreUsuario, $desarrolloNombre, etc.)
        extract($data);

        // Carga la vista correspondiente, pasando los datos
        require_once ROOT . 'app/views/user/departamentos.php';
    }
}