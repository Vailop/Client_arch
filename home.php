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
        /* Asegurar que las tarjetas de progreso muestren todo el contenido */
        .card.dashboard-progress {
            height: auto !important;
            min-height: auto !important;
        }
        
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
                                <i class="iconsminds-box-close mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $totM2Fmt ?> M<sup>2</sup> Totales</p>
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
                                ?>
                                <div class="mb-4">
                                    <p class="mb-2"><?= htmlspecialchars($categoria) ?>
                                        <span class="float-right text-muted"><?= $valorActualFmt ?>/<?= $valorObjetivoFmt ?></span>
                                    </p>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                            aria-valuenow="<?= $porcentaje ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100"
                                            style="width: <?= $porcentaje ?>%;"></div>
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
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3" id="salesTitle-<?= $idD ?>">Plusvalía <?= $nom ?></h5>
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
        (async function () {
        const anio = new Date().getFullYear();

        // Selecciona todos los canvases que comienzan con "salesChart-<IdDesarrollo>"
        const canvases = document.querySelectorAll('canvas[id^="salesChart-"]');

        for (const cv of canvases) {
            const parts = cv.id.split('-');           // ["salesChart", "123"]
            const idDes = Number(parts[1]);
            if (!idDes) continue;

            try {
            const res  = await fetch(`plusvalia_mes.php?IdDesarrollo=${idDes}&anio=${anio}`);
            const json = await res.json();

            // El título ya está establecido desde PHP, no necesitamos cambiarlo aquí
            // Simplemente mantenemos la lógica del gráfico

            // Calcula rango Y con los valores existentes
            const vals = json.valorM2.filter(v => v != null);
            const min  = vals.length ? Math.min(...vals) : 0;
            const max  = vals.length ? Math.max(...vals) : 100;
            const pad  = Math.max(10, (max - min) * 0.1);

            // Construye el gráfico
            const ctx = cv.getContext('2d');
            new Chart(ctx, {
                type: "LineWithShadow", // si no ves nada, prueba "line"
                data: {
                labels: json.labels, // ['Ene', 'Feb', ...]
                datasets: [{
                    label: "Valor m²",
                    data: json.valorM2, // [número o null]
                    borderColor: window.themeColor1 || '#00365a',
                    pointBackgroundColor: window.foregroundColor || '#00365a',
                    pointBorderColor: window.themeColor1 || '#ffffff',
                    pointHoverBackgroundColor: window.themeColor1 || '#ffffff',
                    pointHoverBorderColor: window.foregroundColor || '#ffffff',
                    pointRadius: 6,
                    pointBorderWidth: 2,
                    pointHoverRadius: 8,
                    spanGaps: true, // no une puntos con null
                    fill: false
                }]
                },
                options: {
                plugins: { datalabels: { display: false } },
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: {
                    callbacks: {
                    label: function (item) {
                        var val = item.yLabel;
                        var varPct = json.varPct[item.index];
                        var line = ' $/m²: ' + Number(val).toLocaleString('es-MX', { maximumFractionDigits: 2 });
                        if (varPct !== null && varPct !== undefined) line += ' (Δ ' + varPct + '%)';
                        return line;
                    }
                    }
                },
                scales: {
                    yAxes: [{
                    gridLines: { display: true, lineWidth: 1, color: "rgba(0,0,0,0.1)", drawBorder: false },
                    ticks: { beginAtZero: false, min: Math.floor(min - pad), max: Math.ceil(max + pad) }
                    }],
                    xAxes: [{ gridLines: { display: false } }]
                }
                }
            });

            } catch (e) {
            console.error('Error cargando plusvalía para IdDesarrollo=' + idDes, e);
            }
        }
        })();
    </script>
</body>

</html>