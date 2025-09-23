<?php
    require 'config.php';
    // Iniciar la sesión
    session_start();

    // Verificar si se envió el formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtener los datos del formulario
        $RFCUser = $_POST["username"];
        $contrasena = $_POST["password"];

        // Conectar a la base de datos (Asegúrate de actualizar la información de conexión)
        $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

        // Consulta SQL para verificar las credenciales utilizando una consulta preparada
        $consulta = "SELECT US.IdUsuario, US.RFC, US.Contrasena, US.Nombre, PE.IdPerfil, PE.DesPerfil, US.Avatar
                    FROM tbp_usuarios as US
                    INNER JOIN tbc_perfiles as PE ON US.IdPerfil = PE.IdPerfil
                    WHERE US.RFC = ?";

        // Preparar la consulta
        $stmt = $conexion->prepare($consulta);

        // Vincular los parámetros
        $stmt->bind_param("s", $RFCUser);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // Usuario encontrado, verificar la contraseña
            $fila = $resultado->fetch_assoc();

            if ($contrasena == $fila["Contrasena"]) {

                if($RFCUser == $contrasena)
                {
                    if($fila["IdPerfil"] == 1)
                    {
                        $_SESSION["idusuario"] = $fila["IdUsuario"];
                        $_SESSION["nombre"] = $fila["Nombre"];
                        $_SESSION["Avatar"] = $fila["Avatar"];
                        //echo "Redirigiendo a modificar datos de usuario...1";
                        header("Location: datos_usuario.php");
                    }
                    else if($fila["IdPerfil"] == 2)
                    {
                        $_SESSION["idusuario"] = $fila["IdUsuario"];
                        $_SESSION["nombre"] = $fila["Nombre"];
                        $_SESSION["Avatar"] = $fila["Avatar"];
                        //echo "Redirigiendo a modificar datos de usuario...2";
                        header("Location: datos_usuario.php");
                    }
                    exit();
                }
                else
                {
                    echo "Redirigiendo a Inicio...";
                    $_SESSION["idusuario"] = $fila["IdUsuario"];
                    $_SESSION["nombre"] = $fila["Nombre"];
                    $_SESSION["perfil"] = $fila["DesPerfil"];
                    $_SESSION["Avatar"] = $fila["Avatar"];
                    
                    if($fila["IdPerfil"] == 1)
                    {
                        header("Location: home_admin.php");
                    }
                    else if($fila["IdPerfil"] == 2)
                    {
                        header("Location: home.php");
                    }
                    exit();
                }

            } else {
                echo '<script type="text/javascript">alert("Contraseña incorrecta");</script>';
            }
        } else {
            echo '<script type="text/javascript">alert("Usuario no encontrado");</script>';
        }

        // Cerrar la conexión a la base de datos
        $conexion->close();
    }
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords" content="Archanděl, desarrollos, Desarrollos, departamentos, Departamentos, casas, Casas, renta, Renta, venta, Venta, residencias, Residencias, oficinas, Oficinas, proyectos inmobiliarios, lujo, CDMX, área metropolitana">
    <meta name="description" content="Archanděl Desarrollos Inmobiliarios en CDMX y área metropolitana. Creamos espacios de alto nivel que potencian la calidad de vida, combinando diseño, innovación y exclusividad.">

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

                            <p class="text-white h6 mb-3 text-justify">Utiliza tus credenciales para iniciar sesión, por favor. Si aún no eres miembro, por favor <a href="#" class="text-white font-weight-bold">solicita tu regístro.</a>.</p>
                        </div>

                        <div class="form-side">
                            <span class="logo-single"></span>
                            
                            <h6 class="mb-4">Iniciar Sesión</h6>
                            <form action="login.php" method="post">
                                <label class="form-group has-float-label mb-4">
                                    <input type="text" class="form-control" id="username" name="username" placeholder="" required />
                                    <span>RFC</span>
                                </label>

                                <label class="form-group has-float-label mb-4">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="" required />
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