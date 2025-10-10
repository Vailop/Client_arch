<?php
require_once '../config.php';
require_once '../funciones/crearCarpetaUsuario.php';
require_once '../funciones/generar_pagos.php';
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
    $precioM2Inicial = (float)$_POST['precio_m2_inicial'];
    $precioCompra = (float)$_POST['precio_compra'];
    $fechaFirma = $_POST['fecha_firma'];
    $enganche = (float)$_POST['enganche'];
    $montoMensual = (float)$_POST['monto_mensual'];
    $numeroMeses = (int)$_POST['numero_meses'];
    $fechaInicio = $_POST['fecha_inicio'];
    
    // Calcular vigencia: fecha_firma + numero_meses
    $fechaVigenciaCalculada = '';
    if (!empty($fechaFirma) && $numeroMeses > 0) {
        $fechaVigenciaCalculada = date('Y-m-d', strtotime($fechaFirma . " +{$numeroMeses} months"));
    }
    
    // Procesar archivos
    $archivoComprobanteInicial = null;
    $archivoPlano = null;
    
    if (isset($_FILES['comprobante_inicial']) && $_FILES['comprobante_inicial']['error'] === UPLOAD_ERR_OK) {
        $archivoComprobanteInicial = $_FILES['comprobante_inicial'];
    }
    
    if (isset($_FILES['plano_departamento']) && $_FILES['plano_departamento']['error'] === UPLOAD_ERR_OK) {
        $archivoPlano = $_FILES['plano_departamento'];
    }
    
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
                
                // Crear carpetas del desarrollo para este usuario
                crearCarpetaDesarrollo($nuevoIdUsuario, $idDesarrollo, $departamento);
                
                // Guardar archivos
                $rutaComprobanteInicial = null;
                $rutaPlano = null;
                
                if ($archivoComprobanteInicial) {
                    $resultadoComprobante = guardarComprobanteInicial($nuevoIdUsuario, $idDesarrollo, $departamento, $archivoComprobanteInicial);
                    if ($resultadoComprobante['success']) {
                        $rutaComprobanteInicial = $resultadoComprobante['ruta_relativa'];
                    }
                }
                
                if ($archivoPlano) {
                    $resultadoPlano = guardarPlano($nuevoIdUsuario, $idDesarrollo, $departamento, $archivoPlano);
                    if ($resultadoPlano['success']) {
                        $rutaPlano = $resultadoPlano['ruta_relativa'];
                    }
                }
                
                // Insertar relación usuario-desarrollo
                $sqlRelacion = "INSERT INTO tbr_usuario_desarrollos 
                                (IdUsuario, IdDesarrollo, Dpto, File_Comprobante, File_Planos, File_Avance_Obra, M2inicial, Fecha_Firma, Vigencia, Estatus) 
                                VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?, 1)";
                $stmtRelacion = $conexion->prepare($sqlRelacion);
                $stmtRelacion->bind_param('iissdss', $nuevoIdUsuario, $idDesarrollo, $departamento, $rutaComprobanteInicial, $rutaPlano, $metrosCuadrados, $fechaFirma, $fechaVigenciaCalculada);
                
                if ($stmtRelacion->execute()) {
                    // PASO 1: Insertar el ENGANCHE como primer pago
                    if ($enganche > 0) {
                        $sqlEnganche = "INSERT INTO tbr_pagos (
                            IdUsuario, IdDesarrollo, Dpto, IdCliente, 
                            m2inicial, m2actual, Precio_Compraventa, Precio_Actual,
                            FechaPago, Monto, Estatus, Concepto, CreatedAt
                        ) VALUES (?, ?, ?, NULL, ?, NULL, ?, NULL, ?, ?, 2, 'Enganche', NOW())";
                        
                        $stmtEnganche = $conexion->prepare($sqlEnganche);
                        $stmtEnganche->bind_param('iisddsd', 
                            $nuevoIdUsuario, $idDesarrollo, $departamento,
                            $precioM2Inicial, $precioCompra,
                            $fechaFirma, $enganche
                        );
                        $stmtEnganche->execute();
                        $idPagoEnganche = $stmtEnganche->insert_id;
                        $stmtEnganche->close();
                        
                        // Guardar comprobante del enganche
                        if ($rutaComprobanteInicial && $idPagoEnganche > 0) {
                            $sqlUpdateComprobante = "INSERT INTO tbr_comprobantes_pago 
                                                     (IdPago, IdUsuario, IdDesarrollo, Dpto, ArchivoComprobante, Estatus, FechaSubida) 
                                                     VALUES (?, ?, ?, ?, ?, 'Aprobado', NOW())";
                            $stmtComp = $conexion->prepare($sqlUpdateComprobante);
                            $stmtComp->bind_param('iiiss', $idPagoEnganche, $nuevoIdUsuario, $idDesarrollo, $departamento, $rutaComprobanteInicial);
                            $stmtComp->execute();
                            $stmtComp->close();
                        }
                        
                        $precioRestante = $precioCompra - $enganche;
                    } else {
                        $precioRestante = $precioCompra;
                    }
                    
                    // PASO 2: Generar pagos mensuales
                    $datosContrato = [
                        'IdUsuario' => $nuevoIdUsuario,
                        'IdDesarrollo' => $idDesarrollo,
                        'Depto' => $departamento,
                        'm2inicial' => $precioM2Inicial,
                        'Precio_Compraventa' => $precioRestante,
                        'FechaInicio' => $fechaInicio,
                        'MontoMensual' => $montoMensual,
                        'NumeroMensualidades' => $numeroMeses
                    ];
                    
                    $resultadoPagos = generarPagosMensuales($conexion, $datosContrato);
                    
                    if ($resultadoPagos === true) {
                        $totalPagos = $enganche > 0 ? $numeroMeses + 1 : $numeroMeses;
                        $mensajeExito = "Usuario creado exitosamente. Se generaron {$totalPagos} pagos" . ($enganche > 0 ? " (1 enganche + {$numeroMeses} mensualidades)" : "") . ". Contraseña inicial: {$rfc}";
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
    <title>Panel Administrador - Archandel</title>
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
            margin-bottom: 1rem;
        }
    </style>
