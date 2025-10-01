<?php

require_once ROOT . "app/models/Pago.php";
require_once ROOT . "app/models/Archivo.php";
require_once ROOT . "app/core/Database.php"; 

/**
 * Controlador encargado de manejar la lógica relacionada con los pagos
 * (ejemplo: subida de comprobantes, vinculación con cronogramas, etc.).
 */
class PagosController
{
    // Propiedad privada para mantener la conexión a la base de datos
    private $conn;

    /**
     * Constructor del controlador.
     * Inicializa la conexión a la base de datos usando la clase Database.
     */
    public function __construct()
    {
        $database = Database::getInstance();    // Obtenemos la instancia singleton de Database
        $this->conn = $database->getConnection(); // Guardamos la conexión PDO
    }

    /**
     * Procesa la subida de un comprobante de pago por parte del usuario.
     */
    public function subirComprobante()
    {
        // Iniciar sesión para verificar al usuario actual
        session_start();

        // Si el usuario no está autenticado, redirigirlo al login
        if (!isset($_SESSION["idusuario"])) {
            header("Location: " . BASE_URL . "login");
            exit();
        }

        // Verificar que la petición sea POST (el formulario fue enviado)
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            // Si no es POST, redirigir a departamentos
            header("Location: " . BASE_URL . "departamentos");
            exit();
        }

        // ID del usuario autenticado
        $idUsuario = (int)$_SESSION["idusuario"];

        // 1. Obtener datos enviados por el formulario
        $idDesarrollo     = (int)$_POST['id_desarrollo'];         // Proyecto o desarrollo
        $departamentoNo   = trim($_POST['departamento_no']);      // Número de departamento
        $idCronogramaPago = (int)$_POST['id_pago_programado'];    // Pago programado
        $montoReal        = (float)$_POST['monto_real'];          // Monto real que el cliente reporta
        $fechaReal        = $_POST['fecha_real'];                 // Fecha en la que se realizó el pago
        $comprobante      = $_FILES['comprobante'];               // Archivo subido (comprobante)
        $comentarios      = $_POST['comentarios'] ?? null;        // Comentarios opcionales

        // 2. Validaciones básicas para asegurar que los datos son correctos
        if (
            $idDesarrollo <= 0 || 
            empty($departamentoNo) || 
            $idCronogramaPago <= 0 || 
            $montoReal <= 0 || 
            $comprobante['error'] !== UPLOAD_ERR_OK
        ) {
            // Si algún dato es inválido, redirigir con error y mantener el desarrollo en pantalla
            header("Location: " . BASE_URL . "departamentos?error=datos_invalidos&IdDesarrollo=" . $idDesarrollo);
            exit();
        }

        // 3. Insertar el pago real en la tabla 'pagos'
        //    Se asume que en catalogo_estado_pago el ID 1 corresponde a "pendiente"
        $idPagoReal = Pago::createPagoReal(
            $idUsuario, 
            $idDesarrollo, 
            $departamentoNo, 
            $montoReal, 
            $fechaReal, 
            $comentarios, 
            1 // Estado "pendiente"
        );

        // 4. Si el pago se creó correctamente
        if ($idPagoReal) {

            // Subir y registrar el archivo comprobante en la tabla 'archivos'
            $idArchivo = Archivo::uploadAndRegister(
                $idUsuario,
                $comprobante,
                'comprobante_cliente', // Nombre definido en catalogo_tipos_archivo
                'pagos',               // Tabla asociada
                $idPagoReal            // ID del pago recién creado
            );

            // Vincular este pago real con el cronograma de pagos correspondiente
            Pago::linkPagoRealizadoToCronograma($idCronogramaPago, $idPagoReal);

            // Redirigir al usuario con mensaje de éxito
            header("Location: " . BASE_URL . "departamentos?success=pago_subido&IdDesarrollo=" . $idDesarrollo . "&IdUsuario=" . $idUsuario);
            exit();

        } else {
            // Si ocurre un error al crear el pago en la BD, redirigir con mensaje de error
            header("Location: " . BASE_URL . "departamentos?error=db_pago_fallo&IdDesarrollo=" . $idDesarrollo . "&IdUsuario=" . $idUsuario);
            exit();
        }
    }
}