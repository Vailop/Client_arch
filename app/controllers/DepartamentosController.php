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

        // ... (Verificación de sesión, obtención de $idUsuario, $nombreUsuario, $urlAvatar) ...
        if (!isset($_SESSION["idusuario"])) {
            header("Location: " . BASE_URL . "login");
            exit();
        }

        $idUsuario = $_SESSION["idusuario"];
        $nombreUsuario = $_SESSION["nombre"];
        $urlAvatar = isset($_SESSION["url_avatar"]) ? $_SESSION["url_avatar"] : '/img/default-avatar.png';

        // Se calcula el número del mes anterior (se usa para el menú, se mantiene)
        $mesAnterior = (int)date('n') - 1;
        if ($mesAnterior === 0) {
            $mesAnterior = 12;
        }

        // Obtiene el ID del desarrollo desde la URL (GET)
        $idDesa = isset($_GET['IdDesarrollo']) ? (int)$_GET['IdDesarrollo'] : null;
        // El mes seleccionado no se usa en el cuerpo, pero lo dejamos como dato de paso
        $mesSeleccionado = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date("m");

        // Si no hay desarrollo seleccionado → redirige al home
        if (empty($idDesa)) {
            header("Location: " . BASE_URL . "home");
            exit();
        }

        // ----------------- CONSULTAS PRINCIPALES -----------------

        // 1. Lista de desarrollos para el menú lateral
        $desarrollos_list = Desarrollo::getByUserId($idUsuario);

        // 2. Datos del desarrollo actual
        $desarrolloObj = Desarrollo::getById($idDesa);
        $desarrolloNombre = $desarrolloObj['nombre'] ?? "Desarrollo no encontrado";
        
        // 3. Archivos generales
        $plano_url_general = Archivo::getUrlByTipoAndRegistro('plano_arquitectonico', $idDesa, 'desarrollos');
        $manual_url_general = Archivo::getUrlByTipoAndRegistro('manual_usuario', $idDesa, 'desarrollos');

        // 4. LISTA DE DEPARTAMENTOS DETALLADOS (¡EL CAMBIO CLAVE!)
        // Ahora cargaremos TODOS los datos necesarios para CADA departamento

        $departamentos_con_datos = [];
        $departamentos_base = Desarrollo::getDepartamentosByUserAndDesarrollo($idUsuario, $idDesa);
        
        foreach ($departamentos_base as $depto_base) {
            $numDepto = $depto_base['departamento_no']; // Ejemplo: 'A-101'
            $idDesarrollo = $idDesa;
            $idUsuario = $idUsuario;

            $detalles_depto_mock = [
                'Dpto' => $numDepto,
                'IdCliente' => $idUsuario, // Este campo parece innecesario, pero lo mantenemos
                'Precio_Compraventa' => 2500000.00, // Debes obtenerlo de tu tabla de departamentos
                'm2inicial' => 100.00,
                'm2actual' => 105.00,
                'SuperficieReal' => 102.50, // Superficie en m²
                'File_Comprobante' => 'path/comprobante_' . $numDepto . '.pdf', // Último comprobante general si lo hay
                'File_Planos' => 'path/planos_' . $numDepto . '.pdf', // Planos del departamento
            ];
            // --- FIN SIMULACIÓN ---

            $depto_completo = array_merge($depto_base, $detalles_depto_mock);

            // 5. Historial de pagos (esto debe ser por departamento)
            $historial_pagos = ProgramarPago::getHistorialPagosDetallado(
                $idUsuario, 
                $idDesarrollo, 
                $numDepto
            );
            
            $depto_completo['historial_pagos'] = $historial_pagos;

            $departamentos_con_datos[] = $depto_completo;
        }

        // ----------------- PREPARAR DATOS PARA LA VISTA -----------------
        $data = [
            'idUsuario' => $idUsuario,
            'nombreUsuario' => $nombreUsuario,
            'urlAvatar' => $urlAvatar,
            'mesAnterior' => $mesAnterior,
            'idDesarrollo' => $idDesa,
            'desarrolloNombre' => $desarrolloNombre,
            'desarrollos_list' => $desarrollos_list, // Para el menú lateral
            'plano_url_general' => $plano_url_general,
            'manual_url_general' => $manual_url_general,
            // ¡ESTO ES LO NUEVO Y ES CLAVE!
            'departamentos' => $departamentos_con_datos, 
            'baseUrl' => BASE_URL . 'archivos/departamentos/', // Base URL para archivos específicos de depto
        ];

        extract($data);

        // Carga la vista correspondiente, pasando los datos
        require_once ROOT . 'app/views/user/departamentos.php';
    }
   
}