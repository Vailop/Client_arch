<?php
require 'config.php';
session_start();

// Verificar autenticación
if (!isset($_SESSION["idusuario"])) {
    header("Location: login.php");
    exit();
}

$mensaje = '';
$error = '';
$idUsuario = $_SESSION["idusuario"];

$conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Obtener datos actuales del usuario
$sql = "SELECT RFC, Nombre, Correo_electronico, Telefono, Avatar, IdPerfil, RequiereCambioPassword 
        FROM tbp_usuarios WHERE IdUsuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

$esPrimerIngreso = ($usuario['RequiereCambioPassword'] == 1);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $passwordNuevo = trim($_POST['password_nuevo']);
    $passwordConfirm = trim($_POST['password_confirm']);
    
    // Validaciones
    if (empty($nombre) || empty($email)) {
        $error = 'Nombre y correo electrónico son obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico inválido';
    } elseif ($esPrimerIngreso && (empty($passwordNuevo) || empty($passwordConfirm))) {
        $error = 'Debe establecer una nueva contraseña';
    } elseif (!empty($passwordNuevo) && $passwordNuevo !== $passwordConfirm) {
        $error = 'Las contraseñas no coinciden';
    } elseif (!empty($passwordNuevo) && strlen($passwordNuevo) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } else {
        // Iniciar transacción
        $conexion->begin_transaction();
        
        try {
            // Manejar avatar si se subió
            $rutaAvatar = $usuario['Avatar'];
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $resultado = guardarAvatar($idUsuario, $_FILES['avatar']);
                if ($resultado['success']) {
                    $rutaAvatar = $resultado['ruta'];
                } else {
                    throw new Exception($resultado['message']);
                }
            }
            
            // Actualizar datos básicos
            if (!empty($passwordNuevo)) {
                $passwordHash = password_hash($passwordNuevo, PASSWORD_DEFAULT);
                $sqlUpdate = "UPDATE tbp_usuarios 
                             SET Nombre = ?, Correo_electronico = ?, Telefono = ?, 
                                 Avatar = ?, Contrasena = ?, RequiereCambioPassword = 0,
                                 FechaUltimaActualizacion = NOW()
                             WHERE IdUsuario = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param('sssssi', $nombre, $email, $telefono, $rutaAvatar, $passwordHash, $idUsuario);
            } else {
                $sqlUpdate = "UPDATE tbp_usuarios 
                             SET Nombre = ?, Correo_electronico = ?, Telefono = ?, 
                                 Avatar = ?, FechaUltimaActualizacion = NOW()
                             WHERE IdUsuario = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param('ssssi', $nombre, $email, $telefono, $rutaAvatar, $idUsuario);
            }
            
            if (!$stmtUpdate->execute()) {
                throw new Exception('Error al actualizar datos');
            }
            
            $stmtUpdate->close();
            $conexion->commit();
            
            // Actualizar sesión
            $_SESSION['nombre'] = $nombre;
            $_SESSION['Avatar'] = $rutaAvatar;
            
            $mensaje = 'Datos actualizados correctamente';
            
            // Si era primer ingreso, redirigir al home
            if ($esPrimerIngreso) {
                sleep(2);
                if ($usuario['IdPerfil'] == 1) {
                    header("Location: home_admin.php");
                } else {
                    header("Location: home.php");
                }
                exit();
            }
            
            // Recargar datos actualizados
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();
            $stmt->close();
            
        } catch (Exception $e) {
            $conexion->rollback();
            $error = $e->getMessage();
        }
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="en">

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
                <div class="col-12 col-md-6 mx-auto my-auto">
                    <div class="card auth-card">
                        <div class="form-side">
                            <div class="align-items-center text-center">
                                <img class="mb-5" src="logos/black.png">
                            </div>
                            
                            <?php if ($esPrimerIngreso): ?>
                            <div class="alert alert-info">
                                <i class="simple-icon-info"></i> 
                                <strong>Bienvenido.</strong> Por favor complete su información de perfil.
                            </div>
                            <?php endif; ?>
                            
                            <h5 class="mt-4 mb-5"><?php echo $esPrimerIngreso ? 'Completar Perfil' : 'Actualizar Datos'; ?></h5>
                            
                            <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-success">
                                <i class="simple-icon-check"></i> <?php echo $mensaje; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="simple-icon-exclamation"></i> <?php echo $error; ?>
                            </div>
                            <?php endif; ?>
                            
                            <form action="datos_usuario.php" method="post" enctype="multipart/form-data">
                                
                                <!-- Avatar -->
                                <div class="text-center mb-4">
                                    <img src="<?php echo !empty($usuario['Avatar']) ? 'uploads/' . $usuario['Avatar'] : 'img/profile-pic-generic.jpg'; ?>" 
                                         class="avatar-preview mb-2" 
                                         id="avatarPreview"
                                         alt="Avatar">
                                    <div>
                                        <label for="avatar" class="btn btn-outline-primary btn-sm avatar-upload">
                                            <i class="simple-icon-camera"></i> Cambiar foto
                                        </label>
                                        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif" style="display: none;" onchange="previewAvatar(this)">
                                        <small class="form-text text-muted d-block">JPG, PNG o GIF - Máximo 2MB</small>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-12 col-md-6">
                                        <!-- RFC (solo lectura) -->
                                        <div class="form-group">
                                            <label class="form-group has-float-label mb-3">
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['RFC']); ?>" readonly>
                                                <span>RFC <span class="text-danger">*</span></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-md-6">
                                        <!-- Nombre -->
                                        <label class="form-group has-float-label mb-3">
                                            <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($usuario['Nombre']); ?>" required>
                                            <span>Nombre Completo <span class="text-danger">*</span></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-12 col-md-6">
                                        <!-- Email -->
                                        <label class="form-group has-float-label mb-3">
                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($usuario['Correo_electronico']); ?>" required>
                                            <span>Correo Electrónico <span class="text-danger">*</span></span>
                                        </label>
                                    </div>

                                    <div class="form-group col-12 col-md-6">
                                        <!-- Teléfono -->
                                        <label class="form-group has-float-label mb-3">
                                            <input type="tel" class="form-control" name="telefono" value="<?php echo htmlspecialchars($usuario['Telefono']); ?>" placeholder="">
                                            <span>Teléfono</span>
                                        </label>
                                    </div>
                                </div>
                                                               
                                <h6 class="mb-3">
                                    <?php echo $esPrimerIngreso ? 'Establecer Contraseña' : 'Cambiar Contraseña (Opcional)'; ?>
                                </h6>
                                
                                <div class="form-row">
                                    <div class="form-group col-12 col-md-6">
                                        <!-- Nueva contraseña -->
                                        <label class="form-group has-float-label mb-3">
                                            <input type="password" class="form-control" name="password_nuevo" id="password_nuevo" minlength="8" <?php echo $esPrimerIngreso ? 'required' : ''; ?>>
                                            <span>Nueva Contraseña <?php echo $esPrimerIngreso ? '<span class="text-danger">*</span>' : ''; ?></span>
                                        </label>
                                    </div>

                                    <div class="form-group col-12 col-md-6">
                                        <!-- Confirmar contraseña -->
                                        <label class="form-group has-float-label mb-4">
                                            <input type="password" class="form-control" name="password_confirm" id="password_confirm" minlength="8" <?php echo $esPrimerIngreso ? 'required' : ''; ?>>
                                            <span>Confirmar Contraseña <?php echo $esPrimerIngreso ? '<span class="text-danger">*</span>' : ''; ?></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <?php if (!$esPrimerIngreso): ?>
                                    <a href="<?php echo $usuario['IdPerfil'] == 1 ? 'home_admin.php' : 'home.php'; ?>" class="btn btn-outline-secondary">Cancelar</a>
                                    <?php endif; ?>
                                    
                                    <button type="submit" class="btn btn-primary btn-lg"><?php echo $esPrimerIngreso ? 'Completar y Continuar' : 'Guardar Cambios'; ?></button>
                                </div>
                            </form>
                        </div>
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

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                // Validar tamaño
                if (input.files[0].size > 2 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 2MB.');
                    input.value = '';
                    return;
                }
                
                // Mostrar preview
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#avatarPreview').attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Validar que las contraseñas coincidan
        $('#password_confirm').on('blur', function() {
            var nuevo = $('#password_nuevo').val();
            var confirmar = $(this).val();
            
            if (nuevo && confirmar && nuevo !== confirmar) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.invalid-feedback').length) {
                    $(this).after('<div class="invalid-feedback">Las contraseñas no coinciden</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        });
    </script>
</body>

</html>