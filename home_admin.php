<?php
    require 'config.php';
    session_start();

    // Verificar que sea administrador
    if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != 1) {
        header("Location: login.php");
        exit();
    }

    $conexion = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $idUsuario = $_SESSION["idusuario"];
    $urlAvatar = $_SESSION["Avatar"];

    // Consulta de desarrollos con KPIs
    $sqlDesarrollos = "SELECT d.IdDesarrollo, d.Nombre_Desarrollo, d.Descripcion, d.RutaLogo, d.Estatus,
                            (SELECT COUNT(DISTINCT ud.IdUsuario) 
                            FROM tbr_usuario_desarrollos ud 
                            WHERE ud.IdDesarrollo = d.IdDesarrollo AND ud.Estatus = 1) as TotalClientes,
                            (SELECT COUNT(*) 
                            FROM tbr_comprobantes_pago cp 
                            WHERE cp.IdDesarrollo = d.IdDesarrollo AND cp.Estatus = 'Pendiente') as ComprobantesPendientes,
                            (SELECT COUNT(*) 
                            FROM tbr_pagos p 
                            WHERE p.IdDesarrollo = d.IdDesarrollo AND p.Estatus = 1 AND p.FechaPago < CURDATE()) as PagosVencidos,
                            (SELECT ROUND(AVG(a.ValorActual / a.ValorObjetivo * 100), 1)
                            FROM tbr_avance_desarrollo a 
                            WHERE a.IdDesarrollo = d.IdDesarrollo) as AvancePromedio
                        FROM tbp_desarrollos d
                        WHERE d.Estatus = 1
                        ORDER BY d.Nombre_Desarrollo ASC";

    $desarrollos = $conexion->query($sqlDesarrollos);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - Archandel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    
    <link rel="icon" href="/favicon.png" type="image/x-icon">
    
    <link rel="stylesheet" href="font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="css/main.css" />
    
    <style>
        .kpis-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .kpi-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
        }
        
        .kpi-item i {
            margin-right: 0.3rem;
        }
        
        .nuevo-desarrollo-card {
            border: 2px dashed #dee2e6;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .nuevo-desarrollo-card:hover {
            border-color: #007bff;
            background-color: #e7f3ff;
        }
        
        .nuevo-desarrollo-content {
            text-align: center;
            color: #6c757d;
        }
        
        .nuevo-desarrollo-content i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .alerta-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
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
                    ?>
                        <li>
                            <a href="admin/desarrollo_detalle.php?id=<?= $devMenu['IdDesarrollo'] ?>"><i class="iconsminds-folders"></i><span class="d-inline-block"><?= htmlspecialchars($devMenu['Nombre_Desarrollo']) ?></span></a>
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

    <!-- CONTENIDO PRINCIPAL -->
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1>Panel de Administración</h1>
                    <div class="separator mb-5"></div>
                </div>
            </div>
            
            <!-- GRID DE DESARROLLOS -->
            <div class="row">
                <?php if ($desarrollos && $desarrollos->num_rows > 0): ?>
                    <?php while ($dev = $desarrollos->fetch_assoc()): 
                        $alertas = (int)$dev['ComprobantesPendientes'] + (int)$dev['PagosVencidos'];
                    ?>
                    <div class="col-lg-3 col-md-3 col-12 mb-4">
                        <div class="card desarrollo-card" onclick="window.location.href='admin/desarrollo_detalle.php?id=<?= $dev['IdDesarrollo'] ?>'">
                            <?php if (!empty($dev['RutaLogo'])): ?>
                                <img src="<?= htmlspecialchars($dev['RutaLogo']) ?>" class="desarrollo-img card-img-top" alt="<?= htmlspecialchars($dev['Nombre_Desarrollo']) ?>" onerror="this.src='img/profile-pic-generic.jpg'">
                            <?php else: ?>
                                <div class="desarrollo-img card-img-top bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="iconsminds-shop-4" style="font-size: 3rem; color: #000000;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($alertas > 0): ?>
                                <span class="badge badge-pill badge-danger position-absolute alerta-badge" style="top: 10px; right: 10px; font-size: 0.9rem; padding: 0.5rem 0.8rem;"><?= $alertas ?> Alerta<?= $alertas > 1 ? 's' : '' ?></span>
                            <?php endif; ?>
                            
                            <div class="card-body card-body-desarrollo">
                                <h5 class="desarrollo-title">
                                    <?= htmlspecialchars($dev['Nombre_Desarrollo']) ?>
                                </h5>
                                
                                <!-- KPIs -->
                                <div class="kpis-container">
                                    <div class="kpi-item">
                                        <i class="simple-icon-people text-primary"></i><strong><?= $dev['TotalClientes'] ?></strong>&nbsp;Cliente<?= $dev['TotalClientes'] != 1 ? 's' : '' ?>
                                    </div>
                                    
                                    <?php if ($dev['ComprobantesPendientes'] > 0): ?>
                                    <div class="kpi-item text-warning">
                                        <i class="simple-icon-doc"></i><strong><?= $dev['ComprobantesPendientes'] ?></strong>&nbsp;Pendiente<?= $dev['ComprobantesPendientes'] != 1 ? 's' : '' ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($dev['PagosVencidos'] > 0): ?>
                                    <div class="kpi-item text-danger">
                                        <i class="simple-icon-exclamation"></i><strong><?= $dev['PagosVencidos'] ?></strong>&nbsp;Vencido<?= $dev['PagosVencidos'] != 1 ? 's' : '' ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Avance de Obra -->
                                <?php if ($dev['AvancePromedio'] !== null): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Avance de Obra</small>
                                        <small class="text-muted"><strong><?= number_format($dev['AvancePromedio'], 1) ?>%</strong></small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <?php 
                                            $avance = $dev['AvancePromedio'];
                                            $colorClass = $avance >= 80 ? 'bg-success' : ($avance >= 50 ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <div class="progress-bar <?= $colorClass ?>" role="progressbar" style="width: <?= $avance ?>%" aria-valuenow="<?= $avance ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <button class="btn btn-primary btn-block" type="button">
                                    <i class="simple-icon-arrow-right"></i> Gestionar Desarrollo
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                
                <!-- TARJETA NUEVO DESARROLLO -->
                <div class="col-lg-3 col-md-3 col-12 mb-4">
                    <div class="card nuevo-desarrollo-card" onclick="alert('Próximamente: Crear Nuevo Desarrollo')">
                        <div class="nuevo-desarrollo-content">
                            <i class="iconsminds-add"></i>
                            <h5>Nuevo Desarrollo</h5>
                            <p class="text-muted mb-0">Agregar proyecto inmobiliario</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!$desarrollos || $desarrollos->num_rows === 0): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No hay desarrollos registrados</h4>
                        <p class="mb-0">Comienza agregando tu primer desarrollo inmobiliario.</p>
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
                        <p class="mb-0 text-muted">© 2025 ARCHANDEL. Todos los derechos reservados.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/vendor/jquery-3.3.1.min.js"></script>
    <script src="js/vendor/bootstrap.bundle.min.js"></script>
    <script src="js/vendor/perfect-scrollbar.min.js"></script>
    <script src="js/dore.script.js"></script>
    <script src="js/scripts.js"></script>
    
    <script>
        console.log('Panel Admin cargado correctamente');
    </script>
</body>

</html>

<?php $conexion->close(); ?>