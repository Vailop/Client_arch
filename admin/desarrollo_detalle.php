<?php
    require '../config.php';
    session_start();

    // Verificar que sea administrador
    if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != 1) {
        header("Location: ../login.php");
        exit();
    }

    // Obtener ID del desarrollo
    $idDesarrollo = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($idDesarrollo <= 0) {
        header("Location: ../home_admin.php");
        exit();
    }

    $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    // Consulta del desarrollo con KPIs
    $sqlDesarrollo = "SELECT 
        d.IdDesarrollo,
        d.Nombre_Desarrollo,
        d.Descripcion,
        d.RutaLogo,
        d.RutaImagenes,
        (SELECT COUNT(DISTINCT ud.IdUsuario) 
        FROM tbr_usuario_desarrollos ud 
        WHERE ud.IdDesarrollo = d.IdDesarrollo AND ud.Estatus = 1) as TotalClientes,
        (SELECT COUNT(*) 
        FROM tbr_comprobantes_pago cp 
        WHERE cp.IdDesarrollo = d.IdDesarrollo AND cp.Estatus = 'Pendiente') as ComprobantesPendientes,
        (SELECT COUNT(*) 
        FROM tbr_pagos p 
        WHERE p.IdDesarrollo = d.IdDesarrollo AND p.Estatus = 1 AND p.FechaPago < CURDATE()) as PagosVencidos,
        (SELECT COUNT(*) 
        FROM tbr_pagos p 
        WHERE p.IdDesarrollo = d.IdDesarrollo AND p.Estatus = 2) as PagosPagados,
        (SELECT COUNT(*) 
        FROM tbr_pagos p 
        WHERE p.IdDesarrollo = d.IdDesarrollo AND p.Estatus = 1) as PagosPendientes,
        (SELECT ROUND(AVG(a.ValorActual / a.ValorObjetivo * 100), 1)
        FROM tbr_avance_desarrollo a 
        WHERE a.IdDesarrollo = d.IdDesarrollo) as AvancePromedio
    FROM tbp_desarrollos d
    WHERE d.IdDesarrollo = ?";

    $stmt = $conexion->prepare($sqlDesarrollo);
    $stmt->bind_param('i', $idDesarrollo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        header("Location: ../home_admin.php");
        exit();
    }

    $desarrollo = $resultado->fetch_assoc();
    $stmt->close();
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
        .opcion-card {
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            min-height: 200px;
        }
        
        .opcion-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .opcion-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .badge-alert {
            position: absolute;
            top: 15px;
            right: 15px;
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
                        // Resetear el puntero del resultado de desarrollos para el menú
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
                                        <a href="actualizar_plusvalia.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-folder-cloud"></i><span class="d-inline-block">Gestión de Archivos</span></a>
                                    </li>
                                    <li>
                                        <a href="actualizar_plusvalia.php?idDesarrollo=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-file-clipboard"></i><span class="d-inline-block">Reportes</span></a>
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
                    <h1><?= htmlspecialchars($desarrollo['Nombre_Desarrollo']) ?></h1>
                    <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
                        <ol class="breadcrumb pt-0">
                            <li class="breadcrumb-item">
                                <a href="home_admin.php">Dashboard</a>
                            </li>
                        </ol>
                    </nav>
                    <div class="separator mb-5"></div>
                </div>
            </div>
            
            <!-- KPIs RÁPIDOS -->
            <div class="row">
                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-male-female mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $desarrollo['TotalClientes'] ?> Clientes Activos</p>
                                    <p class="text-small text-white"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-clock mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $desarrollo['PagosPagados'] ?> Pagos Completados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <a href="#" class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-alarm-clock mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $desarrollo['PagosPendientes'] ?> Pagos Pendientes</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <a href="#" class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-bar-chart-4 mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= number_format($desarrollo['AvancePromedio'] ?? 0, 1) ?>% Avance de Obra</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- USUARIOS -->
                <div class="col-lg-3 col-md-3 mb-4">
                    <div class="card opcion-card" onclick="window.location.href='alta_usuario.php?idDesarrollo=<?= $idDesarrollo ?>'">
                        <div class="card-body text-center">
                            <i class="iconsminds-add-user opcion-icon"></i>
                            <h5>Alta de Usuario</h5>
                            <p class="text-muted mb-0">Registrar nuevo cliente y asignar departamento</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 mb-4">
                    <div class="card opcion-card" onclick="window.location.href='usuarios.php?idDesarrollo=<?= $idDesarrollo ?>'">
                        <div class="card-body text-center position-relative">
                            <i class="iconsminds-business-man-woman opcion-icon"></i>
                            <h5>Administrar Usuarios</h5>
                            <p class="text-muted mb-0">Ver y gestionar clientes del desarrollo</p>
                            <?php if ($desarrollo['TotalClientes'] > 0): ?>
                                <span class="badge badge-primary position-absolute" style="top: 15px; right: 15px;">
                                    <?= $desarrollo['TotalClientes'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- PAGOS -->
                <div class="col-lg-3 col-md-3 mb-4">
                    <div class="card opcion-card" onclick="window.location.href='validar_comprobantes.php?idDesarrollo=<?= $idDesarrollo ?>'">
                        <div class="card-body text-center position-relative">
                            <i class="iconsminds-file-edit opcion-icon"></i>
                            <h5>Validar Comprobantes</h5>
                            <p class="text-muted mb-0">Aprobar o rechazar pagos subidos</p>
                            <?php if ($desarrollo['ComprobantesPendientes'] > 0): ?>
                                <span class="badge badge-danger badge-alert">
                                    <?= $desarrollo['ComprobantesPendientes'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 mb-4">
                    <div class="card opcion-card" onclick="window.location.href='plan_pagos.php?idDesarrollo=<?= $idDesarrollo ?>'">
                        <div class="card-body text-center">
                            <i class="iconsminds-calendar-4 opcion-icon"></i>
                            <h5>Plan de Pagos</h5>
                            <p class="text-muted mb-0">Generar calendario de mensualidades</p>
                        </div>
                    </div>
                </div>

                <!-- AVANCE DE OBRA -->
                <div class="col-lg-3 col-md-3 mb-4">
                    <div class="card opcion-card" onclick="window.location.href='actualizar_avance.php?idDesarrollo=<?= $idDesarrollo ?>'">
                        <div class="card-body text-center">
                            <i class="iconsminds-bar-chart-4 opcion-icon"></i>
                            <h5>Actualizar Avance de Obra</h5>
                            <p class="text-muted mb-0">Modificar progreso de construcción</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 mb-4">
                    <div class="card opcion-card" onclick="window.location.href='actualizar_plusvalia.php?idDesarrollo=<?= $idDesarrollo ?>'">
                        <div class="card-body text-center">
                            <i class="iconsminds-line-chart-1 opcion-icon"></i>
                            <h5>Actualizar Plusvalía</h5>
                            <p class="text-muted mb-0">Modificar valor M² mensual</p>
                        </div>
                    </div>
                </div>

                <!-- ARCHIVOS -->
                <div class="col-lg-3 col-md-3 mb-4">
                    <div class="card opcion-card" onclick="alert('Próximamente')">
                        <div class="card-body text-center">
                            <i class="iconsminds-folder-cloud opcion-icon"></i>
                            <h5>Gestión de Archivos</h5>
                            <p class="text-muted mb-0">Planos y documentos del desarrollo</p>
                        </div>
                    </div>
                </div>

                <!-- REPORTES -->
                <div class="col-lg-3 col-md-3 mb-4">
                    <div class="card opcion-card" onclick="alert('Próximamente')">
                        <div class="card-body text-center">
                            <i class="iconsminds-file-clipboard opcion-icon"></i>
                            <h5>Reportes</h5>
                            <p class="text-muted mb-0">Exportar datos e ingresos</p>
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