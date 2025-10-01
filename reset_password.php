<?php
require 'config.php';

$mensaje = '';
$error = '';
$tokenValido = false;
$tokenExpirado = false;
$idUsuario = null;

// Verificar token en URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    
    // Validar token
    $sql = "SELECT r.IdReset, r.IdUsuario, r.FechaExpiracion, r.Usado, u.Nombre 
            FROM tbr_password_reset r
            INNER JOIN tbp_usuarios u ON r.IdUsuario = u.IdUsuario
            WHERE r.Token = ? AND r.Usado = 0";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $datos = $resultado->fetch_assoc();
        
        // Verificar si no ha expirado
        if (strtotime($datos['FechaExpiracion']) > time()) {
            $tokenValido = true;
            $idUsuario = $datos['IdUsuario'];
            $nombreUsuario = $datos['Nombre'];
            $idReset = $datos['IdReset'];
        } else {
            $tokenExpirado = true;
            $error = 'Este enlace ha expirado. Solicita uno nuevo.';
        }
    } else {
        $error = 'Enlace inválido o ya utilizado.';
    }
    
    $stmt->close();
    $conexion->close();
} else {
    $error = 'Token no proporcionado.';
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $tokenValido) {
    $passwordNuevo = trim($_POST['password_nuevo']);
    $passwordConfirm = trim($_POST['password_confirm']);
    
    if (empty($passwordNuevo) || empty($passwordConfirm)) {
        $error = 'Por favor complete todos los campos';
    } elseif ($passwordNuevo !== $passwordConfirm) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($passwordNuevo) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } else {
        $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        $conexion->begin_transaction();
        
        try {
            // Actualizar contraseña
            $passwordHash = password_hash($passwordNuevo, PASSWORD_DEFAULT);
            $sqlUpdate = "UPDATE tbp_usuarios 
                         SET Contrasena = ?, RequiereCambioPassword = 0, FechaUltimaActualizacion = NOW()
                         WHERE IdUsuario = ?";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->bind_param('si', $passwordHash, $idUsuario);
            
            if (!$stmtUpdate->execute()) {
                throw new Exception('Error al actualizar contraseña');
            }
            
            // Marcar token como usado
            $sqlToken = "UPDATE tbr_password_reset SET Usado = 1 WHERE IdReset = ?";
            $stmtToken = $conexion->prepare($sqlToken);
            $stmtToken->bind_param('i', $idReset);
            $stmtToken->execute();
            
            $conexion->commit();
            
            $mensaje = 'Contraseña actualizada exitosamente. Ya puedes iniciar sesión.';
            $tokenValido = false; // Ocultar formulario
            
            $stmtUpdate->close();
            $stmtToken->close();
            
        } catch (Exception $e) {
            $conexion->rollback();
            $error = $e->getMessage();
        }
        
        $conexion->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel - Restablecer Contraseña</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    
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
                            
                            <?php if (!empty($mensaje)): ?>
                                <!-- Éxito -->
                                <div class="text-center">
                                    <i class="iconsminds-yes d-block mb-3" style="font-size: 48px; color: #28a745;"></i>
                                    <h6 class="mb-4">¡Listo!</h6>
                                    <div class="alert alert-success">
                                        <?php echo $mensaje; ?>
                                    </div>
                                    <a href="login.php" class="btn btn-primary btn-block btn-lg">Iniciar Sesión</a>
                                </div>
                            
                            <?php elseif (!$tokenValido): ?>
                                <!-- Token inválido o expirado -->
                                <div class="text-center">
                                    <i class="iconsminds-close d-block mb-3" style="font-size: 48px; color: #dc3545;"></i>
                                    <h6 class="mb-4"><?php echo $tokenExpirado ? 'Enlace Expirado' : 'Enlace Inválido'; ?></h6>
                                    <div class="alert alert-danger">
                                        <?php echo $error; ?>
                                    </div>
                                    <a href="recuperar_password.php" class="btn btn-primary btn-block btn-lg mb-2">Solicitar Nuevo Enlace</a>
                                    <a href="login.php" class="btn btn-outline-secondary btn-block btn-lg">Volver al Inicio</a>
                                </div>
                            
                            <?php else: ?>
                                <!-- Formulario de nueva contraseña -->
                                <h6 class="mt-4 mb-5">Nueva Contraseña</h6>
                                <p class="text-muted mb-4">Hola <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong>, establece tu nueva contraseña.</p>
                                
                                <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="simple-icon-exclamation"></i> <?php echo $error; ?>
                                </div>
                                <?php endif; ?>

                                <form method="post" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
                                    <label class="form-group has-float-label mb-4">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password_nuevo" 
                                               name="password_nuevo" 
                                               minlength="8"
                                               placeholder="" 
                                               required />
                                        <span>Nueva Contraseña</span>
                                    </label>
                                    
                                    <small class="form-text text-muted mb-3">
                                        La contraseña debe tener al menos 8 caracteres.
                                    </small>

                                    <label class="form-group has-float-label mb-4">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password_confirm" 
                                               name="password_confirm" 
                                               minlength="8"
                                               placeholder="" 
                                               required />
                                        <span>Confirmar Contraseña</span>
                                    </label>

                                    <button class="btn btn-primary btn-block btn-lg btn-shadow" type="submit">
                                        Restablecer Contraseña
                                    </button>
                                </form>
                            <?php endif; ?>
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
    
    <?php if ($tokenValido): ?>
    <script>
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
        
        // Mostrar fortaleza de contraseña
        $('#password_nuevo').on('keyup', function() {
            var password = $(this).val();
            var strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[^a-zA-Z0-9]+/)) strength++;
            
            // Remover indicador previo
            $(this).parent().find('.password-strength').remove();
            
            if (password.length > 0) {
                var color = strength < 2 ? 'danger' : (strength < 4 ? 'warning' : 'success');
                var text = strength < 2 ? 'Débil' : (strength < 4 ? 'Media' : 'Fuerte');
                $(this).after('<small class="form-text text-' + color + ' password-strength">Fortaleza: ' + text + '</small>');
            }
        });
    </script>
    <?php endif; ?>
</body>

</html>