</head>

<body id="app-container" class="menu-default show-spinner">
    <!-- NAVBAR -->
    <nav class="navbar fixed-top">
        <div class="d-flex align-items-center navbar-left">
            <a href="#" class="menu-button d-none d-md-block">
                <svg class="main" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 17">
                    <rect x="0.48" y="0.5" width="7" height="1" />
                    <rect x="0.48" y="7.5" width="7" height="1" />
                    <rect x="0.48" y="15.5" width="7" height="1" />
                </svg>
                <svg class="sub" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 17">
                    <rect x="1.56" y="0.5" width="16" height="1" />
                    <rect x="1.56" y="7.5" width="16" height="1" />
                    <rect x="1.56" y="15.5" width="16" height="1" />
                </svg>
            </a>

            <a href="#" class="menu-button-mobile d-xs-block d-sm-block d-md-none">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26 17">
                    <rect x="0.5" y="0.5" width="25" height="1" />
                    <rect x="0.5" y="7.5" width="25" height="1" />
                    <rect x="0.5" y="15.5" width="25" height="1" />
                </svg>
            </a>
        </div>

        <a class="navbar-logo" href="home_admin.php">
            <span class="logo d-none d-xs-block"></span>
            <span class="logo-mobile d-block d-xs-none"></span>
        </a>

        <div class="navbar-right">
            <div class="header-icons d-inline-block align-middle">
                <!-- Dark Mode -->
                <div class="d-none d-md-inline-block align-text-bottom mr-3">
                    <div class="custom-switch custom-switch-primary-inverse custom-switch-small pl-1">
                        <input class="custom-switch-input" id="switchDark" type="checkbox" checked>
                        <label class="custom-switch-btn" for="switchDark"></label>
                    </div>
                </div>

                <!-- Fullscreen -->
                <button class="header-icon btn btn-empty d-none d-sm-inline-block" type="button" id="fullScreenButton">
                    <i class="simple-icon-size-fullscreen"></i>
                    <i class="simple-icon-size-actual"></i>
                </button>
            </div>
            
            <!-- Usuario -->
            <div class="user d-inline-block">
                <button class="btn btn-empty p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                    <a class="dropdown-item" href="datos_usuario.php">Mi Cuenta</a>
                    <a class="dropdown-item" href="cerrar_sesion.php">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="menu">
        <div class="main-menu">
            <div class="scroll">
                <ul class="list-unstyled">
                    <li>
                        <a href="#dashboard"><i class="iconsminds-hotel"></i><span>Desarrollos</span></a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- SUB MENU -->
        <div class="sub-menu">
            <div class="scroll">
                <ul class="list-unstyled" data-link="dashboard">
                    <?php
                        $sqlMenuDesarrollos = "SELECT IdDesarrollo, Nombre_Desarrollo 
                                            FROM tbp_desarrollos 
                                            WHERE Estatus = 1 
                                            ORDER BY Nombre_Desarrollo ASC";
                        $menuDesarrollos = $conexion->query($sqlMenuDesarrollos);
                        
                        if ($menuDesarrollos && $menuDesarrollos->num_rows > 0):
                            while ($devMenu = $menuDesarrollos->fetch_assoc()):
                                $isActive = ($devMenu['IdDesarrollo'] == $idDesarrollo) ? 'true' : 'false';
                                $showClass = ($devMenu['IdDesarrollo'] == $idDesarrollo) ? 'show' : '';
                    ?>
                        <li>
                            <a href="#" data-toggle="collapse" data-target="#collapseDev<?= $devMenu['IdDesarrollo'] ?>" aria-expanded="<?= $isActive ?>" aria-controls="collapseDev<?= $devMenu['IdDesarrollo'] ?>" class="rotate-arrow-icon <?= $isActive == 'true' ? '' : 'collapsed' ?>"><i class="simple-icon-arrow-down"></i><span class="d-inline-block"><?= htmlspecialchars($devMenu['Nombre_Desarrollo']) ?></span></a>
                            <div id="collapseDev<?= $devMenu['IdDesarrollo'] ?>" class="collapse <?= $showClass ?>">
                                <ul class="list-unstyled inner-level-menu">
                                    <li>
                                        <a href="alta_usuario.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-add-user"></i><span class="d-inline-block">Alta de Usuario</span></a>
                                    </li>
                                    <li>
                                        <a href="usuarios.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-business-man-woman"></i><span class="d-inline-block">Administrar Usuarios</span></a>
                                    </li>
                                    <li>
                                        <a href="validar_comprobantes.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-file-edit"></i><span class="d-inline-block">Validar Comprobantes</span></a>
                                    </li>
                                    <li>
                                        <a href="plan_pagos.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-calendar-4"></i><span class="d-inline-block">Plan de Pagos</span></a>
                                    </li>
                                    <li>
                                        <a href="actualizar_avance.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-bar-chart-4"></i><span class="d-inline-block">Actualizar Avance</span></a>
                                    </li>
                                    <li>
                                        <a href="actualizar_plusvalia.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-line-chart-1"></i><span class="d-inline-block">Actualizar Plusvalía</span></a>
                                    </li>
                                    <li>
                                        <a href="archivos.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-folder-cloud"></i><span class="d-inline-block">Gestión de Archivos</span></a>
                                    </li>
                                    <li>
                                        <a href="reportes.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-file-clipboard"></i><span class="d-inline-block">Reportes</span></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    <?php 
                            endwhile;
                        else:
                    ?>
                        <li>
                            <i class="simple-icon-info"></i><span class="d-inline-block text-muted">Sin desarrollos</span>
                        </li>
                    <?php endif; ?>
                    
                    <li class="mt-3">
                        <a href="#" onclick="alert('Próximamente: Crear Desarrollo'); return false;"><i class="simple-icon-plus"></i><span class="d-inline-block">Nuevo Desarrollo</span></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1><a href="desarrollo_detalle.php?id=<?= $idDesarrollo ?>"><?= htmlspecialchars($desarrollo['Nombre_Desarrollo']) ?></a></h1>
                    <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
                        <ol class="breadcrumb pt-0">
                            <li class="breadcrumb-item active">Alta de Usuario</li>
                            <li class="breadcrumb-item"><a href="../home_admin.php">Dashboard</a></li>                            
                        </ol>
                    </nav>
                    <div class="separator mb-5"></div>
                </div>
            </div>
        </div>

        <?php if ($mensajeExito): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> <?= $mensajeExito ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>

        <?php if ($mensajeError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= $mensajeError ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>

         <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <!-- SECCIÓN 1: DATOS PERSONALES -->
                            <div class="form-section">
                                <h5 class="mb-4"><i class="iconsminds-business-man-woman"></i> DATOS PERSONALES</h5>
                                
                                <div class="row">
                                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                                        <div class="form-group">
                                            <label>Nombre Completo <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nombre" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>" required maxlength="100">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                                        <div class="form-group">
                                            <label>RFC <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="rfc" value="<?= isset($_POST['rfc']) ? htmlspecialchars($_POST['rfc']) : '' ?>" required maxlength="13" style="text-transform: uppercase;" placeholder="XAXX010101000">
                                            <small class="form-text text-muted">Esta será su contraseña inicial</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                                        <div class="form-group">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required maxlength="100">
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                                        <div class="form-group">
                                            <label>Teléfono</label>
                                            <input type="tel" class="form-control" name="telefono" value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : '' ?>" maxlength="15" placeholder="5512345678">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- SECCIÓN 2: ASIGNACIÓN DE DEPARTAMENTO -->
                            <div class="form-section">
                                <h5 class="form-section-title"><i class="iconsminds-building"></i> ASIGNACIÓN DE DEPARTAMENTO</h5>
                                
                                <div class="row">
                                    <div class="col-12 col-md-4 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Departamento <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="departamento" value="<?= isset($_POST['departamento']) ? htmlspecialchars($_POST['departamento']) : '' ?>" required placeholder="Ej: 201">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Precio de Compraventa <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="precio_compra" value="<?= isset($_POST['precio_compra']) ? htmlspecialchars($_POST['precio_compra']) : '' ?>" required placeholder="Ej: 5905785.12">
                                            <small class="form-text text-muted">Precio total del departamento</small>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Precio por M² Inicial</label>
                                            <input type="number" step="0.01" class="form-control" name="precio_m2_inicial" value="<?= isset($_POST['precio_m2_inicial']) ? htmlspecialchars($_POST['precio_m2_inicial']) : '' ?>" placeholder="Ej: 46075.66">
                                            <small class="form-text text-muted">Precio por m² al momento de la compra</small>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>M² Totales</label>
                                            <input type="number" step="0.01" class="form-control" name="metros_cuadrados" value="<?= isset($_POST['metros_cuadrados']) ? htmlspecialchars($_POST['metros_cuadrados']) : '' ?>" placeholder="Ej: 128.5">
                                            <small class="form-text text-muted">Superficie total construida</small>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Fecha Firma Contrato</label>
                                            <input type="date" class="form-control" name="fecha_firma" value="<?= isset($_POST['fecha_firma']) ? htmlspecialchars($_POST['fecha_firma']) : '' ?>">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Enganche</label>
                                            <input type="number" step="0.01" class="form-control" name="enganche" value="<?= isset($_POST['enganche']) ? htmlspecialchars($_POST['enganche']) : '0' ?>" placeholder="Ej: 500000">
                                            <small class="form-text text-muted">Primer Pago</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-6 col-lg-4 mb-3">
                                        <div class="form-group">
                                            <label>Comprobante del Enganche</label>
                                            <input type="file" class="form-control" name="comprobante_inicial" accept=".pdf,.jpg,.jpeg,.png">
                                            <small class="form-text text-muted">PDF, JPG o PNG. Máximo 5MB</small>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-4 mb-3">
                                        <div class="form-group">
                                            <label>Plano del Departamento</label>
                                            <input type="file" class="form-control" name="plano_departamento" accept=".pdf,.jpg,.jpeg,.png">
                                            <small class="form-text text-muted">PDF, JPG o PNG. Máximo 5MB</small>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Escrituración</label>
                                            <input type="number" step="0.01" class="form-control" name="escrituracion" value="<?= isset($_POST['escrituracion']) ? htmlspecialchars($_POST['escrituracion']) : '0' ?>" placeholder="Ej: 500000">
                                            <small class="form-text text-muted">Ultimo Pago</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-md-6 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Fecha Firma De Escrituración</label>
                                            <input type="date" class="form-control" name="fecha_escrituracion" value="<?= isset($_POST['fecha_escrituracion']) ? htmlspecialchars($_POST['fecha_escrituracion']) : '' ?>">
                                        </div>
                                    </div>
                                </div>                                
                            </div>

                            <!-- SECCIÓN 3: PLAN DE PAGOS -->
                            <div class="form-section">
                                <h5 class="form-section-title"><i class="iconsminds-calendar-4"></i> PLAN DE PAGOS</h5>
                                
                                <div class="row">
                                    <div class="col-12 col-md-6 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Fecha de Pago Mensual<span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="fecha_inicio" value="<?= isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : '' ?>" required>
                                            <small class="form-text text-muted">Los pagos se generarán mensualmente a partir de esta fecha</small>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Monto Mensual <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="monto_mensual" value="<?= isset($_POST['monto_mensual']) ? htmlspecialchars($_POST['monto_mensual']) : '' ?>" required placeholder="Ej: 20000">
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-md-6 col-lg-2 mb-3">
                                        <div class="form-group">
                                            <label>Número de Mensualidades <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="numero_meses" value="<?= isset($_POST['numero_meses']) ? htmlspecialchars($_POST['numero_meses']) : '' ?>" required min="1" max="360" placeholder="Ej: 30">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <a href="desarrollo_detalle.php?id=<?= $idDesarrollo ?>" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Crear Usuario y Generar Pagos</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
         </div>    

    </main>

    <footer class="page-footer">
        <div class="footer-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <p class="mb-0 text-muted">© 2025 ARCHANDEL. Todos los derechos reservados.</p>
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