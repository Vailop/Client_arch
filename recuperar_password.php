<?php
require 'config.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje = '';
$error = '';
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rfc = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    if (empty($rfc) || empty($email)) {
        $error = 'Por favor complete todos los campos';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico inválido';
    } else {
        $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        
        // Verificar que exista el usuario con ese RFC y email
        $sql = "SELECT IdUsuario, Nombre FROM tbp_usuarios 
                WHERE RFC = ? AND Correo_electronico = ? AND Estatus = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ss', $rfc, $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            $fechaCreacion = date('Y-m-d H:i:s');
            $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Guardar token en BD
            $sqlToken = "INSERT INTO tbr_password_reset (IdUsuario, Token, FechaCreacion, FechaExpiracion) 
                         VALUES (?, ?, ?, ?)";
            $stmtToken = $conexion->prepare($sqlToken);
            $stmtToken->bind_param('isss', $usuario['IdUsuario'], $token, $fechaCreacion, $fechaExpiracion);
            
            if ($stmtToken->execute()) {
                // Construir URL de reset
                $urlReset = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                
                // Preparar email con PHPMailer
                $mail = new PHPMailer(true);
                
                try {
                    // Configuración SMTP
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com'; // Cambia según tu proveedor
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'contactoarchandel@gmail.com'; // Tu email
                    $mail->Password   = 'qixbabuxznenmjep'; // Contraseña de aplicación
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';
                    
                    // Remitente y destinatario
                    $mail->setFrom('noreply@archandel.com', 'Archandel');
                    $mail->addAddress($email, $usuario['Nombre']);
                    
                    // Contenido del email
                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperación de contraseña - Archandel';
                    
                    // Plantilla HTML
                    $año = date('Y');
                    $nombreUsuario = htmlspecialchars($usuario['Nombre']);
                    
                    $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { margin:0; padding:0; background:#000000; }
        .container { max-width:650px; margin:0 auto; }
    </style>
</head>
<body style="margin:0; padding:0; background:#000000;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#000000; padding:40px 0;">
        <tr>
            <td align="center">
                <table width="650" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:20px 20px 0 0;">
                    <!-- Logo -->
                    <tr>
                        <td style="padding:30px; text-align:center;">
                            <img src="https://archandel.com/img/logos/archandel_ng.png" width="200" alt="Archandel" />
                        </td>
                    </tr>
                    <!-- Contenido -->
                    <tr>
                        <td style="padding:50px 40px;">
                            <h2 style="color:#000000; font-size:28px; text-align:center; margin:0 0 20px;">Recuperación de Contraseña</h2>
                            <p style="color:#5d5c5c; font-size:16px; line-height:24px; margin:0 0 20px;">Hola <strong>{$nombreUsuario}</strong>,</p>

                            <p style="color:#5d5c5c; font-size:14px; line-height:22px; margin:0 0 20px;">Recibimos una solicitud para restablecer tu contraseña. Si no realizaste esta solicitud, puedes ignorar este correo.</p>
                            <p style="text-align:center; margin:30px 0;"><a href="{$urlReset}" style="display:inline-block; background:#000000; color:#ffffff; font-size:16px; font-weight:bold; text-decoration:none; padding:15px 40px; border-radius:25px;">Restablecer Contraseña</a></p>
                            <p style="color:#5d5c5c; font-size:12px; line-height:20px; margin:20px 0 10px;">O copia y pega este enlace:</p>
                            <p style="background:#f4f4f4; padding:15px; border-radius:5px; word-break:break-all; font-size:12px;"><a href="{$urlReset}" style="color:#1e52bd;">{$urlReset}</a></p>
                            
                            <div style="margin-top:30px; padding-top:20px; border-top:1px solid #ebebeb;">
                                <p style="color:#a1a1a1; font-size:13px; margin:10px 0;"><strong style="color:#000000;">⚠️ Importante:</strong></p>
                                <ul style="color:#a1a1a1; font-size:13px; line-height:20px; margin:10px 0; padding-left:20px;">
                                    <li>Solo puede ser usado una vez.</li>
                                    <li>Este enlace expirará en <strong>1 hora</strong>.</li>
                                    <li>Si no solicitaste este cambio, tu cuenta está segura.</li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding:30px 40px; border-top:1px solid #ebebeb; text-align:center;">
                            <p style="color:#a1a1a1; font-size:12px; margin:0 0 10px;">
                                ¿Necesitas ayuda? Contáctanos en <a href="mailto:soporte@archandel.com" style="color:#000000;">soporte@archandel.com</a>
                            </p>
                            <p style="color:#000000; font-size:11px; margin:0;">
                                &copy; {$año} Archandel. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
                    
                    $mail->AltBody = "Hola {$nombreUsuario},\n\nRecibimos una solicitud para restablecer tu contraseña.\n\nVisita este enlace: {$urlReset}\n\nEste enlace expirará en 1 hora.\n\n© {$año} Archandel";
                    
                    $mail->send();
                    $enviado = true;
                    $mensaje = 'Se ha enviado un correo con instrucciones para restablecer tu contraseña.';
                    
                } catch (Exception $e) {
                    $error = 'Error al enviar el correo. Intenta nuevamente.';
                    error_log("Error PHPMailer: {$mail->ErrorInfo}");
                }
            } else {
                $error = 'Error al procesar la solicitud. Intenta nuevamente.';
            }
            
            $stmtToken->close();
        } else {
            // Por seguridad, no indicamos si el usuario existe o no
            $enviado = true;
            $mensaje = 'Si los datos son correctos, recibirás un correo con instrucciones.';
        }
        
        $stmt->close();
        $conexion->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel - Recuperar Contraseña</title>
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
                            
                            <?php if ($enviado): ?>
                                <div class="text-center">
                                    <i class="iconsminds-mail-send d-block mb-3" style="font-size: 48px; color: #145388;"></i>
                                    <h6 class="mb-4">Revisa tu correo</h6>
                                    <div class="alert alert-success">
                                        <?php echo $mensaje; ?>
                                    </div>
                                    <a href="login.php" class="btn btn-primary btn-block btn-lg">Volver al inicio</a>
                                </div>
                            <?php else: ?>
                                <h6 class="mt-4 mb-5">¿Olvidaste tu contraseña?</h6>
                                <p class="text-muted mb-4">Ingresa tu RFC y correo electrónico para recibir instrucciones.</p>

                                <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="simple-icon-exclamation"></i> <?php echo $error; ?>
                                </div>
                                <?php endif; ?>

                                <form method="post" action="recuperar_password.php">
                                    <label class="form-group has-float-label mb-4">
                                        <input type="text" 
                                               class="form-control" 
                                               id="username" 
                                               name="username" 
                                               placeholder="" 
                                               required />
                                        <span>RFC</span>
                                    </label>

                                    <label class="form-group has-float-label mb-4">
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               placeholder="" 
                                               required />
                                        <span>Correo Electrónico</span>
                                    </label>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="login.php" class="btn btn-outline-secondary btn-lg">Volver</a>
                                        <button class="btn btn-primary btn-lg btn-shadow" type="submit">Enviar</button>
                                    </div>
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
</body>

</html>