<?php
    require 'config.php';
    // Verificar la sesión para asegurarse de que el usuario esté autenticado
    session_start();

    if (!isset($_SESSION["nombre"]) && !isset($_SESSION["idusuario"])) {
        // Si no está autenticado, redirigir al formulario de login
        echo "<script>window.location.href = 'login.html';</script>";
        exit();
    } else {
        $conexion      = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        $idUsuario     = $_SESSION["idusuario"];
        $urlAvatar     = $_SESSION["Avatar"];
        $nom_desarrollo= "";
        $desarrollo    = "";
        $anoActual     = date("Y");
        $mesActual     = date("m");

        $idDesa = isset($_GET['IdDesarrollo']) ? (int)$_GET['IdDesarrollo'] : null;
        $idUsuarioURL   = isset($_GET['IdUsuario']) ? (int)$_GET['IdUsuario'] : null;
        $mesSeleccionado= isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date("m");

        // ---- Helpers ----
        function slug($s){
            $s = iconv('UTF-8','ASCII//TRANSLIT',$s);
            $s = strtolower(trim($s));
            $s = preg_replace('/[^a-z0-9]+/','_', $s);
            return trim($s,'_');
        }

        function carpetaDesarrollo($rutaImagenes, $nombre){
            $ruta = trim((string)$rutaImagenes);
            if ($ruta !== '') {
                $ruta = str_replace('\\','/',$ruta);
                $ruta = trim($ruta,'/');
                $last = basename($ruta);
                if ($last !== '') return $last;
            }
            return slug($nombre);
        }

        // Traer nombre y carpeta real del desarrollo
        $carpeta = '';
        if (!empty($idDesa)) {
            $qDes = "SELECT Nombre_Desarrollo, COALESCE(RutaImagenes,'') AS RutaImagenes
                    FROM tbp_desarrollos WHERE IdDesarrollo = ?";
            $stDes = $conexion->prepare($qDes);
            $stDes->bind_param('i', $idDesa);
            $stDes->execute();
            $rDes = $stDes->get_result();
            if ($d = $rDes->fetch_assoc()) {
                $desarrollo = $d['Nombre_Desarrollo'];
                $carpeta = carpetaDesarrollo($d['RutaImagenes'], $d['Nombre_Desarrollo']);
            }
            $stDes->close();
        }

        // Obtener departamentos únicos del desarrollo
        $departamentos = [];
        if (!empty($idDesa)) {
            $sqlDeptos = "SELECT DISTINCT 
                            p.Dpto, p.IdCliente, p.Precio_Compraventa, p.m2inicial, p.m2actual,
                            ud.M2Inicial as SuperficieReal, ud.File_Planos
                         FROM tbr_pagos p
                         LEFT JOIN tbr_usuario_desarrollos ud ON p.IdUsuario = ud.IdUsuario 
                            AND p.IdDesarrollo = ud.IdDesarrollo 
                            AND p.Dpto = ud.Dpto
                         WHERE p.IdDesarrollo = ? AND p.IdUsuario = ?
                         ORDER BY p.Dpto";
            $stmtDeptos = $conexion->prepare($sqlDeptos);
            $stmtDeptos->bind_param('ii', $idDesa, $idUsuario);
            $stmtDeptos->execute();
            $resDeptos = $stmtDeptos->get_result();
            
            while($depto = $resDeptos->fetch_assoc()) {
                $departamentos[] = $depto;
            }
            $stmtDeptos->close();
        }

        // Construcción de rutas
        $dirMes = sprintf('COM%02d', $mesSeleccionado);
        $appUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/').'/';
        $baseRel = 'desarrollos/'.$carpeta.'/'.$anoActual.'/'.$dirMes.'/';
        $baseUrl = $appUrl.$baseRel;
    }
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords" content="Archandél, desarrollos, Desarrollos, departamentos, Departamentos, casas, Casas, renta, Renta, venta, Venta, residencias, Residencias, oficinas, Oficinas, proyectos inmobiliarios, lujo, CDMX, área metropolitana">
    <meta name="description" content="Archandél Desarrollos Inmobiliarios en CDMX y área metropolitana. Creamos espacios de alto nivel que potencian la calidad de vida, combinando diseño, innovación y exclusividad.">

    <!-- Favicon icon -->
	<link rel="icon" href="/favicon.png" type="image/x-icon">

    <link rel="stylesheet" href="font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="css/vendor/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="css/vendor/datatables.responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="css/main.css" />

    <style>
        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
        }
        
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        
        .tab-content {
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 0.25rem 0.25rem;
            padding: 1rem;
        }

        /* Estilos para comprobantes dinámicos */
        .comprobante-item {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }

        .comprobante-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .btn-eliminar-comprobante {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .total-mismatch {
            border-color: #dc3545 !important;
        }

        .total-match {
            border-color: #28a745 !important;
        }

        /* Asegurar que las notificaciones aparezcan sobre el modal */
        .notifyjs-wrapper {
            z-index: 9999 !important;
        }

        .notifyjs-corner {
            z-index: 9999 !important;
        }

        /* Forzar contenedor del body */
        .notifyjs-bootstrap-base {
            z-index: 9999 !important;
        }

        /* Responsive para tablas en móvil */
        @media (max-width: 767px) {
            .table-responsive {
                border: none;
            }
            
            .data-table {
                font-size: 12px;
            }
            
            .data-table thead th,
            .data-table tbody td {
                padding: 0.5rem 0.25rem;
                white-space: nowrap;
            }
            
            /* Forzar scroll horizontal */
            .dataTables_wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .dataTables_scrollBody {
                overflow-x: auto !important;
            }
        }

        /* Asegurar que la tabla tenga ancho mínimo */
        .data-table {
            min-width: 800px;
            width: 100% !important;
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

        <a class="navbar-logo" href="Dashboard.Default.html">
            <span class="logo d-none d-xs-block"></span>
            <span class="logo-mobile d-block d-xs-none"></span>
        </a>

        <div class="navbar-right">
            <!-- Dark Mode -->
            <div class="header-icons d-inline-block align-middle">
                <div class="d-none d-md-inline-block align-text-bottom mr-3">
                    <div class="custom-switch custom-switch-primary-inverse custom-switch-small pl-1" 
                        data-toggle="tooltip" data-placement="left" title="Dark Mode">
                        <input class="custom-switch-input" id="switchDark" type="checkbox" checked>
                        <label class="custom-switch-btn" for="switchDark"></label>
                    </div>
                </div>

                <!-- Notificaciones -->
                <div class="position-relative d-inline-block">
                    <button class="header-icon btn btn-empty" type="button" id="notificationButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="simple-icon-bell"></i>
                        <span class="count">1</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right mt-3 position-absolute" id="notificationDropdown">
                        <div class="scroll">
                            <div class="d-flex flex-row mb-3 pb-3 border-bottom">
                                <a href="#">
                                    <img src="img/profiles/l-2.jpg" alt="Notification Image" class="img-thumbnail list-thumbnail xsmall border-0 rounded-circle" />
                                </a>
                                <div class="pl-3">
                                    <a href="#">
                                        <p class="font-weight-medium mb-1">Joisse Kaycee just sent a new comment!</p>
                                        <p class="text-muted mb-0 text-small">09.04.2018 - 12:45</p>
                                    </a>
                                </div>
                            </div>
                        </div>
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
                <button class="btn btn-empty p-0" type="button" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <span class="name"><?php echo $_SESSION["nombre"];?></span>
                    <span>
                        <img alt="Profile Picture" src="<?php echo $urlAvatar;?>" />
                    </span>
                </button>

                <div class="dropdown-menu dropdown-menu-right mt-3">
                    <a class="dropdown-item" href="#">Cuenta</a>
                    <a class="dropdown-item" href="#">Soporte</a>
                    <a class="dropdown-item" href="cerrar_sesion.php">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="menu">
        <div class="main-menu">
            <div class="scroll">
                <ul class="list-unstyled">
                    <li>
                        <a href="#dashboard"><i class="iconsminds-hotel"></i><span>Desarrollo (s)</span></a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- SUB MENU -->
        <div class="sub-menu">
            <div class="scroll">
                <ul class="list-unstyled" data-link="dashboard">                    
                    <?php
                        $mesAnterior = (int)date('n') - 1;
                        if ($mesAnterior === 0) { $mesAnterior = 12; }

                        $sql = "SELECT DISTINCT DS.IdDesarrollo, DS.Nombre_Desarrollo
                                FROM tbr_usuario_desarrollos UD
                                INNER JOIN tbp_desarrollos DS ON UD.IdDesarrollo = DS.IdDesarrollo
                                WHERE UD.IdUsuario = ? AND UD.Estatus = 1
                                ORDER BY DS.Nombre_Desarrollo ASC";

                        if ($stmt = $conexion->prepare($sql)) {
                            $stmt->bind_param('i', $idUsuario);
                            $stmt->execute();
                            $res = $stmt->get_result();

                        if ($res && $res->num_rows > 0) {                       
                            while ($row = $res->fetch_assoc()) {
                                $idDes  = (int)$row['IdDesarrollo'];
                                $nombre = htmlspecialchars($row['Nombre_Desarrollo'], ENT_QUOTES, 'UTF-8');
                    ?>                    
                    <li>
                        <a href="departamentos.php?IdUsuario=<?= $idUsuario; ?>&IdDesarrollo=<?= $idDes; ?>&mes=<?= $mesAnterior; ?>" data-idDesarrollo="<?= $idDes; ?>"><i class="iconsminds-folders"></i><span class="d-inline-block"><?= $nombre; ?></span></a></li>
                        <?php
                            }
                            } else {
                        ?>
                            <li><i class="iconsminds-folder-delete"></i><span class="d-inline-block">No hay desarrollos asignados</span></li>
                            <?php
                            }
                            $stmt->close();
                            } else {
                        ?>
                    <li><i class="iconsminds-folder-close"></i><span class="d-inline-block text-danger">Error al preparar la consulta</span></li>
                    <?php
                        }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1><?php echo $desarrollo; ?></h1>
                    <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
                        <ol class="breadcrumb pt-0">
                            <li class="breadcrumb-item">
                                <a href="home.php">Dashboard</a>
                            </li>
                        </ol>
                    </nav>
                    <div class="separator mb-5"></div>
                </div>
            </div>
            
            <?php if (!empty($departamentos)): ?>
            <!-- PESTAÑAS DE DEPARTAMENTOS -->
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="departamentoTabs" role="tablist">
                        <?php foreach($departamentos as $index => $depto): ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                               id="depto<?= $depto['Dpto'] ?>-tab" 
                               data-toggle="tab" 
                               href="#depto<?= $depto['Dpto'] ?>" 
                               role="tab" 
                               aria-controls="depto<?= $depto['Dpto'] ?>" 
                               aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                Departamento <?= $depto['Dpto'] ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="tab-content" id="departamentoTabsContent">
                        <?php foreach($departamentos as $index => $depto): 
                            $numDepto = $depto['Dpto'];
                            $idCliente = $depto['IdCliente'];
                            $precioCompraventa = (float)$depto['Precio_Compraventa'];
                            $superficieReal = (float)($depto['SuperficieReal'] ?? 128);
                            $filePlanos = trim($depto['File_Planos'] ?? '');
                            $urlPlanos = $filePlanos ? $baseUrl . rawurlencode($filePlanos) : '';
                            $fechaFormateada = date('d/m/Y H:i');
                            
                            // Calcular pagos realizados
                            $sqlPagos = "SELECT 
                                SUM(CASE WHEN Estatus = 2 THEN Monto ELSE 0 END) as ImportePagado
                            FROM tbr_pagos 
                            WHERE IdDesarrollo = ? AND IdUsuario = ? AND Dpto = ?";
                            
                            $stmtPagos = $conexion->prepare($sqlPagos);
                            $stmtPagos->bind_param('iis', $idDesa, $idUsuario, $numDepto);
                            $stmtPagos->execute();
                            $resPagos = $stmtPagos->get_result();
                            $dataPagos = $resPagos->fetch_assoc();
                            $stmtPagos->close();
                            
                            $importePagado = (float)($dataPagos['ImportePagado'] ?? 0);
                        ?>
                        <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                             id="depto<?= $numDepto ?>" 
                             role="tabpanel" 
                             aria-labelledby="depto<?= $numDepto ?>-tab">
                             
                            <!-- FICHAS KPI DEL DEPARTAMENTO -->
                            <div class="row mb-4">
                                <div class="col-lg-3">
                                    <div class="card mb-4 progress-banner">
                                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                                            <div>
                                                <i class="iconsminds-hotel mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <p class="lead text-white"><?= number_format($superficieReal, 2) ?> M² Superficie</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="card mb-4 progress-banner">
                                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                                            <div>
                                                <i class="iconsminds-financial mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <p class="lead text-white">$<?= number_format($importePagado, 2) ?> Importe Pagado</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="card mb-4 progress-banner">
                                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                                            <div>
                                                <i class="iconsminds-blueprint mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <p class="lead text-white">Planos</p>
                                                    <small class="text-white-50">Actualizado: <?= $fechaFormateada ?></small>
                                                </div>
                                            </div>
                                            <div>
                                                <?php if ($filePlanos): ?>
                                                    <a href="<?= $urlPlanos ?>" 
                                                       target="_blank" 
                                                       rel="noopener" 
                                                       download 
                                                       class="btn btn-light btn-sm">
                                                        <i class="simple-icon-cloud-download"></i> Descargar
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-light btn-sm" disabled>
                                                        <i class="simple-icon-ban"></i> Sin archivo
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="card mb-4 progress-banner">
                                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                                            <div>
                                                <i class="iconsminds-gear mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <p class="lead text-white">Vacío</p>
                                                    <small class="text-white-50">Por definir</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TABLA DE PAGOS -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Historial de Pagos - Departamento <?= $numDepto ?></h5>
                                            <table class="data-table data-table-feature">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha de Pago</th>
                                                        <th>Estatus</th>
                                                        <th>Concepto</th>
                                                        <th>Monto</th>
                                                        <th>Precio Compraventa</th>
                                                        <th>Restante</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Obtener pagos específicos de este departamento
                                                    $sqlDetallePagos = "SELECT IdPago, FechaPago, Estatus, Concepto, Monto, Precio_Compraventa, Precio_Actual
                                                                        FROM tbr_pagos 
                                                                        WHERE IdDesarrollo = ? AND IdUsuario = ? AND Dpto = ?
                                                                        ORDER BY FechaPago";
                                                    
                                                    $stmtDetalle = $conexion->prepare($sqlDetallePagos);
                                                    $stmtDetalle->bind_param('iis', $idDesa, $idUsuario, $numDepto);
                                                    $stmtDetalle->execute();
                                                    $resDetalle = $stmtDetalle->get_result();
                                                    
                                                    $saldoRestante = $precioCompraventa;
                                                    $hoy = date('Y-m-d');
                                                    
                                                    while($pago = $resDetalle->fetch_assoc()):
                                                        $estatusNum = (int)$pago['Estatus'];
                                                        $fechaPago = $pago['FechaPago'];
                                                        $monto = (float)$pago['Monto'];
                                                        $idPagoActual = $pago['IdPago'];
                                                        
                                                        // Verificar si hay comprobantes pendientes para este pago
                                                        $sqlComprobantes = "SELECT COUNT(*) as ComprobantesEnValidacion 
                                                                            FROM tbr_comprobantes_pago 
                                                                            WHERE IdPago = ? AND Estatus = 'Pendiente'";
                                                        $stmtCompr = $conexion->prepare($sqlComprobantes);
                                                        $stmtCompr->bind_param('i', $idPagoActual);
                                                        $stmtCompr->execute();
                                                        $resCompr = $stmtCompr->get_result();
                                                        $dataCompr = $resCompr->fetch_assoc();
                                                        $stmtCompr->close();
                                                        
                                                        $tieneComprobantes = (int)$dataCompr['ComprobantesEnValidacion'] > 0;
                                                        
                                                        // Determinar estatus y clase CSS
                                                        $estatus = 'Pendiente';
                                                        $estatusClass = 'badge-primary';
                                                        $mostrarBoton = false;
                                                        
                                                        if ($estatusNum === 2) {
                                                            $estatus = 'Pagado';
                                                            $estatusClass = 'badge-success';
                                                            $saldoRestante -= $monto;
                                                        } elseif ($estatusNum === 0) {
                                                            $estatus = 'Cancelado';
                                                            $estatusClass = 'badge-secondary';
                                                        } elseif ($estatusNum === 1) {
                                                            if ($tieneComprobantes) {
                                                                $estatus = 'En Validación';
                                                                $estatusClass = 'badge-warning';
                                                            } elseif ($fechaPago < $hoy) {
                                                                $estatus = 'Vencido';
                                                                $estatusClass = 'badge-danger';
                                                                $mostrarBoton = true;
                                                            } else {
                                                                $estatus = 'Pendiente';
                                                                $estatusClass = 'badge-primary';
                                                                $mostrarBoton = true;
                                                            }
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td><?= date('d/m/Y', strtotime($pago['FechaPago'])) ?></td>
                                                        <td><span class="badge <?= $estatusClass ?>"><?= $estatus ?></span></td>
                                                        <td><?= htmlspecialchars($pago['Concepto']) ?></td>
                                                        <td>$<?= number_format($monto, 2) ?></td>
                                                        <td>$<?= number_format($precioCompraventa, 2) ?></td>
                                                        <td>$<?= number_format($saldoRestante, 2) ?></td>
                                                        <td>
                                                            <?php if ($mostrarBoton): ?>
                                                                <button type="button" class="btn btn-primary btn-sm btn-abonar" data-toggle="modal" data-target="#modalComprobantes" data-idpago="<?= $idPagoActual ?>" data-concepto="<?= htmlspecialchars($pago['Concepto']) ?>" data-monto="<?= $monto ?>" data-fecha="<?= date('d/m/Y', strtotime($pago['FechaPago'])) ?>">
                                                                    <i class="simple-icon-cloud-upload"></i> Abonar
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="text-muted">—</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; 
                                                    $stmtDetalle->close();
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h4>No hay departamentos registrados</h4>
                        <p>No se encontraron departamentos para este desarrollo y usuario.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="page-footer">
        <div class="footer-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <p class="mb-0 text-muted">© 2023 ARCHANDEL. All Right Reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- MODAL PARA SUBIR COMPROBANTES -->
    <div class="modal fade" id="modalComprobantes" tabindex="-1" role="dialog" aria-labelledby="modalComprobantesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalComprobantesLabel">Subir Comprobantes de Pago</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formComprobantes" enctype="multipart/form-data">
                    <div class="modal-body">
                        <!-- Información del pago -->
                        <div class="alert alert-info">
                            <h6 class="mb-2">Información del Pago</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Concepto:</strong> <span id="pagoConcepto">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Monto total:</strong> <span id="pagoMonto">$0.00</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Fecha límite:</strong> <span id="pagoFecha">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Total comprobantes:</strong> <span id="totalComprobantes" class="badge badge-secondary">$0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Contenedor de comprobantes -->
                        <div id="comprobantesContainer">
                            <!-- Los comprobantes se agregan dinámicamente -->
                        </div>

                        <!-- Botón para agregar comprobante -->
                        <div class="text-center mb-3">
                            <button type="button" id="btnAgregarComprobante" class="btn btn-outline-primary">
                                <i class="simple-icon-plus"></i> Agregar Comprobante
                            </button>
                        </div>

                        <input type="hidden" id="pagoIdPago" name="idPago">
                        <input type="hidden" id="pagoMontoHidden" name="montoTotal">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnSubirComprobantes" class="btn btn-primary" disabled>
                            <i class="simple-icon-cloud-upload"></i> Subir Comprobantes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/vendor/jquery-3.3.1.min.js"></script>
    <script src="js/vendor/bootstrap.bundle.min.js"></script>
    <script src="js/vendor/perfect-scrollbar.min.js"></script>
    <script src="js/vendor/datatables.min.js"></script>
    <script src="js/vendor/bootstrap-notify.min.js"></script>
    <script src="js/dore.script.js"></script>
    <script src="js/scripts.js"></script>

    <script>
        $(document).ready(function() {
            // Solo recalcular columnas cuando se cambie de pestaña
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
            });

            // Manejar apertura del modal
            $(document).on('click', '.btn-abonar', function() {
                var idPago = $(this).data('idpago');
                var concepto = $(this).data('concepto');
                var monto = $(this).data('monto');
                var fecha = $(this).data('fecha');

                // Llenar información del pago
                $('#pagoConcepto').text(concepto);
                $('#pagoMonto').text('' + Number(monto).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                $('#pagoFecha').text(fecha);
                $('#pagoIdPago').val(idPago);
                $('#pagoMontoHidden').val(monto);

                // Limpiar contenedor
                $('#comprobantesContainer').empty();
                $('#totalComprobantes').text('$0.00').removeClass('badge-success badge-danger').addClass('badge-secondary');
                $('#alertaValidacion').hide();
                $('#btnSubirComprobantes').prop('disabled', true);

                // Agregar primer comprobante
                agregarComprobante();

                // Mostrar modal
                $('#modalComprobantes').modal('show');
            });
        });

        // Función para formatear input de moneda
        function formatearMoneda(input) {
            // Obtener solo números y punto decimal
            let valor = input.value.replace(/[^0-9.]/g, '');
            
            // Si está vacío, no hacer nada
            if (valor === '') return;
            
            // Asegurar solo un punto decimal
            let partes = valor.split('.');
            if (partes.length > 2) {
                valor = partes[0] + '.' + partes.slice(1).join('');
            }
            
            // Limitar decimales a 2 SOLO si ya hay punto decimal
            if (partes.length === 2 && partes[1].length > 2) {
                valor = partes[0] + '.' + partes[1].substring(0, 2);
            }
            
            // Formatear con comas para miles
            let numero = parseFloat(valor);
            if (!isNaN(numero)) {
                input.value = numero.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }

        // Función para obtener valor numérico sin formato
        function obtenerValorNumerico(inputFormateado) {
            return parseFloat(inputFormateado.replace(/[^0-9.]/g, '')) || 0;
        }

        var contadorComprobantes = 0;

        // Actualizar la función agregarComprobante
        function agregarComprobante() {
            contadorComprobantes++;
            
            var html = `
                <div class="comprobante-item" data-numero="${contadorComprobantes}">
                    <div class="comprobante-header">
                        <h6 class="mb-0">Comprobante ${contadorComprobantes}</h6>
                        ${contadorComprobantes > 1 ? '<button type="button" class="btn btn-danger btn-eliminar-comprobante btn-sm" onclick="eliminarComprobante(' + contadorComprobantes + ')"><i class="simple-icon-trash"></i></button>' : ''}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Monto del Comprobante <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control monto-comprobante" name="montos_display[]" placeholder="0.00" required>
                                    <input type="hidden" class="monto-valor" name="montos[]">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Archivo <span class="text-danger">*</span></label>
                                <input type="file" class="form-control-file" name="archivos[]" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="form-text text-muted">PDF, JPG, PNG máximo 5MB</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Referencia Bancaria</label>
                                <input type="text" class="form-control" name="referencias[]" placeholder="Número de referencia">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Real del Pago</label>
                                <input type="date" class="form-control" name="fechas[]" max="${new Date().toISOString().split('T')[0]}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Comentarios</label>
                                <textarea class="form-control" name="comentarios[]" rows="2" placeholder="Observaciones adicionales (opcional)"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#comprobantesContainer').append(html);
            
            // Agregar eventos a los nuevos inputs
            $('.monto-comprobante').last().on('keyup input', function() {
                // Actualizar campo hidden con valor numérico
                let valorNumerico = obtenerValorNumerico(this.value);
                $(this).siblings('.monto-valor').val(valorNumerico);
                
                calcularTotal();
            });
            
            // Formatear al perder el foco
            $('.monto-comprobante').last().on('blur', function() {
                if (this.value.trim() !== '') {
                    formatearMoneda(this);
                    let valorNumerico = obtenerValorNumerico(this.value);
                    $(this).siblings('.monto-valor').val(valorNumerico);
                    calcularTotal();
                }
            });
            
            // Limpiar formato al enfocar para facilitar edición
            $('.monto-comprobante').last().on('focus', function() {
                let valorNumerico = obtenerValorNumerico(this.value);
                if (valorNumerico > 0) {
                    this.value = valorNumerico.toString();
                }
            });
        }

        function eliminarComprobante(numero) {
            $('[data-numero="' + numero + '"]').remove();
            renumerarComprobantes();
            calcularTotal();
        }

        function renumerarComprobantes() {
            var contador = 1;
            $('#comprobantesContainer .comprobante-item').each(function() {
                $(this).attr('data-numero', contador);
                $(this).find('h6').text('Comprobante ' + contador);
                contador++;
            });
            contadorComprobantes = contador - 1;
        }

        // Actualizar función calcularTotal para usar valores numéricos
        function calcularTotal() {
            var total = 0;
            var montoEsperado = parseFloat($('#pagoMontoHidden').val());
            
            $('.monto-valor').each(function() {
                var valor = parseFloat($(this).val()) || 0;
                total += valor;
            });
            
            $('#totalComprobantes').text('$' + Number(total).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            
            // Validar suma con notificaciones
            if (Math.abs(total - montoEsperado) < 0.01) {
                $('#totalComprobantes').removeClass('badge-secondary badge-danger').addClass('badge-success');
                $('#alertaValidacion').hide();
                $('#btnSubirComprobantes').prop('disabled', false);
                
                // Notificación de validación exitosa (solo si hay monto)
                if (total > 0) {
                    $.notify({
                        message: 'Montos validados correctamente',
                        icon: 'simple-icon-check'
                    }, {
                        type: 'success',
                        delay: 2000,
                        z_index: 9999,
                        container: 'body',
                        placement: {
                            from: "top",
                            align: "center"
                        }
                    });
                }
            } else if (total > montoEsperado) {
                $('#totalComprobantes').removeClass('badge-secondary badge-success').addClass('badge-danger');
                $('#mensajeValidacion').text('La suma excede el monto del pago.');
                $('#alertaValidacion').show();
                $('#btnSubirComprobantes').prop('disabled', true);
                
                $.notify({
                    message: 'La suma excede el monto del pago',
                    icon: 'simple-icon-exclamation'
                }, {
                    type: 'warning',
                    delay: 3000,
                    z_index: 9999,
                    container: 'body',
                    placement: {
                        from: "top",
                        align: "center"
                    }
                });
            } else {
                $('#totalComprobantes').removeClass('badge-success badge-danger').addClass('badge-secondary');
                $('#mensajeValidacion').text('La suma de comprobantes debe igualar el monto total del pago.');
                $('#alertaValidacion').show();
                $('#btnSubirComprobantes').prop('disabled', true);
            }
        }

        // Agregar comprobante adicional
        $('#btnAgregarComprobante').click(function() {
            agregarComprobante();
        });

        // Envío del formulario con notificaciones bootstrap-notify
        $('#formComprobantes').on('submit', function(e) {
            e.preventDefault();
            
            // Mostrar notificación de procesando
            $.notify({
                message: 'Subiendo comprobantes, por favor espere...',
                icon: 'simple-icon-cloud-upload'
            }, {
                type: 'info',
                delay: 0,
                placement: {
                    from: "top",
                    align: "center"
                }
            });
            
            var formData = new FormData(this);
            
            $.ajax({
                url: 'subir_comprobantes.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    try {
                        var data = JSON.parse(response);
                        if (data.success) {
                            // Notificación de éxito
                            $.notify({
                                message: 'Comprobantes subidos exitosamente. En breve serán validados.',
                                icon: 'simple-icon-check'
                            }, {
                                type: 'success',
                                delay: 4000,
                                placement: {
                                    from: "top",
                                    align: "center"
                                }
                            });
                            
                            $('#modalComprobantes').modal('hide');
                            
                            // Recargar después de un breve delay
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                            
                        } else {
                            // Notificación de error específico
                            $.notify({
                                message: data.message || 'Error al procesar los comprobantes',
                                icon: 'simple-icon-exclamation'
                            }, {
                                type: 'danger',
                                delay: 6000,
                                placement: {
                                    from: "top",
                                    align: "center"
                                }
                            });
                        }
                    } catch (e) {
                        // Error al parsear JSON
                        $.notify({
                            message: 'Error inesperado en la respuesta del servidor',
                            icon: 'simple-icon-exclamation'
                        }, {
                            type: 'danger',
                            delay: 6000,
                            placement: {
                                from: "top",
                                align: "center"
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Error de conexión o servidor
                    $.notify({
                        message: 'Error de conexión. Verifique su internet e intente nuevamente.',
                        icon: 'simple-icon-close'
                    }, {
                        type: 'danger',
                        delay: 6000,
                        placement: {
                            from: "top",
                            align: "center"
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>