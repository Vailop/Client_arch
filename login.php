<?php
// Incluir el archivo de configuración con las credenciales de la base de datos 'constructora'
require 'config.php';

// Iniciar la sesión
session_start();

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $RFCUser = $_POST["username"];
    $contrasena = $_POST["password"];

    // Conectar a la base de datos 'constructora' usando las constantes del config.php
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    // Si la conexión falla, mostrar un mensaje de error claro
    if ($conexion->connect_error) {
        die("Error de conexión a la base de datos: " . $conexion->connect_error);
    }

    // Consulta SQL para verificar las credenciales en la tabla 'usuarios'
    $consulta = "SELECT IdUsuario, rfc, password, nombre, IdPerfil FROM tbp_usuarios WHERE rfc = ?";

    // Preparar la consulta
    $stmt = $conexion->prepare($consulta);
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    // Vincular los parámetros
    $stmt->bind_param("s", $RFCUser);

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener el resultado
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Usuario encontrado, obtener la fila
        $fila = $resultado->fetch_assoc();

        // Comparar la contraseña ingresada con la contraseña de la base de datos
        // Nota: Aquí se asume que la contraseña está en texto plano, si está hasheada, usa 'password_verify'
        if ($contrasena == $fila["password"]) {
            
            // Las credenciales son correctas, manejar la sesión
            $_SESSION["idusuario"] = $fila["IdUsuario"];
            $_SESSION["nombre"] = $fila["nombre"];
            $_SESSION["perfil"] = $fila["IdPerfil"];
            
            // Redirigir al usuario según su perfil
            if($fila["IdPerfil"] == 1)
            {
                header("Location: home_admin.php");
            }
            else if($fila["IdPerfil"] == 2)
            {
                header("Location: home.php");
            }
            exit();

        } else {
            echo '<script type="text/javascript">alert("Contraseña incorrecta");</script>';
        }
    } else {
        echo '<script type="text/javascript">alert("Usuario no encontrado");</script>';
    }

    // Cerrar la conexión
    $stmt->close();
    $conexion->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords"
        content="Archanděl, desarrollos, Desarrollos, departamentos, Departamentos, casas, Casas, renta, Renta, venta, Venta, residencias, Residencias, oficinas, Oficinas, proyectos inmobiliarios, lujo, CDMX, área metropolitana">
    <meta name="description"
        content="Archanděl Desarrollos Inmobiliarios en CDMX y área metropolitana. Creamos espacios de alto nivel que potencian la calidad de vida, combinando diseño, innovación y exclusividad.">

    <!-- Favicon icon -->
    <link rel="icon" href="/favicon.png" type="image/x-icon">

    <link rel="stylesheet" href="font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap-float-label.min.css" />
    <link rel="stylesheet" href="css/main.css" />
</head>

<body class="background show-spinner no-footer">
    <div class="fixed-background"></div>

    <main>
        <div class="container">
            <div class="row h-100">
                <div class="col-12 col-md-10 mx-auto my-auto">
                    <div class="card auth-card">
                        <div class="position-relative image-side">

                            <p class="text-white h6 mb-3 text-justify">Utiliza tus credenciales para iniciar sesión, por
                                favor. Si aún no eres miembro, por favor <a href="#"
                                    class="text-white font-weight-bold">solicita tu regístro.</a>.</p>
                        </div>

                        <div class="form-side">
                            <span class="logo-single"></span>

                            <h6 class="mb-4">Iniciar Sesión</h6>
                            <form action="login.php" method="post">
                                <label class="form-group has-float-label mb-4">
                                    <input type="text" class="form-control" id="username" name="username" placeholder=""
                                        required />
                                    <span>RFC</span>
                                </label>

                                <label class="form-group has-float-label mb-4">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="" required />
                                    <span>Contraseña</span>
                                </label>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="olvidasteContraseña.php">¿Olvidaste tu contraseña?</a>
                                    <button class="btn btn-primary btn-lg btn-shadow" type="submit">ENTRAR</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/vendor/jquery-3.3.1.min.js"></script>
    <script src="js/vendor/bootstrap.bundle.min.js"></script>
    <script src="js/dore.script.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>