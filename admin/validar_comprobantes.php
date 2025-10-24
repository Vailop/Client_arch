<?php
require_once '../config.php';
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

// Procesar aprobación/rechazo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $idComprobante = (int)$_POST['idComprobante'];
    $accion = $_POST['accion']; // 'aprobar' o 'rechazar'
    $observaciones = trim($_POST['observaciones_admin'] ?? '');
    
    if ($idComprobante > 0 && in_array($accion, ['aprobar', 'rechazar'])) {
        $conexion->begin_transaction();
        
        try {
            // Obtener datos del comprobante
            $sqlComprobante = "SELECT c.IdComprobante, c.IdPago, c.IdUsuario, c.MontoComprobante, p.Monto as MontoTotal, p.Estatus as EstatusPago, u.Correo_electronico, u.Nombre as NombreUsuario
                              FROM tbr_comprobantes_pago c
                              INNER JOIN tbr_pagos p ON c.IdPago = p.IdPago
                              INNER JOIN tbp_usuarios u ON c.IdUsuario = u.IdUsuario
                              WHERE c.IdComprobante = ? AND c.IdDesarrollo = ?";
            
            $stmtComp = $conexion->prepare($sqlComprobante);
            $stmtComp->bind_param('ii', $idComprobante, $idDesarrollo);
            $stmtComp->execute();
            $comprobante = $stmtComp->get_result()->fetch_assoc();
            $stmtComp->close();
            
            if (!$comprobante) {
                throw new Exception('Comprobante no encontrado');
            }
            
            if ($accion === 'aprobar') {
                // Actualizar estatus del comprobante
                $sqlUpdate = "UPDATE tbr_comprobantes_pago 
                             SET Estatus = 'Aprobado', ObservacionesAdmin = ?, FechaValidacion = NOW()
                             WHERE IdComprobante = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param('si', $observaciones, $idComprobante);
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                // Verificar si todos los comprobantes del pago están aprobados
                $sqlVerificar = "SELECT SUM(MontoComprobante) as TotalAprobado, COUNT(*) as TotalComprobantes, SUM(CASE WHEN Estatus = 'Aprobado' THEN 1 ELSE 0 END) as ComprobantesAprobados
                                FROM tbr_comprobantes_pago
                                WHERE IdPago = ?";
                
                $stmtVer = $conexion->prepare($sqlVerificar);
                $stmtVer->bind_param('i', $comprobante['IdPago']);
                $stmtVer->execute();
                $verificacion = $stmtVer->get_result()->fetch_assoc();
                $stmtVer->close();
                
                // Si todos están aprobados Y la suma coincide, marcar pago como pagado
                if ($verificacion['ComprobantesAprobados'] == $verificacion['TotalComprobantes'] &&
                    abs($verificacion['TotalAprobado'] - $comprobante['MontoTotal']) < 0.01) {
                    
                    $sqlPago = "UPDATE tbr_pagos 
                               SET Estatus = 2, 
                                   UpdatedAt = NOW()
                               WHERE IdPago = ?";
                    $stmtPago = $conexion->prepare($sqlPago);
                    $stmtPago->bind_param('i', $comprobante['IdPago']);
                    $stmtPago->execute();
                    $stmtPago->close();
                    
                    $mensajeExito = 'Comprobante aprobado. El pago se ha marcado como completado.';
                } else {
                    $mensajeExito = 'Comprobante aprobado correctamente.';
                }
                
                // TODO: Enviar email de aprobación al cliente
                
            } else { // rechazar
                if (empty($observaciones)) {
                    throw new Exception('Debe proporcionar un motivo de rechazo');
                }
                
                $sqlUpdate = "UPDATE tbr_comprobantes_pago 
                             SET Estatus = 'Rechazado', 
                                 ObservacionesAdmin = ?,
                                 FechaValidacion = NOW()
                             WHERE IdComprobante = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param('si', $observaciones, $idComprobante);
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                $mensajeExito = 'Comprobante rechazado. El cliente podrá subir uno nuevo.';
                
                // TODO: Enviar email de rechazo al cliente con observaciones
            }
            
            $conexion->commit();
            
        } catch (Exception $e) {
            $conexion->rollback();
            $mensajeError = $e->getMessage();
        }
    }
}

