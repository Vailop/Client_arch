<?php

require_once ROOT . "app/core/Database.php";

class Archivo
{
    private static $conn;

    /**
     * Inicializa la conexión a la base de datos de forma estática.
     */
    private static function __constructStatic()
    {
        if (self::$conn === null) {
            $database = Database::getInstance();
            self::$conn = $database->getConnection();
        }
    }

    /**
     * Busca la URL de un archivo específico asociado a un registro y tipo.
     * * @param string $tipoNombre Nombre del tipo de archivo (ej: 'plano_arquitectonico').
     * @param int $idRegistro ID del registro asociado (ej: ID del Desarrollo).
     * @param string $tablaAsociada Nombre de la tabla asociada (ej: 'desarrollos' o 'pagos').
     * @return string|null La ruta relativa del archivo (ej: 'storage/desarrollo_1/plano.pdf') o null si no se encuentra.
     */
    public static function getUrlByTipoAndRegistro($tipoNombre, $idRegistro, $tablaAsociada)
    {
        self::__constructStatic();

        try {
            // La consulta busca la ruta del archivo haciendo JOIN con el catálogo de tipos.
            $sql = "SELECT a.ruta 
                    FROM archivos a
                    INNER JOIN catalogo_tipos_archivo c ON a.id_tipo_archivo = c.id_tipo_archivo
                    WHERE c.nombre = :tipoNombre 
                    AND a.id_registro_asociado = :idRegistro 
                    AND a.tabla_asociada = :tablaAsociada
                    ORDER BY a.fecha_creacion DESC LIMIT 1";

            $stmt = self::$conn->prepare($sql);

            // Asignación segura de parámetros
            $stmt->bindParam(':tipoNombre', $tipoNombre, PDO::PARAM_STR);
            $stmt->bindParam(':idRegistro', $idRegistro, PDO::PARAM_INT);
            $stmt->bindParam(':tablaAsociada', $tablaAsociada, PDO::PARAM_STR);

            $stmt->execute();

            // Obtener solo la columna 'ruta'
            $ruta = $stmt->fetchColumn();

            // Si se encuentra la ruta, la devolvemos. Si no, devolvemos null.
            // En tu modelo Archivo.php, al obtener la ruta:
            // NO agregues una barra al inicio si la ruta de la DB ya está bien configurada.
            return $ruta !== false ? $ruta : null; 
        } catch (PDOException $e) {
            error_log("Error en getUrlByTipoAndRegistro de Archivo: " . $e->getMessage());
            return null; // Devolver null en caso de error de DB
        }
    }

    /**
     * Sube un archivo al servidor y registra su metadata en la base de datos.
     * @param int $idUsuario ID del usuario que sube el archivo.
     * @param array $fileData Array $_FILES[] del archivo.
     * @param string $tipoNombre Nombre del tipo de archivo (ej: 'comprobante_cliente').
     * @param string $tablaAsociada Nombre de la tabla principal a la que se asociará (ej: 'pagos').
     * @param int $idRegistroAsociado ID del registro principal (ej: id_pago).
     * @return int|false ID del nuevo registro en 'archivos' o false en caso de error.
     */
    public static function uploadAndRegister(
        $idUsuario, 
        $fileData, 
        $tipoNombre, 
        $tablaAsociada, 
        $idRegistroAsociado
    ) {
        self::__constructStatic();

        $ruta_base = 'storage/comprobantes/'; // Directorio de destino
        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $nombre_original = $fileData['name'];
        
        // Generar un nombre de archivo único para evitar colisiones
        $nombre_archivo = uniqid('comp_') . '.' . $extension;
        $ruta_destino_server = ROOT . $ruta_base . $nombre_archivo;
        
        // Ruta para guardar en la BD
        $ruta_db = $ruta_base . $nombre_archivo;

        // 1. Mover el archivo subido al destino final
        if (!move_uploaded_file($fileData['tmp_name'], $ruta_destino_server)) {
            error_log("Error al mover el archivo subido.");
            return false;
        }

        try {
            // 2. Obtener el ID del tipo de archivo
            $sqlTipo = "SELECT id_tipo_archivo FROM catalogo_tipos_archivo WHERE nombre = ?";
            $stmtTipo = self::$conn->prepare($sqlTipo);
            $stmtTipo->execute([$tipoNombre]);
            $idTipoArchivo = $stmtTipo->fetchColumn();

            if (!$idTipoArchivo) {
                error_log("Tipo de archivo no encontrado: " . $tipoNombre);
                return false;
            }

            // 3. Registrar en la tabla archivos
            $sql = "INSERT INTO archivos (id_tipo_archivo, id_registro_asociado, tabla_asociada, ruta, nombre_original, extension, usuario_creacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = self::$conn->prepare($sql);
            $stmt->execute([
                $idTipoArchivo, 
                $idRegistroAsociado, 
                $tablaAsociada, 
                $ruta_db, 
                $nombre_original, 
                $extension, 
                $idUsuario
            ]);

            return self::$conn->lastInsertId();

        } catch (PDOException $e) {
            error_log("Error de DB al registrar archivo: " . $e->getMessage());
            return false;
        }
    }

}