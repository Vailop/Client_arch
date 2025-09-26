<?php
/**
 * Clase controladora para la autenticación de usuarios.
 * Contiene la lógica para mostrar el formulario de login y
 * para procesar la solicitud de inicio de sesión.
 */
class AuthController {

    /**
     * Muestra la vista del formulario de login.
     * Este método se encarga de cargar y renderizar la página de inicio de sesión.
     * @return void
     */
    public function login() {
        // Incluye el archivo de la vista que contiene el formulario HTML de login.
        require_once ROOT . 'app/views/auth/login.php';
    }

    /**
     * Maneja la lógica de validación de credenciales cuando se envía el formulario de login.
     * Procesa la solicitud POST, se conecta a la base de datos y verifica los datos del usuario.
     * @return void
     */
    public function handleLogin() {
        // Verifica si la solicitud HTTP es de tipo POST, lo que indica que el formulario fue enviado.
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Usa el patrón Singleton para obtener la única instancia de la conexión a la base de datos.
            $database = Database::getInstance();
            $conn = $database->getConnection();

            // Obtiene los datos enviados desde el formulario.
            $RFCUser = $_POST["username"];
            $contrasena = $_POST["password"];

            // Lógica de validación de usuario y contraseña.
            // Se usa una sentencia preparada para evitar inyecciones SQL.
            $consulta = "SELECT id_usuario, rfc, password_hash, nombre, id_rol FROM usuarios WHERE rfc = ?";
            
            $stmt = $conn->prepare($consulta);
            // Si la preparación de la consulta falla, detiene la ejecución.
            if (!$stmt) {
                // `errorInfo()` se usa para obtener información detallada del error de PDO.
                die("Error al preparar la consulta: " . $conn->errorInfo()[2]);
            }
            
            // Ejecuta la consulta con el nombre de usuario (RFC).
            $stmt->execute([$RFCUser]);
            // Obtiene la primera fila del resultado como un array asociativo.
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifica si se encontró un usuario con el RFC proporcionado.
            if ($fila) {
                // Compara la contraseña ingresada con el hash almacenado en la base de datos.
                // `password_verify()` es una función segura para verificar contraseñas hasheadas.
                if (password_verify($contrasena, $fila["password_hash"])) {
                    
                    // Si las credenciales son correctas, inicia la sesión del usuario.
                    session_start();
                    // Almacena datos del usuario en la sesión para usarlos en otras páginas.
                    $_SESSION["idusuario"] = $fila["id_usuario"];
                    $_SESSION["nombre"] = $fila["nombre"];
                    $_SESSION["id_rol"] = $fila["id_rol"];

                    // Redirige al usuario a la página de inicio correspondiente a su rol.
                    if ($fila["id_rol"] == 1) { // 1 para 'administrador'
                        // La constante BASE_URL se asume que está definida en algún archivo de configuración.
                        header("Location: " . BASE_URL . "admin/home");
                    } else if ($fila["id_rol"] == 2) { // 2 para 'cliente'
                        header("Location: " . BASE_URL . "home");
                    }
                    exit(); // Detiene la ejecución del script después de la redirección.

                } else {
                    // Contraseña incorrecta.
                    // Nota: Usar `alert()` en PHP no es una buena práctica para producción.
                    // Es mejor redirigir con un parámetro de error o usar un sistema de mensajes flash.
                    echo '<script type="text/javascript">alert("Contraseña incorrecta");</script>';
                }
            } else {
                // Usuario no encontrado.
                echo '<script type="text/javascript">alert("Usuario no encontrado");</script>';
            }
        }
    }

    /**
     * Cierra la sesión del usuario y lo redirige a la página de login.
     * @return void
     */
    public function logout() {
        // Asegurarse de que la sesión exista
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Destruye todas las variables de la sesión
        $_SESSION = array();

        // Si se desea destruir la sesión completamente, también se debe borrar la cookie de sesión.
        // Nota: Esto destruirá la sesión y no solo los datos de sesión.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finalmente, destruye la sesión
        session_destroy();

        // Redirige al usuario a la página de login
        header("Location: " . BASE_URL . "login");
        exit();
    }
}