// Obtener comprobantes pendientes
$sqlComprobantes = "SELECT 
    c.IdComprobante,
    c.IdPago,
    c.NumeroComprobante,
    c.MontoComprobante,
    c.ArchivoComprobante,
    c.Referencia,
    c.FechaPagoReal,
    c.FechaSubida,
    c.ObservacionesUsuario,
    c.Dpto,
    u.IdUsuario,
    u.Nombre as NombreCliente,
    u.RFC,
    u.Correo_electronico,
    u.Telefono,
    p.Monto as MontoTotal,
    p.FechaPago,
    p.Concepto,
    (SELECT COUNT(*) FROM tbr_comprobantes_pago WHERE IdPago = c.IdPago) as TotalComprobantes,
    (SELECT SUM(MontoComprobante) FROM tbr_comprobantes_pago WHERE IdPago = c.IdPago AND Estatus = 'Aprobado') as MontoAprobado
FROM tbr_comprobantes_pago c
INNER JOIN tbp_usuarios u ON c.IdUsuario = u.IdUsuario
INNER JOIN tbr_pagos p ON c.IdPago = p.IdPago
WHERE c.IdDesarrollo = ? AND c.Estatus = 'Pendiente'
ORDER BY c.FechaSubida ASC";

$stmtComp = $conexion->prepare($sqlComprobantes);
$stmtComp->bind_param('i', $idDesarrollo);
$stmtComp->execute();
$comprobantes = $stmtComp->get_result();
$stmtComp->close();

// Función para obtener ruta completa del archivo
function obtenerRutaArchivo($archivo, $desarrollo) {
    return "/desarrollos/" . slug($desarrollo) . "/" . date('Y') . "/" . sprintf('COM%02d', date('m')) . "/comprobantes/" . $archivo;
}

function slug($s) {
    $s = iconv('UTF-8','ASCII//TRANSLIT',$s);
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/','_', $s);
    return trim($s,'_');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Validar Comprobantes - Archandel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    
    <link rel="icon" href="../favicon.png" type="image/x-icon">
    
    <link rel="stylesheet" href="../font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="../font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="../css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="../css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="../css/main.css" />

    <style>
        .archivo-icono-container {
            transition: all 0.3s ease;
        }

        .archivo-icono-container:hover {
            background: #e9ecef !important;
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        /* Modal */
        #contenedorImagen img {
            background: white;
            padding: 1rem;
        }

        #contenedorImagen::-webkit-scrollbar {
            width: 8px;
        }

        #contenedorImagen::-webkit-scrollbar-track {
            background: #2d3436;
        }

        #contenedorImagen::-webkit-scrollbar-thumb {
            background: #636e72;
            border-radius: 4px;
        }

        #contenedorImagen::-webkit-scrollbar-thumb:hover {
            background: #b2bec3;
        }
    </style>
</head>

