<?php
require 'config.php';
session_start();

// Variables para mensajes
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rfcUser = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    // Validaciones básicas
    if (empty($rfcUser) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        
        if ($conexion->connect_error) {
            $error = 'Error de conexión. Intente más tarde.';
        } else {
            // Consulta preparada
            $consulta = "SELECT IdUsuario, RFC, Contrasena, Nombre, IdPerfil, Avatar, 
                               RequiereCambioPassword, Estatus
                        FROM tbp_usuarios 
                        WHERE RFC = ? AND Estatus = 1";
            
            $stmt = $conexion->prepare($consulta);
            $stmt->bind_param("s", $rfcUser);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $usuario = $resultado->fetch_assoc();
                
                // Verificar contraseña hasheada
                if (password_verify($password, $usuario["Contrasena"])) {
                    
                    // Configurar sesión
                    $_SESSION["idusuario"] = $usuario["IdUsuario"];
                    $_SESSION["nombre"] = $usuario["Nombre"];
                    $_SESSION["perfil"] = $usuario["IdPerfil"];
                    $_SESSION["Avatar"] = $usuario["Avatar"];
                    
                    // ✅ ADMINISTRADORES: Acceso directo (sin forzar cambio de password)
                    if ($usuario["IdPerfil"] == 1) {
                        header("Location: home_admin.php");
                        exit();
                    }
                    
                    // ✅ CLIENTES: Verificar si es primer ingreso
                    if ($usuario["RequiereCambioPassword"] == 1) {
                        header("Location: datos_usuario.php");
                        exit();
                    }
                    
                    // ✅ CLIENTES: Login normal
                    header("Location: home.php");
                    exit();
                    
                } else {
                    $error = 'RFC o contraseña incorrectos';
                }
            } else {
                $error = 'RFC o contraseña incorrectos';
            }
            
            $stmt->close();
            $conexion->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel - Iniciar Sesión</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords" content="Archandél, desarrollos, Desarrollos, departamentos, Departamentos, casas, Casas, renta, Renta, venta, Venta, residencias, Residencias, oficinas, Oficinas, proyectos inmobiliarios, lujo, CDMX, área metropolitana">
    <meta name="description" content="Archandél Desarrollos Inmobiliarios en CDMX y área metropolitana. Creamos espacios de alto nivel que potencian la calidad de vida, combinando diseño, innovación y exclusividad.">

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
                <div class="col-12 col-md-4 mx-auto my-auto">
                    <div class="card auth-card">
                        <div class="form-side">
                            <div class="align-items-center text-center">
                                <img class="mb-5" src="logos/black.png" alt="Archandel">
                            </div>
                            
                            <h6 class="mt-4 mb-5">Iniciar Sesión</h6>
                            
                            <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="simple-icon-exclamation"></i> <?php echo $error; ?>
                            </div>
                            <?php endif; ?>
                            
                            <form action="login.php" method="post">
                                <label class="form-group has-float-label mb-4">
                                    <input type="text" class="form-control" id="username" name="username" placeholder="" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required />
                                    <span>RFC</span>
                                </label>

                                <label class="form-group has-float-label mb-4">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="" required />
                                    <span>Contraseña</span>
                                </label>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="recuperar_password.php">¿Olvidaste tu contraseña?</a>
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