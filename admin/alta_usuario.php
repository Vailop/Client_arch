<?php
require_once '../config.php';
require_once '../funciones/crearCarpetaUsuario.php';

session_start();

// Verificar que sea administrador
if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != 1) {
    header("Location: ../login.php");
    exit();
}

$idDesarrollo = isset($_GET['idDesarrollo']) ? (int)$_GET['idDesarrollo'] : 0;

if ($idDesarrollo <= 0) {
    header("Location: ../home_admin.php");
    exit();
}

$conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Obtener info del desarrollo
$sqlDesarrollo = "SELECT IdDesarrollo, Nombre_Desarrollo FROM tbp_desarrollos WHERE IdDesarrollo = ?";
$stmt = $conexion->prepare($sqlDesarrollo);
$stmt->bind_param('i', $idDesarrollo);
$stmt->execute();
$desarrollo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$desarrollo) {
    header("Location: ../home_admin.php");
    exit();
}

$mensajeExito = '';
$mensajeError = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rfc = strtoupper(trim($_POST['rfc']));
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $departamento = trim($_POST['departamento']);
    $metrosCuadrados = (float)$_POST['metros_cuadrados'];
    $precioCompra = (float)$_POST['precio_compra'];
    $fechaFirma = $_POST['fecha_firma'];
    $fechaVigencia = $_POST['fecha_vigencia'];
    $enganche = (float)$_POST['enganche'];
    $montoMensual = (float)$_POST['monto_mensual'];
    $numeroMeses = (int)$_POST['numero_meses'];
    $fechaInicio = $_POST['fecha_inicio'];
    
    // Validaciones
    if (empty($rfc) || empty($nombre) || empty($email) || empty($departamento)) {
        $mensajeError = 'Por favor complete todos los campos obligatorios';
    } else {
        // Verificar que el RFC no exista
        $sqlCheckRFC = "SELECT IdUsuario FROM tbp_usuarios WHERE RFC = ?";
        $stmtCheck = $conexion->prepare($sqlCheckRFC);
        $stmtCheck->bind_param('s', $rfc);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        
        if ($resultCheck->num_rows > 0) {
            $mensajeError = 'El RFC ya está registrado en el sistema';
        } else {
            // Hashear contraseña (por defecto el RFC)
            $passwordHash = password_hash($rfc, PASSWORD_DEFAULT);
            
            // Insertar usuario
            $sqlUsuario = "INSERT INTO tbp_usuarios (RFC, Contrasena, Nombre, Email, Telefono, IdPerfil, RequiereCambioPassword, Estatus, FechaRegistro) 
                           VALUES (?, ?, ?, ?, ?, 2, 1, 1, NOW())";
            $stmtUsuario = $conexion->prepare($sqlUsuario);
            $stmtUsuario->bind_param('sssss', $rfc, $passwordHash, $nombre, $email, $telefono);
            
            if ($stmtUsuario->execute()) {
                $nuevoIdUsuario = $stmtUsuario->insert_id;
                
                // Crear carpetas del usuario
                crearCarpetaUsuario($nuevoIdUsuario);
                
                // Insertar relación usuario-desarrollo
                $sqlRelacion = "INSERT INTO tbr_usuario_desarrollos 
                                (IdUsuario, IdDesarrollo, Dpto, MetrosCuadrados, PrecioCompra, FechaFirma, Vigencia, Enganche, Estatus, FechaRegistro) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
                $stmtRelacion = $conexion->prepare($sqlRelacion);
                $stmtRelacion->bind_param('iisddssd', $nuevoIdUsuario, $idDesarrollo, $departamento, $metrosCuadrados, $precioCompra, $fechaFirma, $fechaVigencia, $enganche);
                
                if ($stmtRelacion->execute()) {
                    // Generar plan de pagos
                    $datosContrato = [
                        'IdUsuario' => $nuevoIdUsuario,
                        'IdDesarrollo' => $idDesarrollo,
                        'Depto' => $departamento,
                        'IdCliente' => $nuevoIdUsuario,
                        'm2inicial' => $metrosCuadrados,
                        'm2actual' => $metrosCuadrados,
                        'Precio_Compraventa' => $precioCompra,
                        'FechaInicio' => $fechaInicio,
                        'MontoMensual' => $montoMensual,
                        'NumeroMensualidades' => $numeroMeses
                    ];

                    $resultadoPagos = generarPagosMensuales($conexion, $datosContrato);

                    if ($resultadoPagos) {
                        $mensajeExito = "Usuario creado exitosamente. Se generaron {$numeroMeses} pagos mensuales.";
                        $_POST = array();
                    } else {
                        $mensajeError = 'Usuario creado pero hubo un error al generar los pagos.';
                    }
                } else {
                    $mensajeError = 'Error al asignar el desarrollo: ' . $stmtRelacion->error;
                }
                
                $stmtRelacion->close();
            } else {
                $mensajeError = 'Error al crear el usuario: ' . $stmtUsuario->error;
            }
            
            $stmtUsuario->close();
        }
        
        $stmtCheck->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Usuario - <?= htmlspecialchars($desarrollo['Nombre_Desarrollo']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    
    <link rel="icon" href="../favicon.png" type="image/x-icon">
    
    <link rel="stylesheet" href="../font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="../font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="../css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="../css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="../css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../css/main.css" />
    
    <style>
        .form-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #145388;
        }
    </style>
</head>

<body id="app-container" class="menu-default show-spinner">
    <!-- NAVBAR -->
    <nav class="navbar fixed-top">
        <div class="d-flex align-items-center navbar-left">
            <a href="desarrollo_detalle.php?id=<?= $idDesarrollo ?>" class="btn btn-outline-light btn-sm">
                <i class="simple-icon-arrow-left"></i> Volver
            </a>
        </div>

        <a class="navbar-logo" href="../home_admin.php">
            <span class="logo d-none d-xs-block"></span>
            <span class="logo-mobile d-block d-xs-none"></span>
        </a>

        <div class="navbar-right">
            <div class="user d-inline-block">
                <button class="btn btn-empty p-0" type="button" data-toggle="dropdown">
                    <span class="name"><?php echo $_SESSION["nombre"]; ?></span>
                    <span style="display: inline-block; width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; color: white; text-align: center; line-height: 35px; font-weight: bold; font-size: 14px; margin-left: 8px;">
                        <?php 
                        $palabras = explode(' ', $_SESSION["nombre"]);
                        echo strtoupper(substr($palabras[0], 0, 1));
                        if (isset($palabras[1])) echo strtoupper(substr($palabras[1], 0, 1));
                        ?>
                    </span>
                </button>

                <div class="dropdown-menu dropdown-menu-right mt-3">
                    <a class="dropdown-item" href="../datos_usuario.php">Mi Cuenta</a>
                    <a class="dropdown-item" href="../cerrar_sesion.php">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1>Alta de Usuario</h1>
                    <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block">
                        <ol class="breadcrumb pt-0">
                            <li class="breadcrumb-item">
                                <a href="../home_admin.php">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="desarrollo_detalle.php?id=<?= $idDesarrollo ?>">
                                    <?= htmlspecialchars($desarrollo['Nombre_Desarrollo']) ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Alta de Usuario</li>
                        </ol>
                    </nav>
                    <div class="separator mb-5"></div>
                </div>
            </div>

            <?php if ($mensajeExito): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> <?= $mensajeExito ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($mensajeError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= $mensajeError ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="POST" action="">
                                <!-- SECCIÓN 1: DATOS PERSONALES -->
                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="iconsminds-user"></i> DATOS PERSONALES
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>RFC <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="rfc" 
                                                       value="<?= isset($_POST['rfc']) ? htmlspecialchars($_POST['rfc']) : '' ?>" 
                                                       required maxlength="13" 
                                                       pattern="[A-Z]{4}\d{6}[A-Z0-9]{3}" 
                                                       placeholder="XAXX010101000">
                                                <small class="form-text text-muted">Esta será su contraseña inicial</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nombre Completo <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="nombre" 
                                                       value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>" 
                                                       required maxlength="100">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" name="email" 
                                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                                                       required maxlength="100">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Teléfono</label>
                                                <input type="tel" class="form-control" name="telefono" 
                                                       value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : '' ?>" 
                                                       maxlength="15" placeholder="5512345678">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECCIÓN 2: ASIGNACIÓN DE DEPARTAMENTO -->
                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="iconsminds-building"></i> ASIGNACIÓN DE DEPARTAMENTO
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Departamento <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="departamento" 
                                                       value="<?= isset($_POST['departamento']) ? htmlspecialchars($_POST['departamento']) : '' ?>" 
                                                       required placeholder="Ej: 201">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>M² Totales</label>
                                                <input type="number" step="0.01" class="form-control" name="metros_cuadrados" 
                                                       value="<?= isset($_POST['metros_cuadrados']) ? htmlspecialchars($_POST['metros_cuadrados']) : '' ?>" 
                                                       placeholder="Ej: 128.5">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Precio Compra</label>
                                                <input type="number" step="0.01" class="form-control" name="precio_compra" 
                                                       value="<?= isset($_POST['precio_compra']) ? htmlspecialchars($_POST['precio_compra']) : '' ?>" 
                                                       placeholder="Ej: 5905785.12">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Fecha Firma Contrato</label>
                                                <input type="date" class="form-control" name="fecha_firma" 
                                                       value="<?= isset($_POST['fecha_firma']) ? htmlspecialchars($_POST['fecha_firma']) : '' ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Vigencia Contrato</label>
                                                <input type="date" class="form-control" name="fecha_vigencia" 
                                                       value="<?= isset($_POST['fecha_vigencia']) ? htmlspecialchars($_POST['fecha_vigencia']) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECCIÓN 3: PLAN DE PAGOS -->
                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="iconsminds-calendar-4"></i> PLAN DE PAGOS
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Enganche</label>
                                                <input type="number" step="0.01" class="form-control" name="enganche" 
                                                       value="<?= isset($_POST['enganche']) ? htmlspecialchars($_POST['enganche']) : '0' ?>" 
                                                       placeholder="Ej: 500000">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Monto Mensual <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control" name="monto_mensual" 
                                                       value="<?= isset($_POST['monto_mensual']) ? htmlspecialchars($_POST['monto_mensual']) : '' ?>" 
                                                       required placeholder="Ej: 20000">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Número de Mensualidades <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" name="numero_meses" 
                                                       value="<?= isset($_POST['numero_meses']) ? htmlspecialchars($_POST['numero_meses']) : '' ?>" 
                                                       required min="1" max="360" placeholder="Ej: 30">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Fecha Primer Pago <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="fecha_inicio" 
                                                       value="<?= isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : '' ?>" 
                                                       required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <a href="desarrollo_detalle.php?id=<?= $idDesarrollo ?>" class="btn btn-outline-secondary">
                                        <i class="simple-icon-close"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="simple-icon-check"></i> Crear Usuario y Generar Pagos
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="page-footer">
        <div class="footer-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="mb-0 text-muted">© 2025 ARCHANDEL</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="../js/vendor/jquery-3.3.1.min.js"></script>
    <script src="../js/vendor/bootstrap.bundle.min.js"></script>
    <script src="../js/vendor/perfect-scrollbar.min.js"></script>
    <script src="../js/dore.script.js"></script>
    <script src="../js/scripts.js"></script>
</body>
</html>

<?php $conexion->close(); ?>