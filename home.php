<?php
    require 'config.php';
    // Verificar la sesión para asegurarse de que el usuario esté autenticado
    session_start();

    if (!isset($_SESSION["nombre"]) && !isset($_SESSION["idusuario"])) {
        // Si no está autenticado, redirigir al formulario de login
        echo "<script>window.location.href = 'login.html';</script>";
        exit();
    }
    else
    {
        $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        $idUsuario = $_SESSION["idusuario"];
        $urlAvatar = $_SESSION["Avatar"];
    }
?>

<!DOCTYPE html>
<html lang="en">

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
    <link rel="stylesheet" href="css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="css/vendor/fullcalendar.min.css" />
    <link rel="stylesheet" href="css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="css/main.css" />

    <style>
        /* Alertas */
        .alerta-item {
            padding: 10px 12px;
            border-left: 4px solid;
            margin-bottom: 8px;
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.03);
            cursor: pointer;
            transition: all 0.2s;
        }

        .alerta-item:hover {
            background: rgba(0, 0, 0, 0.08);
            transform: translateX(3px);
        }

        .alerta-critica { border-color: #dc3545; }
        .alerta-alta { border-color: #ffc107; }
        .alerta-media { border-color: #fd7e14; }
        .alerta-info { border-color: #28a745; }

        .alerta-titulo {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .alerta-mensaje {
            font-size: 12px;
            color: #666;
        }

        #notificationDropdown .spinner-border-sm {
            width: 1.5rem;
            height: 1.5rem;
        }
        /* Asegurar que las tarjetas de progreso muestren todo el contenido */
        .card.dashboard-progress {
            height: auto !important;
            min-height: auto !important;
        }

        .card.dashboard-progress .progress {
            height: 8px;
            border-radius: 4px;
        }

        /* Colores para las barras */
        .progress-bar.bg-danger { background-color: #dc3545 !important; }
        .progress-bar.bg-warning { background-color: #ffc107 !important; }
        .progress-bar.bg-success { background-color: #28a745 !important; }
                
        .card.dashboard-progress .card-body {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }
        
        /* Ajustar espaciado para que quepan las 8 barras */
        .card.dashboard-progress .mb-4 {
            margin-bottom: 1rem !important;
        }
        
        .card.dashboard-progress .progress {
            height: 6px;
        }
        
        .card.dashboard-progress p {
            margin-bottom: 0.5rem;
            font-size: 13px;
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
                        <span class="count" id="alertasCount">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right mt-3 position-absolute" id="notificationDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                        <div class="p-3">
                            <h6 class="mb-3">Notificaciones</h6>
                            <div id="alertasContainer">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <p class="mt-2 mb-0 text-muted small">Cargando...</p>
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
                    <h1>Dashboard</h1>
                    <div class="separator mb-5"></div>
                </div>
            </div>

            <?php
                $sql = "SELECT COUNT(DISTINCT IdDesarrollo) AS total_desarrollos, COUNT(*) AS total_departamentos, COALESCE(SUM(M2Inicial), 0) AS total_m2
                        FROM tbr_usuario_desarrollos
                        WHERE IdUsuario = ? AND Estatus = 1";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('i', $idUsuario);
                $stmt->execute();
                $res = $stmt->get_result();
                $kpi = $res->fetch_assoc();
                $stmt->close();

                // Formateos y valores de seguridad
                $totDev  = (int)($kpi['total_desarrollos'] ?? 0);
                $totDep  = (int)($kpi['total_departamentos'] ?? 0);
                $totM2   = (float)($kpi['total_m2'] ?? 0);

                // Para mostrar bonito
                $totM2Fmt = number_format($totM2, 2, '.', ','); // 52,677.16
            ?>

            <!-- TARJETAS KPI -->
            <div class="row">
                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-hotel mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $totDev ?> Desarrollos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-post-office mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $totDep ?> Departamentos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <a href="#" class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-box-close mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $totM2Fmt ?> M<sup>2</sup> Totales</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <a href="#" class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-financial mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <?php
                                        $sqlMens = "SELECT COUNT(*) AS restantes FROM tbr_pagos WHERE IdUsuario = ? AND Estatus = 1";
                                        $stmtMens = $conexion->prepare($sqlMens);
                                        $stmtMens->bind_param('i', $idUsuario);
                                        $stmtMens->execute();
                                        $resMens = $stmtMens->get_result();
                                        $mensRestantes = $resMens->fetch_assoc()['restantes'] ?? 0;
                                        $stmtMens->close();
                                    ?>
                                    <p class="lead text-white"><?= $mensRestantes ?> Mensualidades restantes</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- AVANCE DE OBRA Y CALENDARIO -->
            <div class="row">
                <?php
                    // Obtener desarrollos del usuario para tarjetas de progreso
                    $sqlDesarrollos = "SELECT DISTINCT DS.IdDesarrollo, DS.Nombre_Desarrollo
                                    FROM tbr_usuario_desarrollos UD
                                    INNER JOIN tbp_desarrollos DS ON UD.IdDesarrollo = DS.IdDesarrollo
                                    WHERE UD.IdUsuario = ? AND UD.Estatus = 1
                                    ORDER BY DS.Nombre_Desarrollo ASC";

                    $stmtDes = $conexion->prepare($sqlDesarrollos);
                    $stmtDes->bind_param('i', $idUsuario);
                    $stmtDes->execute();
                    $resDes = $stmtDes->get_result();

                    $categorias_orden = [
                        'Planificación',
                        'Excavación y cimentación', 
                        'Pilares / estructura',
                        'Instalaciones',
                        'Acabados interiores y exteriores',
                        'Equipamiento',
                        'Inspección y pruebas',
                        'Entrega'
                    ];

                    while ($desarrollo = $resDes->fetch_assoc()) {
                        $idDes = (int)$desarrollo['IdDesarrollo'];
                        $nombreDes = htmlspecialchars($desarrollo['Nombre_Desarrollo'], ENT_QUOTES, 'UTF-8');
                        
                        // Obtener datos de avance para este desarrollo
                        $sqlAvance = "SELECT Categoria, ValorActual, ValorObjetivo
                                    FROM tbr_avance_desarrollo 
                                    WHERE IdDesarrollo = ?
                                    ORDER BY FIELD(Categoria, '" . implode("','", $categorias_orden) . "')";
                        
                        $stmtAvance = $conexion->prepare($sqlAvance);
                        $stmtAvance->bind_param('i', $idDes);
                        $stmtAvance->execute();
                        $resAvance = $stmtAvance->get_result();
                        
                        $avances = [];
                        while ($avance = $resAvance->fetch_assoc()) {
                            $avances[$avance['Categoria']] = $avance;
                        }
                        $stmtAvance->close();
                ?>
                
                <!-- AVANCE DE OBRA -->
                <div class="col-lg-6 mb-4">
                    <div class="card dashboard-progress">
                        <div class="position-absolute card-top-buttons">
                            <button class="btn btn-header-light icon-button" onclick="location.reload()">
                                <i class="simple-icon-refresh"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Avance De Obra <?= $nombreDes ?></h5>
                            
                            <?php foreach ($categorias_orden as $categoria): ?>
                                <?php
                                    // Obtener valores o usar defaults
                                    if (isset($avances[$categoria])) {
                                        $valorActual = (float)$avances[$categoria]['ValorActual'];
                                        $valorObjetivo = (float)$avances[$categoria]['ValorObjetivo'];
                                    } else {
                                        $valorActual = 0;
                                        $valorObjetivo = 1;
                                    }
                                    
                                    $porcentaje = $valorObjetivo > 0 ? round(($valorActual / $valorObjetivo) * 100, 2) : 0;
                                    $valorActualFmt = rtrim(rtrim(number_format($valorActual, 2, '.', ''), '0'), '.');
                                    $valorObjetivoFmt = rtrim(rtrim(number_format($valorObjetivo, 2, '.', ''), '0'), '.');

                                    // Determinar color según porcentaje
                                    $colorBarra = 'bg-primary';
                                    if ($porcentaje >= 100) $colorBarra = 'bg-success';
                                    elseif ($porcentaje < 50) $colorBarra = 'bg-danger';
                                    elseif ($porcentaje < 80) $colorBarra = 'bg-warning';
                                ?>
                                <div class="mb-4">
                                    <p class="mb-2"><?= htmlspecialchars($categoria) ?>
                                        <strong><?= $porcentaje ?>%</strong> 
                                    </p>
                                    <div class="progress">
                                        <div class="progress-bar <?= $colorBarra ?>" role="progressbar" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $porcentaje ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php
                    }
                    $stmtDes->close();
                ?>

                <!-- CALENDARIO -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Calendario De Pagos</h5>
                            <div class="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRÁFICOS DE PLUSVALÍA -->
            <?php
                // Obtener desarrollos para gráficos de plusvalía
                $stmtPlusvalia = $conexion->prepare("SELECT DISTINCT DS.IdDesarrollo, DS.Nombre_Desarrollo
                                                    FROM tbr_usuario_desarrollos UD
                                                    INNER JOIN tbp_desarrollos DS ON UD.IdDesarrollo = DS.IdDesarrollo
                                                    WHERE UD.IdUsuario = ? AND UD.Estatus = 1
                                                    ORDER BY DS.Nombre_Desarrollo");
                $stmtPlusvalia->bind_param('i', $idUsuario);
                $stmtPlusvalia->execute();
                $resPlusvalia = $stmtPlusvalia->get_result();

                while ($d = $resPlusvalia->fetch_assoc()):
                    $idD = (int)$d['IdDesarrollo'];
                    $nom = htmlspecialchars($d['Nombre_Desarrollo'], ENT_QUOTES, 'UTF-8');
            ?>
            <div class="row">
                <!-- Gauge circular - 50% -->
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center d-flex flex-column">
                            <h5 class="card-title mb-3">Plusvalía Total</h5>
                            
                            <!-- Gauge más grande para llenar el espacio -->
                            <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                                <div style="width: 320px; height: 320px; position: relative; margin: 20px 0;">
                                    <canvas id="gaugeChart-<?= $idD ?>"></canvas>
                                    <div style="position: absolute; top: 58%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%;">
                                        <div id="gaugeValue-<?= $idD ?>" style="font-size: 48px; font-weight: 700; line-height: 1;">0%</div>
                                        <div class="text-muted" style="font-size: 13px; margin-top: 8px;">Ganancia acumulada</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Datos en fila horizontal -->
                            <div class="mt-auto pt-3" style="border-top: 1px solid #e9ecef;">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <small class="text-muted d-block mb-1" style="font-size: 10px; text-transform: uppercase;">Inversión</small>
                                        <strong id="inversionInicial-<?= $idD ?>" style="font-size: 15px; display: block;">$0</strong>
                                    </div>
                                    <div class="col-4" style="border-left: 1px solid #e9ecef; border-right: 1px solid #e9ecef;">
                                        <small class="text-muted d-block mb-1" style="font-size: 10px; text-transform: uppercase;">Valor Actual</small>
                                        <strong id="valorActual-<?= $idD ?>" style="font-size: 15px; display: block; color: #28a745;">$0</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block mb-1" style="font-size: 10px; text-transform: uppercase;">Ganancia</small>
                                        <strong id="gananciaNeta-<?= $idD ?>" style="font-size: 15px; display: block; color: #ffc107;">$0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico de líneas - 50% -->
                <div class="col-lg-8 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="mb-3">Evolución de Plusvalía - <?= $nom ?></h5>
                            <div id="statsContainer-<?= $idD ?>" class="mb-3"></div>
                            <div style="height:320px">
                                <canvas id="salesChart-<?= $idD ?>"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endwhile; 
                $stmtPlusvalia->close();
            ?>

            <div class="row">
                <!-- GRÁFICO DONUT: Estado de Pagos -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="card-title mb-4">Estado de Pagos</h6>
                            <div style="height:220px; position:relative;">
                                <canvas id="donutPagos"></canvas>
                            </div>
                            <div id="estadoPagosLegend" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                 <!-- GRÁFICO DE CASCADA: Construcción de Valor -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Construcción de Valor - <?= $nom ?></h5>
                            <div style="height:300px">
                                <canvas id="waterfallChart-<?= $idD ?>"></canvas>
                            </div>
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
                    <div class="col-12 col-sm-6">
                        <p class="mb-0 text-muted">© 2023 ARCHANDEL. All Right Reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/vendor/jquery-3.3.1.min.js"></script>
    <script src="js/vendor/bootstrap.bundle.min.js"></script>
    <script src="js/vendor/Chart.bundle.min.js"></script>
    <script src="js/vendor/chartjs-plugin-datalabels.js"></script>
    <script src="js/vendor/moment.min.js"></script>
    <script src="js/vendor/fullcalendar.min.js"></script>
    <script src="js/vendor/fullcalendar_locale_all.min.js"></script>
    <script src="js/vendor/perfect-scrollbar.min.js"></script>
    <script src="js/vendor/progressbar.min.js"></script>
    <script src="js/vendor/bootstrap-notify.min.js"></script>
    <script src="js/vendor/mousetrap.min.js"></script>
    <script src="js/payments.notify.js"></script>
    <script src="js/dore.script.js"></script>
    <script src="js/scripts.js"></script>

    <script>
        // Cargar alertas con sistema de visto persistente
        function cargarAlertas() {
            fetch('alertas.php')
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }
                    
                    // Obtener timestamp de última vista
                    const ultimaVista = localStorage.getItem('alertas_ultima_vista');
                    const ahora = Date.now();
                    const cincoMinutos = 300000; // 5 minutos en milisegundos
                    
                    const badge = document.getElementById('alertasCount');
                    const container = document.getElementById('alertasContainer');
                    
                    // Verificar si ya vio las alertas hace menos de 5 minutos
                    const yaVisto = ultimaVista && (ahora - parseInt(ultimaVista)) < cincoMinutos;
                    
                    // Actualizar badge
                    if (yaVisto || data.total === 0) {
                        badge.style.display = 'none';
                    } else {
                        badge.style.display = '';
                        badge.textContent = data.total;
                        
                        // Color según prioridad
                        if (data.criticas > 0) {
                            badge.style.backgroundColor = '#ffffff';
                        } else {
                            badge.style.backgroundColor = '';
                        }
                    }
                    
                    // Mostrar alertas en el dropdown
                    if (data.total === 0) {
                        container.innerHTML = `
                            <div class="text-center py-4">
                                <i class="simple-icon-check" style="font-size: 32px; opacity: 0.3;"></i>
                                <p class="text-muted small mt-2 mb-0">Todo al corriente</p>
                            </div>
                        `;
                        return;
                    }
                    
                    let html = '';
                    data.alertas.forEach(alerta => {
                        const clase = 'alerta-' + alerta.prioridad;
                        html += `
                            <div class="alerta-item ${clase}">
                                <div class="alerta-titulo">
                                    ${alerta.icono} ${alerta.titulo}
                                </div>
                                <div class="alerta-mensaje">${alerta.mensaje}</div>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error cargando alertas:', error);
                    document.getElementById('alertasContainer').innerHTML = `
                        <div class="alert alert-danger small mb-0">Error al cargar notificaciones</div>
                    `;
                });
        }

        // Evento al hacer click en la campana
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar alertas al inicio
            cargarAlertas();
            
            // Actualizar cada 5 minutos
            setInterval(cargarAlertas, 300000);
            
            // Marcar como visto al hacer click en la campana
            const btnNotif = document.getElementById('notificationButton');
            if (btnNotif) {
                btnNotif.addEventListener('click', function() {
                    // Guardar timestamp de visualización
                    localStorage.setItem('alertas_ultima_vista', Date.now().toString());
                    
                    // Ocultar badge después de medio segundo
                    setTimeout(function() {
                        const badge = document.getElementById('alertasCount');
                        if (badge) {
                            badge.style.display = 'none';
                        }
                    }, 500);
                });
            }
        });
    </script>

    <script>
        // === GRÁFICOS DE PLUSVALÍA CON GAUGE ===
        (async function() {
            const anio = new Date().getFullYear();
            const canvases = document.querySelectorAll('canvas[id^="salesChart-"]');
            const chartInstances = {};

            for (const cv of canvases) {
                const parts = cv.id.split('-');
                const idDes = Number(parts[1]);
                if (!idDes) continue;

                try {
                    const res = await fetch('plusvalia_mes.php?IdDesarrollo=' + idDes + '&anio=' + anio);
                    const json = await res.json();

                    if (json.error) {
                        console.error('Error:', json.error);
                        continue;
                    }

                    // === CREAR GAUGE CIRCULAR ===
                    const gaugeCanvas = document.getElementById('gaugeChart-' + idDes);
                    if (gaugeCanvas) {
                        const ctx = gaugeCanvas.getContext('2d');
                        
                        let porcentaje = 0;
                        let color = '#6c757d';
                        
                        if (json.plusvaliaTotalPct !== null && json.plusvaliaTotalPct !== undefined) {
                            porcentaje = json.plusvaliaTotalPct;
                            
                            if (porcentaje >= 15) color = '#28a745';
                            else if (porcentaje >= 10) color = '#5cb85c';
                            else if (porcentaje >= 5) color = '#ffc107';
                            else if (porcentaje >= 0) color = '#17a2b8';
                            else color = '#dc3545';
                        }
                        
                        const displayPct = Math.max(0, Math.min(100, Math.abs(porcentaje)));
                        const resto = 100 - displayPct;
                        
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                datasets: [{
                                    data: [displayPct, resto],
                                    backgroundColor: [color, '#e9ecef'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                circumference: Math.PI,
                                rotation: -Math.PI,
                                cutout: '75%',
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { enabled: false }
                                }
                            }
                        });
                        
                        // Actualizar valor central
                        const valueEl = document.getElementById('gaugeValue-' + idDes);
                        if (valueEl) {
                            valueEl.textContent = (porcentaje >= 0 ? '+' : '') + porcentaje.toFixed(1) + '%';
                            valueEl.style.color = color;
                        }
                        
                        // Actualizar montos con ganancia neta
                        if (json.precioCompra && json.precioActual) {
                            const compra = Number(json.precioCompra);
                            const actual = Number(json.precioActual);
                            const ganancia = actual - compra;
                            
                            document.getElementById('inversionInicial-' + idDes).textContent = 
                                '$' + (compra >= 1000000 ? 
                                    (compra / 1000000).toFixed(2) + 'M' : 
                                    compra.toLocaleString('es-MX', {maximumFractionDigits: 0}));
                            
                            document.getElementById('valorActual-' + idDes).textContent = 
                                '$' + (actual >= 1000000 ? 
                                    (actual / 1000000).toFixed(2) + 'M' : 
                                    actual.toLocaleString('es-MX', {maximumFractionDigits: 0}));
                            
                            // Ganancia neta
                            const gananciaEl = document.getElementById('gananciaNeta-' + idDes);
                            if (gananciaEl) {
                                gananciaEl.textContent = 
                                    (ganancia >= 0 ? '+$' : '-$') + 
                                    (Math.abs(ganancia) >= 1000000 ? 
                                        (Math.abs(ganancia) / 1000000).toFixed(2) + 'M' : 
                                        Math.abs(ganancia).toLocaleString('es-MX', {maximumFractionDigits: 0}));
                                gananciaEl.style.color = ganancia >= 0 ? '#28a745' : '#dc3545';
                            }
                        }
                    }

                    // === ESTADÍSTICAS ===
                    const stats = json.estadisticas;
                    if (stats && document.getElementById('statsContainer-' + idDes)) {
                        let statsHTML = '<div class="row text-center small">';
                        
                        if (stats.ultimo !== null) {
                            statsHTML += `
                                <div class="col-md-3">
                                    <span class="text-muted d-block">Valor Actual</span>
                                    <strong>$${stats.ultimo.toLocaleString('es-MX', {maximumFractionDigits: 0})}/m²</strong>
                                </div>
                            `;
                        }
                        
                        if (stats.variacionTotalPct !== null) {
                            const esPositivo = stats.variacionTotalPct >= 0;
                            statsHTML += `
                                <div class="col-md-3">
                                    <span class="text-muted d-block">Variación Anual</span>
                                    <strong class="${esPositivo ? 'text-success' : 'text-danger'}">
                                        ${esPositivo ? '↑' : '↓'} ${Math.abs(stats.variacionTotalPct).toFixed(1)}%
                                    </strong>
                                </div>
                            `;
                        }
                        
                        if (stats.promedio !== null) {
                            statsHTML += `
                                <div class="col-md-3">
                                    <span class="text-muted d-block">Promedio</span>
                                    <strong>$${Math.round(stats.promedio).toLocaleString('es-MX')}/m²</strong>
                                </div>
                            `;
                        }
                        
                        if (stats.tendencia) {
                            const iconos = {
                                'alcista': '📈',
                                'bajista': '📉',
                                'estable': '➡️'
                            };
                            statsHTML += `
                                <div class="col-md-3">
                                    <span class="text-muted d-block">Tendencia</span>
                                    <strong>${iconos[stats.tendencia]} ${stats.tendencia.charAt(0).toUpperCase() + stats.tendencia.slice(1)}</strong>
                                </div>
                            `;
                        }
                        
                        statsHTML += '</div>';
                        document.getElementById('statsContainer-' + idDes).innerHTML = statsHTML;
                    }

                    // === GRÁFICO DE LÍNEAS ===
                    const vals = json.valorM2.filter(v => v != null);
                    const min = vals.length ? Math.min(...vals) : 0;
                    const max = vals.length ? Math.max(...vals) : 100;
                    const pad = Math.max(10, (max - min) * 0.1);

                    const ctx = cv.getContext('2d');
                    chartInstances[idDes] = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: json.labels,
                            datasets: [{
                                label: 'Valor m² ' + json.anio,
                                data: json.valorM2,
                                borderColor: '#00365a',
                                backgroundColor: 'rgba(0, 54, 90, 0.1)',
                                pointBackgroundColor: '#00365a',
                                pointBorderColor: '#ffffff',
                                pointHoverBackgroundColor: '#ffffff',
                                pointHoverBorderColor: '#00365a',
                                pointRadius: 6,
                                pointBorderWidth: 2,
                                pointHoverRadius: 8,
                                borderWidth: 3,
                                spanGaps: true,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            plugins: { 
                                datalabels: { display: false },
                                legend: { display: false }
                            },
                            responsive: true,
                            maintainAspectRatio: false,
                            tooltips: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(item) {
                                        const val = item.yLabel;
                                        if (val === null) return '';
                                        
                                        let line = 'Valor: $' + Number(val).toLocaleString('es-MX', {maximumFractionDigits: 0}) + '/m²';
                                        
                                        const varPct = json.varPct[item.index];
                                        if (varPct !== null && varPct !== undefined) {
                                            line += ' (' + (varPct >= 0 ? '+' : '') + varPct + '%)';
                                        }
                                        
                                        return line;
                                    }
                                }
                            },
                            scales: {
                                yAxes: [{
                                    gridLines: { 
                                        display: true, 
                                        lineWidth: 1, 
                                        color: "rgba(0,0,0,0.1)", 
                                        drawBorder: false 
                                    },
                                    ticks: { 
                                        beginAtZero: false, 
                                        min: Math.floor(min - pad), 
                                        max: Math.ceil(max + pad),
                                        callback: function(value) {
                                            return '$' + value.toLocaleString('es-MX');
                                        }
                                    }
                                }],
                                xAxes: [{ 
                                    gridLines: { display: false } 
                                }]
                            }
                        }
                    });
                
                // === GRÁFICO DE CASCADA ===
                const waterfallCanvas = document.getElementById('waterfallChart-' + idDes);
                if (waterfallCanvas && json.precioCompra && json.precioActual) {
                    const precioCompra = Number(json.precioCompra);
                    const precioActual = Number(json.precioActual);
                    const plusvalia = precioActual - precioCompra;
                    
                    const ctxWaterfall = waterfallCanvas.getContext('2d');
                    
                    new Chart(ctxWaterfall, {
                        type: 'bar',
                        data: {
                            labels: ['Inversión Inicial', 'Plusvalía', 'Valor Actual'],
                            datasets: [{
                                label: 'Valor',
                                data: [precioCompra, plusvalia, precioActual],
                                backgroundColor: [
                                    '#00365a',
                                    plusvalia >= 0 ? '#28a745' : '#dc3545',
                                    '#17a2b8'
                                ],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            return '$' + (value / 1000000).toFixed(2) + 'M';
                                        }
                                    }
                                },
                                // Etiquetas de datos en las barras
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    formatter: function(value) {
                                        return '$' + (value / 1000000).toFixed(2) + 'M';
                                    },
                                    color: '#495057',
                                    font: {
                                        weight: 'bold',
                                        size: 13
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + (value / 1000000).toFixed(1) + 'M';
                                        }
                                    },
                                    grid: {
                                        display: true,
                                        drawBorder: false
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }

                } catch (e) {
                    console.error('Error cargando plusvalía para IdDesarrollo=' + idDes, e);
                }
            }
        })();
    </script>

    <script>
        // === GRÁFICO DONUT: ESTADO DE PAGOS ===
        (async function() {
            try {
                const res = await fetch('estado_pagos.php');
                const data = await res.json();
                
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                
                const ctx = document.getElementById('donutPagos').getContext('2d');
                
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pagados', 'Pendientes', 'Vencidos'],
                        datasets: [{
                            data: [data.pagados, data.pendientes, data.vencidos],
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = data.total;
                                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return label + ': ' + value + ' (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Leyenda personalizada con montos en pesos
                const legendHTML = `
                    <div class="row text-center small">
                        <div class="col-4">
                            <div style="width:12px; height:12px; background:#28a745; display:inline-block; margin-right:5px;"></div>
                            <strong>${data.pagados}</strong> Pagados<br>
                            <small class="text-muted">$${Number(data.monto_pagados).toLocaleString('es-MX', {maximumFractionDigits: 0})}</small>
                        </div>
                        <div class="col-4">
                            <div style="width:12px; height:12px; background:#ffc107; display:inline-block; margin-right:5px;"></div>
                            <strong>${data.pendientes}</strong> Pendientes<br>
                            <small class="text-muted">$${Number(data.monto_pendientes).toLocaleString('es-MX', {maximumFractionDigits: 0})}</small>
                        </div>
                        <div class="col-4">
                            <div style="width:12px; height:12px; background:#dc3545; display:inline-block; margin-right:5px;"></div>
                            <strong>${data.vencidos}</strong> Vencidos<br>
                            <small class="text-muted">$${Number(data.monto_vencidos).toLocaleString('es-MX', {maximumFractionDigits: 0})}</small>
                        </div>
                    </div>
                `;
                document.getElementById('estadoPagosLegend').innerHTML = legendHTML;
                
            } catch (error) {
                console.error('Error cargando estado de pagos:', error);
            }
        })();
    </script>
</body>

</html>