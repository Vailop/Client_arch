<?php

// Incluye la clase Database, que es necesaria para la conexión.
require_once ROOT . "app/core/Database.php";

/**
 * Clase controladora para la página de inicio (Home).
 * Se encarga de la lógica de negocio, la obtención de datos
 * de la base de datos y la preparación de la vista principal del usuario.
 */
class HomeController
{
    // Propiedad privada para almacenar la conexión a la base de datos.
    private $conn;

    /**
     * Constructor del controlador.
     * Inicializa la conexión a la base de datos utilizando el patrón Singleton.
     */
    public function __construct()
    {
        // Obtiene la instancia única de la clase Database.
        $database = Database::getInstance();
        // Almacena el objeto de conexión PDO.
        $this->conn = $database->getConnection();
    }

    /**
     * Método principal para la página de inicio.
     * Gestiona la sesión, obtiene los datos del usuario y los KPI,
     * y prepara los datos para ser mostrados en la vista.
     */
    public function index()
    {
        // Inicia la sesión si aún no está activa.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si el usuario no ha iniciado sesión, lo redirige a la página principal (login).
        if (!isset($_SESSION["idusuario"])) {
            header("Location: /");
            exit(); // Detiene la ejecución del script.
        }

        // Define la variable $basePath para uso en la vista.
        $basePath = '/'; 

        // Captura los datos del usuario desde la sesión.
        $idUsuario = $_SESSION["idusuario"];
        $nombreUsuario = $_SESSION["nombre"];
        $urlAvatar = isset($_SESSION["url_avatar"]) ? $_SESSION["url_avatar"] : '/img/default-avatar.png';
        
        // Calcula el mes anterior para alguna lógica de negocio.
        $mesAnterior = (int)date('n') - 1;
        if ($mesAnterior === 0) {
            $mesAnterior = 12;
        }

        // --- Obtención de datos para los KPI (Key Performance Indicators) ---

        // Consulta para obtener el total de desarrollos y departamentos del usuario.
        $sql_kpi = "SELECT COUNT(DISTINCT id_desarrollo) AS total_desarrollos, COUNT(*) AS total_departamentos FROM usuarios_desarrollos WHERE id_usuario = ?";
        $stmt_kpi = $this->conn->prepare($sql_kpi);
        $stmt_kpi->execute([$idUsuario]);
        $kpi = $stmt_kpi->fetch(PDO::FETCH_ASSOC);

        // Asigna los valores a variables, usando el operador de fusión de null (??) para evitar errores.
        $total_desarrollos = $kpi['total_desarrollos'] ?? 0;
        $total_departamentos = $kpi['total_departamentos'] ?? 0;

        // Consulta para obtener el total de metros cuadrados (M2) para los KPI.
        $sql_m2 = "SELECT COALESCE(SUM(dp.m2_inicial), 0) AS total_m2 
                    FROM pagos p
                    INNER JOIN detalles_pago dp ON p.id_pago = dp.id_pago
                    WHERE p.id_usuario = ?";
        $stmt_m2 = $this->conn->prepare($sql_m2);
        $stmt_m2->execute([$idUsuario]);
        // `fetchColumn` es ideal para obtener un solo valor de una consulta.
        $total_m2_bruto = $stmt_m2->fetchColumn();
        // Formatea el total de M2 para que se muestre con dos decimales y separador de miles.
        $total_m2_formateado = number_format($total_m2_bruto, 2, '.', ',');

        // --- Obtención de listas para la vista ---

        // Consulta para obtener la lista de desarrollos asignados al usuario.
        $sql_desarrollos = "SELECT d.id_desarrollo, d.nombre FROM desarrollos d
                            INNER JOIN usuarios_desarrollos ud ON d.id_desarrollo = ud.id_desarrollo
                            WHERE ud.id_usuario = ?";
        $stmt_des = $this->conn->prepare($sql_desarrollos);
        $stmt_des->execute([$idUsuario]);
        $desarrollos_list = $stmt_des->fetchAll(PDO::FETCH_ASSOC);

        // Consulta para obtener las categorías de avance de obra.
        $sql_categorias = "SELECT nombre FROM catalogo_avance_desarrollo ORDER BY orden ASC";
        $stmt_cat = $this->conn->query($sql_categorias); // 'query' se usa porque no hay parámetros.
        $categorias_orden = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);

        // --- Lógica para obtener el avance de obra por desarrollo ---
        
        $avances_por_desarrollo = [];
        // Itera sobre la lista de desarrollos para obtener los datos de avance para cada uno.
        foreach ($desarrollos_list as $desarrollo) {
            $idDes = $desarrollo['id_desarrollo'];
            
            // Consulta para obtener el avance de obra específico para este desarrollo.
            $sql_avance = "SELECT cao.nombre as categoria, dad.valor_actual, dad.valor_objetivo 
                           FROM avance_desarrollo dad
                           INNER JOIN catalogo_avance_desarrollo cao ON dad.id_categoria_avance = cao.id_categoria_avance
                           WHERE dad.id_desarrollo = ? ORDER BY cao.orden ASC";
            $stmt_avance = $this->conn->prepare($sql_avance);
            $stmt_avance->execute([$idDes]);
            $avances = $stmt_avance->fetchAll(PDO::FETCH_ASSOC);
            $stmt_avance = null;

            $datos_avance = [];
            // Itera sobre los resultados y calcula los porcentajes.
            foreach ($avances as $avance_db) {
                $valorActual = (float)$avance_db['valor_actual'];
                $valorObjetivo = (float)$avance_db['valor_objetivo'];
                // Calcula el porcentaje, evitando la división por cero.
                $porcentaje = $valorObjetivo > 0 ? round(($valorActual / $valorObjetivo) * 100, 2) : 0;
                
                // Almacena los datos en un array asociativo.
                $datos_avance[$avance_db['categoria']] = [
                    'valorActualFmt' => number_format($valorActual, 2, '.', ','),
                    'valorObjetivoFmt' => number_format($valorObjetivo, 2, '.', ','),
                    'porcentaje' => $porcentaje
                ];
            }
            // Agrega los datos de avance al array principal.
            $avances_por_desarrollo[$idDes] = $datos_avance;
        }


        // --- Preparación final de la vista ---

        // Combina todas las variables en un solo array para pasarlas a la vista.
        $data = [
            'basePath'            => $basePath,
            'idUsuario'           => $idUsuario,
            'nombreUsuario'       => $nombreUsuario,
            'urlAvatar'           => $urlAvatar,
            'mesAnterior'         => $mesAnterior,
            'total_desarrollos'   => $total_desarrollos,
            'total_departamentos' => $total_departamentos,
            'total_m2_formateado' => $total_m2_formateado,
            'desarrollos_list'    => $desarrollos_list,
            'avances_por_desarrollo' => $avances_por_desarrollo,
            'categorias_orden'    => $categorias_orden
        ];

        // 'extract' convierte las claves del array $data en variables locales.
        // Por ejemplo, $data['idUsuario'] se convierte en la variable $idUsuario.
        extract($data);
        
        // Incluye el archivo de la vista para renderizar el HTML.
        require_once ROOT . 'app/views/user/homeUser.php';
    }
}