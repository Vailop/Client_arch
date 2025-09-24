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

        $rutaImagenes  = "";
        $urlVideo      = "";

        $idDesa = isset($_GET['IdDesarrollo']) ? (int)$_GET['IdDesarrollo'] : null;

        // (Esto ya no lo uses para carpeta; lo mantenemos si te sirve para otros textos)
        if ($idDesa == 1) { $nom_desarrollo = "san_pedro_de_los_pinos"; $desarrollo = "San Pedro De Los Pinos"; }

        // Obtener los parámetros de la URL
        $idUsuarioURL   = isset($_GET['IdUsuario']) ? (int)$_GET['IdUsuario'] : null;
        $mesSeleccionado= isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date("m");

        // ---- Helpers ----
        // slug por si la BD no trae una ruta válida
        function slug($s){
            $s = iconv('UTF-8','ASCII//TRANSLIT',$s);
            $s = strtolower(trim($s));
            $s = preg_replace('/[^a-z0-9]+/','_', $s);
            return trim($s,'_');
        }

        // tomar SOLO el último segmento de RutaImagenes (carpeta real del desarrollo)
        function carpetaDesarrollo($rutaImagenes, $nombre){
            $ruta = trim((string)$rutaImagenes);
            if ($ruta !== '') {
                $ruta = str_replace('\\','/',$ruta);
                $ruta = trim($ruta,'/');
                $last = basename($ruta);            // <- último segmento
                if ($last !== '') return $last;
            }
            return slug($nombre);                   // fallback
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
                // nombre para el título
                $desarrollo = $d['Nombre_Desarrollo'];
                // carpeta física (o derivada con slug)
                $carpeta    = carpetaDesarrollo($d['RutaImagenes'], $d['Nombre_Desarrollo']);
            }
            $stDes->close();
        }

        // Obtener departamentos únicos del desarrollo con superficie real y archivos
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

        // Construcción de rutas de archivos
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

        $dirMes = sprintf('COM%02d', $mesSeleccionado);
        $appUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/').'/';
        $appRoot = rtrim(str_replace('\\','/', realpath(__DIR__)), '/');
        $baseRel = 'desarrollos/'.$carpeta.'/'.$anoActual.'/'.$dirMes.'/';
        $baseUrl = $appUrl.$baseRel;
        $baseFs = $appRoot.'/'.$baseRel;

        // Carpeta de mes tipo COMxx
        $dirMes = sprintf('COM%02d', $mesSeleccionado);

        // Base relativa para URLs de PDFs (desde la raíz del proyecto web)
        // Resultado esperado: /Clientes_Archandel/desarrollos/<carpeta>/<año>/COMxx/
        $appUrl  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/').'/';      // p.ej. /Clientes_Archandel/
        $appRoot = rtrim(str_replace('\\','/', realpath(__DIR__)), '/');  // p.ej. C:/xampp/htdocs/Clientes_Archandel

        $baseRel = 'desarrollos/'.$carpeta.'/'.$anoActual.'/'.$dirMes.'/';
        $baseUrl = $appUrl.$baseRel;             // URL navegable para href
        $baseFs  = $appRoot.'/'.$baseRel;        // ruta física por si quieres validar con is_file()
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
                        // --- Desarrollo dinámico (mismo <ul>, solo <li> nuevos) ---
                        if (session_status() === PHP_SESSION_NONE) { session_start(); }
                        $idUsuario = isset($_SESSION['idusuario']) ? (int)$_SESSION['idusuario'] : 0;

                        $mesAnterior = (int)date('n') - 1;
                        if ($mesAnterior === 0) { $mesAnterior = 12; }

                        $sql = "SELECT DISTINCT DS.IdDesarrollo, DS.Nombre_Desarrollo
                                FROM tbr_usuario_desarrollos UD
                                INNER JOIN tbp_usuarios    US ON UD.IdUsuario    = US.IdUsuario
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
                            $m2inicial = (float)$depto['m2inicial'];
                            $m2actual = (float)$depto['m2actual'];
                            $superficieReal = (float)($depto['SuperficieReal'] ?? 128);
                            
                            // Archivos
                            $fileComprobante = trim($depto['File_Comprobante'] ?? '');
                            $filePlanos = trim($depto['File_Planos'] ?? '');
                            
                            // URLs de descarga
                            $urlComprobante = $fileComprobante ? $baseUrl . rawurlencode($fileComprobante) : '';
                            $urlPlanos = $filePlanos ? $baseUrl . rawurlencode($filePlanos) : '';
                            
                            // Usar fecha actual como placeholder (se puede mejorar con fecha real del archivo)
                            $fechaFormateada = date('d/m/Y H:i');
                            
                            // Calcular datos para las fichas
                            $superficie = $superficieReal;
                            
                            // Calcular pagos realizados y restantes
                            $sqlPagos = "SELECT COUNT(*) as TotalPagos, SUM(CASE WHEN Estatus = 2 THEN Monto ELSE 0 END) as ImportePagado, SUM(CASE WHEN Estatus IN (0, 1) THEN Monto ELSE 0 END) as ImporteRestante, SUM(CASE WHEN Estatus IN (0, 1) THEN 1 ELSE 0 END) as MensualidadesRestantes
                                        FROM tbr_pagos 
                                        WHERE IdDesarrollo = ? AND IdUsuario = ? AND Dpto = ?";
                            
                            $stmtPagos = $conexion->prepare($sqlPagos);
                            $stmtPagos->bind_param('iis', $idDesa, $idUsuario, $numDepto);
                            $stmtPagos->execute();
                            $resPagos = $stmtPagos->get_result();
                            $dataPagos = $resPagos->fetch_assoc();
                            $stmtPagos->close();
                            
                            $importePagado = (float)($dataPagos['ImportePagado'] ?? 0);
                            $mensualidadesRestantes = (int)($dataPagos['MensualidadesRestantes'] ?? 0);
                        ?>
                        <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="depto<?= $numDepto ?>" role="tabpanel" aria-labelledby="depto<?= $numDepto ?>-tab">                             
                            <!-- FICHAS KPI DEL DEPARTAMENTO -->
                            <div class="row mb-4">
                                <div class="col-lg-3">
                                    <div class="card mb-4 progress-banner">
                                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                                            <div>
                                                <i class="iconsminds-hotel mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <p class="lead text-white"><?= number_format($superficie, 2) ?> M² Superficie</p>
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
                                                    <small class="text-white-50">Actualizado: <?= $fechaFormateada ?></small>
                                                    <p class="lead text-white">Planos</p>
                                                </div>
                                            </div>
                                            <div>
                                                <?php if ($filePlanos): ?>
                                                    <a href="<?= $urlPlanos ?>" target="_blank" rel="noopener" download class="btn btn-light btn-sm">
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
                                                <i class="iconsminds-blueprint mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <small class="text-white-50">Actualizado: <?= $fechaFormateada ?></small>
                                                    <p class="lead text-white">Recibo De Pago</p>
                                                </div>
                                            </div>
                                            <div>
                                                <?php if ($fileRecibo): ?>
                                                    <a href="<?= $urlPlanos ?>" target="_blank" rel="noopener" download class="btn btn-light btn-sm">
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
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        // Obtener pagos específicos de este departamento
                                                        $sqlDetallePagos = "SELECT FechaPago, Estatus, Concepto, Monto, Precio_Compraventa, Precio_Actual
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
                                                            $precioActual = (float)$pago['Precio_Actual'];
                                                            
                                                            // Determinar estatus y clase CSS
                                                            $estatus = 'Pendiente';
                                                            $estatusClass = 'badge-primary';
                                                            
                                                            if ($estatusNum === 2) {
                                                                $estatus = 'Pagado';
                                                                $estatusClass = 'badge-success';
                                                                $saldoRestante -= $monto;
                                                            } elseif ($estatusNum === 0) {
                                                                $estatus = 'Cancelado';
                                                                $estatusClass = 'badge-secondary';
                                                            } elseif ($estatusNum === 1 && $fechaPago < $hoy) {
                                                                $estatus = 'Vencido';
                                                                $estatusClass = 'badge-danger';
                                                            }
                                                    ?>
                                                        <tr>
                                                            <td><?= date('d/m/Y', strtotime($pago['FechaPago'])) ?></td>
                                                            <td><span class="badge <?= $estatusClass ?>"><?= $estatus ?></span></td>
                                                            <td><?= htmlspecialchars($pago['Concepto']) ?></td>
                                                            <td>$<?= number_format($monto, 2) ?></td>
                                                            <td>$<?= number_format($precioCompraventa, 2) ?></td>
                                                            <td>$<?= number_format($saldoRestante, 2) ?></td>
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

    <script src="js/vendor/jquery-3.3.1.min.js"></script>
    <script src="js/vendor/bootstrap.bundle.min.js"></script>
    <script src="js/vendor/perfect-scrollbar.min.js"></script>
    <script src="js/vendor/datatables.min.js"></script>
    <script src="js/dore.script.js"></script>
    <script src="js/scripts.js"></script>

    <script>
        $(document).ready(function() {
            // Solo recalcular columnas cuando se cambie de pestaña
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
            });
        });
    </script>
            
</body>

</html>
</body>

</html>