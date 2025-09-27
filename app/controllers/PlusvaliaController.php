<?php
// app/controllers/PlusvaliaController.php

// Incluye la clase Database, que es necesaria para la conexión.
// Esta clase probablemente se carga automáticamente, pero la inclusión explícita es buena para la claridad.
require_once ROOT . "app/core/Database.php";

/**
 * Clase controladora para gestionar la lógica de la plusvalía.
 * Proporciona un único método para procesar una petición HTTP,
 * consultar datos de plusvalía y devolverlos en formato JSON.
 */
class PlusvaliaController {

    // Propiedad privada para almacenar la conexión a la base de datos.
    private $conn;

    /**
     * Constructor de la clase.
     * Se ejecuta automáticamente cuando se crea una nueva instancia del controlador.
     * @return void
     */
    public function __construct() {
        // Obtenemos la instancia única de la clase Database.
        $database = Database::getInstance();
        // Almacenamos el objeto de conexión PDO para su uso en los métodos del controlador.
        $this->conn = $database->getConnection();
    }

    /**
     * Método principal que maneja la petición para obtener los datos de plusvalía.
     * Lee los parámetros de la URL, consulta la base de datos y
     * devuelve los resultados en un formato JSON estructurado.
     * @return void
     */
    public function getPlusvaliaData() {
        // Establecemos el encabezado de la respuesta HTTP.
        // Esto le dice al cliente (por ejemplo, JavaScript) que la respuesta
        // es un documento JSON y que debe interpretarse como tal.
        header('Content-Type: application/json; charset=utf-8');

        // Capturamos los parámetros de la URL de forma segura.
        // Usamos el operador ternario y una conversión a entero ((int))
        // para asegurar que las variables sean del tipo correcto y tengan un valor predeterminado.
        $idDesarrollo = isset($_GET['IdDesarrollo']) ? (int)$_GET['IdDesarrollo'] : 0;
        $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
        
        // Validamos el año para evitar errores de consulta o valores inesperados.
        // Si el año es inválido, se establece por defecto el año actual.
        if ($anio < 2000 || $anio > 2100) {
            $anio = (int)date('Y');
        }

        // Inicializamos los arrays y variables que se usarán en la respuesta.
        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $valorM2 = array_fill(0, 12, null); // Array con 12 elementos, todos inicializados a 'null'.
        $varPct = []; // Array vacío para almacenar la variación porcentual.
        $nombre = null;

        // Verificamos si el ID del desarrollo es válido antes de realizar las consultas.
        if ($idDesarrollo > 0) {
            // Consulta SQL para obtener los valores mensuales del metro cuadrado.
            // Se usa una sentencia preparada para evitar inyecciones SQL.
            $sql = "SELECT mes, m2_mensual
                    FROM desarrollo_costo_mensual
                    WHERE id_desarrollo = :id_desarrollo AND anio = :anio
                    ORDER BY mes ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_desarrollo', $idDesarrollo, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            
            // Recorremos los resultados y llenamos el array $valorM2.
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // El índice del array se ajusta para que el mes 1 (Enero) corresponda al índice 0.
                $i = max(1, min(12, (int)$r['mes'])) - 1;
                $valorM2[$i] = (float)$r['m2_mensual'];
            }
            // Liberamos los recursos de la declaración.
            $stmt = null;

            // Segunda consulta para obtener el nombre del desarrollo.
            // Esto podría optimizarse uniendo las consultas, pero funciona como está.
            $q = $this->conn->prepare("SELECT nombre FROM desarrollos WHERE id_desarrollo = :id_desarrollo");
            $q->bindParam(':id_desarrollo', $idDesarrollo, PDO::PARAM_INT);
            $q->execute();
            // `fetchColumn()` es útil para obtener el valor de una sola columna.
            $nombre = $q->fetchColumn();
            $q = null;
        }

        // Lógica para calcular la plusvalía porcentual.
        $prev = null; // Almacenará el valor del mes anterior.
        for ($i = 0; $i < 12; $i++) {
            $val = $valorM2[$i];
            // Si hay un valor actual y un valor anterior no nulos...
            if ($prev !== null && $val !== null && $prev > 0) {
                // Calcula la variación porcentual y la redondea a 2 decimales.
                $varPct[$i] = round((($val - $prev) / $prev) * 100, 2);
            } else {
                // Si no hay datos suficientes, se asigna 'null'.
                $varPct[$i] = null;
            }
            // Actualiza el valor del mes anterior si el valor actual no es nulo.
            if ($val !== null) {
                $prev = $val;
            }
        }
        
        // Devolvemos la respuesta en formato JSON.
        // `json_encode` convierte un array o un objeto PHP en una cadena JSON.
        // `JSON_UNESCAPED_UNICODE` es importante para que los caracteres especiales
        // como las tildes no se codifiquen.
        echo json_encode([
            'labels'  => $labels,
            'valorM2' => $valorM2,
            'varPct'  => $varPct,
            'anio'    => $anio,
            'id'      => $idDesarrollo,
            'nombre'  => $nombre
        ], JSON_UNESCAPED_UNICODE);
    }
}