<body id="app-container" class="menu-default show-spinner">
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
                            <li class="breadcrumb-item active">Validar Comprobantes</li>
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

        <?php if ($comprobantes->num_rows === 0): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="iconsminds-check" style="font-size: 4rem; color: #28a745; opacity: 0.3;"></i>
                            <h3 class="mt-3">¡Todo al día!</h3>
                            <p class="text-muted">No hay comprobantes pendientes de validación</p>
                            <a href="desarrollo_detalle.php?id=<?= $idDesarrollo ?>" class="btn btn-outline-primary mt-3">
                                <i class="simple-icon-arrow-left"></i> Volver al Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="mb-4">
                        <span class="badge badge-pill badge-primary" style="font-size: 1.1rem; padding: 0.5rem 1rem;">
                            <?= $comprobantes->num_rows ?> Comprobante<?= $comprobantes->num_rows != 1 ? 's' : '' ?> Pendiente<?= $comprobantes->num_rows != 1 ? 's' : '' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <?php while ($comp = $comprobantes->fetch_assoc()): 
                    $rutaArchivo = obtenerRutaArchivo($comp['ArchivoComprobante'], $desarrollo['Nombre_Desarrollo']);
                    $extension = strtolower(pathinfo($comp['ArchivoComprobante'], PATHINFO_EXTENSION));
                    $esPDF = ($extension === 'pdf');
                    $montoAprobado = $comp['MontoAprobado'] ?? 0;
                    $montoRestante = $comp['MontoTotal'] - $montoAprobado;
                ?>
                <div class="col-lg-4 col-12 mb-4">
                    <div class="card comprobante-card">
                        <div class="card-body">
                            <!-- HEADER -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($comp['Concepto']) ?></h5>
                                    <small class="text-muted">Dpto. <?= htmlspecialchars($comp['Dpto']) ?> - Comprobante #<?= $comp['NumeroComprobante'] ?></small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-monto badge-primary">$<?= number_format($comp['MontoComprobante'], 2) ?></span>
                                    <br>
                                    <small class="text-muted">de $<?= number_format($comp['MontoTotal'], 2) ?></small>
                                </div>
                            </div>

                            <!-- INFORMACIÓN DEL CLIENTE -->
                            <div class="cliente-info">
                                <div class="row">
                                    <div class="col-md-8">
                                        <small class="text-muted d-block">Cliente</small>
                                        <strong><?= htmlspecialchars($comp['NombreCliente']) ?></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">RFC</small>
                                        <strong><?= htmlspecialchars($comp['RFC']) ?></strong>
                                    </div>
                                    <div class="col-md-8 mt-2">
                                        <small class="text-muted d-block">Email</small>
                                        <strong><?= htmlspecialchars($comp['Correo_electronico']) ?></strong>
                                    </div>
                                    <div class="col-md-4 mt-2">
                                        <small class="text-muted d-block">Teléfono</small>
                                        <strong><?= htmlspecialchars($comp['Telefono']) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- DETALLES DEL PAGO -->
                            <div class="row mb-3">
                                <div class="col-3 mt-2">
                                    <small class="text-muted d-block">Fecha límite</small>
                                    <strong><?= date('d/m/Y', strtotime($comp['FechaPago'])) ?></strong>
                                </div>
                                <div class="col-3 mt-2">
                                    <small class="text-muted d-block">Subido el</small>
                                    <strong><?= date('d/m/Y', strtotime($comp['FechaSubida'])) ?></strong>
                                </div>
                                <?php if ($comp['Referencia']): ?>
                                <div class="col-3 mt-2">
                                    <small class="text-muted d-block">Referencia</small>
                                    <strong><?= htmlspecialchars($comp['Referencia']) ?></strong>
                                </div>
                                <?php endif; ?>
                                <?php if ($comp['FechaPagoReal']): ?>
                                <div class="col-3 mt-2">
                                    <small class="text-muted d-block">Fecha de pago real</small>
                                    <strong><?= date('d/m/Y', strtotime($comp['FechaPagoReal'])) ?></strong>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- PROGRESO DEL PAGO -->
                            <?php if ($comp['TotalComprobantes'] > 1): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Progreso del pago</small>
                                    <small class="text-muted">$<?= number_format($montoAprobado, 2) ?> de $<?= number_format($comp['MontoTotal'], 2) ?></small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= ($montoAprobado / $comp['MontoTotal']) * 100 ?>%"></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- COMENTARIOS DEL CLIENTE -->
                            <?php if ($comp['ObservacionesUsuario']): ?>
                            <div class="alert alert-info mb-3">
                                <small class="text-muted d-block mb-1"><strong>Comentarios del cliente:</strong></small>
                                <?= nl2br(htmlspecialchars($comp['ObservacionesUsuario'])) ?>
                            </div>
                            <?php endif; ?>

                            <!-- PREVIEW DEL ARCHIVO -->
                            <div class="text-center mb-3">
                                <div class="archivo-icono-container" style="background: #f8f9fa; padding: 2rem; border-radius: 0.5rem; cursor: pointer;" onclick="verArchivoCompleto('<?= $rutaArchivo ?>', '<?= $extension ?>')">
                                    <?php if ($esPDF): ?>
                                        <i class="iconsminds-paper" style="font-size: 5rem;"></i>
                                        <div class="mt-3">
                                            <strong class="d-block">PDF</strong>
                                            <small class="text-muted">Click para ampliar</small>
                                        </div>
                                    <?php else: ?>
                                        <i class="iconsminds-photo" style="font-size: 5rem;"></i>
                                        <div class="mt-3">
                                            <strong class="d-block">IMAGEN</strong>
                                            <small class="text-muted">Click para ampliar</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- BOTONES DE ACCIÓN -->
                            <form method="POST" class="d-flex justify-content-between">
                                <input type="hidden" name="idComprobante" value="<?= $comp['IdComprobante'] ?>">
                                
                                <button type="button" class="btn btn-outline-secondary" onclick="rechazarComprobante(<?= $comp['IdComprobante'] ?>)">
                                    Rechazar
                                </button>

                                <button type="button" class="btn btn-primary" onclick="aprobarComprobante(<?= $comp['IdComprobante'] ?>)">
                                    Aprobar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
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

    <!-- MODAL PARA APROBAR -->
    <div class="modal fade" id="modalAprobar" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Aprobar Comprobante</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="idComprobante" id="aprobar_idComprobante">
                        <input type="hidden" name="accion" value="aprobar">
                        
                        <p>¿Está seguro de aprobar este comprobante?</p>
                        
                        <div class="form-group">
                            <label>Observaciones (opcional)</label>
                            <textarea class="form-control" name="observaciones_admin" rows="3" placeholder="Comentarios adicionales..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="simple-icon-check"></i> Confirmar Aprobación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA RECHAZAR -->
    <div class="modal fade" id="modalRechazar" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Rechazar Comprobante</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="idComprobante" id="rechazar_idComprobante">
                        <input type="hidden" name="accion" value="rechazar">
                        
                        <div class="alert alert-warning">
                            <i class="simple-icon-info"></i> El cliente podrá subir un nuevo comprobante después del rechazo.
                        </div>
                        
                        <div class="form-group">
                            <label>Motivo del rechazo <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="observaciones_admin" rows="4" placeholder="Explique el motivo del rechazo..." required></textarea>
                            <small class="form-text text-muted">Este mensaje será enviado al cliente</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="simple-icon-close"></i> Confirmar Rechazo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA VER ARCHIVO COMPLETO -->
    <div class="modal fade" id="modalArchivo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="simple-icon-magnifier"></i> Comprobante de Pago
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-0" style="background: #2d3436;">
                    <!-- Contenedor para imagen -->
                    <div id="contenedorImagen" style="display: none; max-height: 80vh; overflow: auto;">
                        <img id="imagenCompleta" src="" class="img-fluid w-100" alt="Comprobante" style="max-height: 80vh; object-fit: contain;">
                    </div>
                    
                    <!-- Contenedor para PDF -->
                    <div id="contenedorPDF" style="display: none;">
                        <iframe id="pdfViewer" src="" style="width: 100%; height: 80vh; border: none;"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="btnDescargar" href="" download class="btn btn-primary">
                        <i class="simple-icon-cloud-download"></i> Descargar
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/vendor/jquery-3.3.1.min.js"></script>
    <script src="../js/vendor/bootstrap.bundle.min.js"></script>
    <script src="../js/vendor/perfect-scrollbar.min.js"></script>
    <script src="../js/dore.script.js"></script>
    <script src="../js/scripts.js"></script>

    <script>
        function aprobarComprobante(idComprobante) {
            $('#aprobar_idComprobante').val(idComprobante);
            $('#modalAprobar').modal('show');
        }

        function rechazarComprobante(idComprobante) {
            $('#rechazar_idComprobante').val(idComprobante);
            $('#modalRechazar').modal('show');
        }

        function verArchivoCompleto(ruta, extension) {
            extension = extension.toLowerCase();
            
            // Resetear contenedores
            $('#contenedorImagen').hide();
            $('#contenedorPDF').hide();
            
            // Configurar botón de descarga
            $('#btnDescargar').attr('href', ruta);
            
            if (extension === 'pdf') {
                // Mostrar PDF en iframe
                $('#pdfViewer').attr('src', ruta);
                $('#contenedorPDF').show();
            } else {
                // Mostrar imagen
                $('#imagenCompleta').attr('src', ruta);
                $('#contenedorImagen').show();
            }
            
            $('#modalArchivo').modal('show');
        }

        // Auto-cerrar alertas después de 5 segundos
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
</body>

</html>

<?php $conexion->close(); ?>