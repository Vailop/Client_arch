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
    <link rel="stylesheet" href="css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="css/main.css" />

    <style>
        /**====== Authentication css end ======**/
        table {
            border-collapse: collapse;
            margin: 0;
            margin-top: 2%;
            padding: 0;
            width: 100%;
            table-layout: fixed;
        }

        table thead tr th{
            background-color: #00365a;
            color: #ffffff;
        }
        
        table tr {
            background-color: #ffffff;
            border: 1px solid #00365a;
            padding: .35em;
        }

        table th,
        table td {
            padding: .625em;
            text-align: center;
            border: 1px solid #00365a;
        }

        table td {
            color: #00365a;
            font-size: 13px;
        }

        table td a i {
            color: #00365a;
            font-size: 18px;
        }

        table td a i:hover {
            color: #00365a;
        }

        table th h5 {
            color: #ffffff;
            font-weight: 300;
            font-size: 15px;
            text-transform: capitalize;
            margin-bottom: 0;
        }

        table th {
            letter-spacing: .1em;
        }

        @media screen and (max-width: 600px) {
            table thead {
                border: none;
                clip: rect(0 0 0 0);
                height: 1px;
                margin: -1px;
                overflow: hidden;
                padding: 0;
                position: absolute;
                width: 1px;
            }

            table tr {
                border-bottom: 1px solid #00365a;
                display: block;
                margin-bottom: .625em;
            }

            table td {
                border-bottom: 1px solid #00365a;
                display: block;
                font-size: .8em;
                text-align: right;
            }

            table td::before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                text-transform: uppercase;
            }
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

                        // CORREGIDO: Usar campos correctos de tbr_usuario_desarrollos
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

        <?php
            // Solo continuar si tenemos los datos necesarios
            if (empty($idDesa) || empty($idUsuario)) {
                echo "<div class='alert alert-warning'>Error: Faltan parámetros necesarios (IdDesarrollo o IdUsuario)</div>";
            } else {
                
                // ===== BLOQUE B: consulta de departamentos + pintado de tabla =====
                // Normaliza mes/año actual y anterior
                $anioSel = (int)date('Y');
                $mesSel  = (int)$mesSeleccionado;
                $mesPrev = $mesSel - 1; 
                $anioPrev = $anioSel;

                $dirMes  = sprintf('COM%02d', (int)$mesSeleccionado);                 // COM08
                $carpeta = $carpeta ?? carpetaDesarrollo($rutaImagenes,'');           // carpeta real del desarrollo
                $appUrl  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/').'/';          // /Clientes_Archandel/
                $appFs   = rtrim(str_replace('\\','/', realpath(__DIR__)), '/').'/';  // C:/xampp/htdocs/Clientes_Archandel/

                $baseRel = 'desarrollos/'.$carpeta.'/'.$anioSel.'/'.$dirMes.'/';      // desarrollos/san_pedro_de_los_pinos/2025/COM08/
                $baseUrl = $appUrl.$baseRel;                                          // URL para href
                $baseFs  = $appFs.$baseRel;                                           // ruta física (opcional para validar existencia)

                if ($mesPrev === 0) { 
                    $mesPrev = 12; 
                    $anioPrev = $anioSel - 1; 
                }

                // CORREGIDO: Primera consulta con campos correctos de tbr_usuario_desarrollos
                $consultaBase = "SELECT UD.Dpto, UD.M2Inicial, UD.File_Planos, UD.File_Comprobante, 
                                        DCM_CUR.M2Mensual AS M2Cur, DCM_PREV.M2Mensual AS M2Prev, 
                                        DCM_BASE.MinM2 AS M2Base
                                FROM tbr_usuario_desarrollos UD
                                JOIN tbp_desarrollos DS ON DS.IdDesarrollo = UD.IdDesarrollo
                                
                                LEFT JOIN tbr_desarrollos_costo_mensual DCM_CUR
                                    ON DCM_CUR.IdDesarrollo = UD.IdDesarrollo
                                    AND DCM_CUR.Mes = ? AND DCM_CUR.Anio = ?
                                    
                                LEFT JOIN tbr_desarrollos_costo_mensual DCM_PREV
                                    ON DCM_PREV.IdDesarrollo = UD.IdDesarrollo
                                    AND DCM_PREV.Mes = ? AND DCM_PREV.Anio = ?
                                    
                                LEFT JOIN (
                                    SELECT IdDesarrollo, Anio, MIN(M2Mensual) AS MinM2
                                    FROM tbr_desarrollos_costo_mensual
                                    WHERE Anio = ?
                                    GROUP BY IdDesarrollo, Anio
                                ) DCM_BASE ON DCM_BASE.IdDesarrollo = UD.IdDesarrollo AND DCM_BASE.Anio = ?
                                
                                WHERE UD.IdUsuario = ? AND UD.IdDesarrollo = ? AND UD.Estatus = 1
                                ORDER BY UD.Dpto";

                $stmt = $conexion->prepare($consultaBase);
                if (!$stmt) {
                    die("Error en prepare: " . $conexion->error);
                }

                $stmt->bind_param('iiiiiiii', $mesSel, $anioSel, $mesPrev, $anioPrev, $anioSel, $anioSel, $idUsuario, $idDesa);
                $stmt->execute();
                $resultado = $stmt->get_result();

                // CORREGIDO: Consulta de pagos usando campos correctos
                $consultaPagos = "SELECT Dpto, 
                                COUNT(*) AS PagosPendientes,
                                SUM(Monto) AS ImportePendiente,
                                MIN(CASE WHEN FechaPago >= CURDATE() THEN FechaPago END) AS ProximoPago
                                FROM tbr_pagos
                                WHERE IdUsuario = ? AND IdDesarrollo = ? AND Estatus = 1
                                GROUP BY Dpto";

                $stmtPagos = $conexion->prepare($consultaPagos);
                if (!$stmtPagos) {
                    die("Error en prepare pagos: " . $conexion->error);
                }

                $stmtPagos->bind_param('ii', $idUsuario, $idDesa);
                $stmtPagos->execute();
                $resultadoPagos = $stmtPagos->get_result();

                // Crear array de pagos indexado por departamento
                $pagosData = [];
                while ($pago = $resultadoPagos->fetch_assoc()) {
                    $pagosData[$pago['Dpto']] = $pago;
                }
                $stmtPagos->close();

                if ($resultado->num_rows > 0) {
                    echo "<div class='row mt-1'>";
                        echo '<div class="col-lg-12 col-md-12">';
                            echo '<div class="card">';
                                echo '<div class="card-body">';
                                    echo '<table>';
                                        echo '<thead>';
                                            echo '<tr class="text-center">';
                                                echo '<th scope="col">Departamento</th>';
                                                echo '<th scope="col">Superficie</th>';
                                                echo '<th scope="col">Plusvalía (m/m)</th>';
                                                echo '<th scope="col">Plusvalía Acumulada</th>';
                                                echo '<th scope="col">Edo. de Cuenta</th>';
                                                echo '<th scope="col">Planos</th>';
                                                echo '<th scope="col">Pagos Pendientes</th>';
                                                echo '<th scope="col">Importe Pendiente</th>';
                                                echo '<th scope="col">Próximo Pago</th>';
                                            echo '</tr>';
                                        echo '</thead>';
                                        echo '<tbody>';
                                            while ($fila = $resultado->fetch_assoc()) {
                                                // Datos básicos
                                                $dpto = (string)($fila['Dpto'] ?? '');
                                                $m2   = isset($fila['M2Inicial']) ? number_format((float)$fila['M2Inicial'], 2).' m²' : '—';

                                                // Calcular plusvalía mes a mes y acumulada
                                                $m2Cur = $fila['M2Cur'] ? (float)$fila['M2Cur'] : null;
                                                $m2Prev = $fila['M2Prev'] ? (float)$fila['M2Prev'] : null;
                                                $m2Base = $fila['M2Base'] ? (float)$fila['M2Base'] : null;

                                                $plusvaliaMensual = '';
                                                $plusvaliaAcumulada = '';

                                                if ($m2Cur !== null && $m2Prev !== null && $m2Prev > 0) {
                                                    $varMensual = (($m2Cur - $m2Prev) / $m2Prev) * 100;
                                                    $plusvaliaMensual = number_format($varMensual, 2) . '%';
                                                } else {
                                                    $plusvaliaMensual = '—';
                                                }

                                                if ($m2Cur !== null && $m2Base !== null && $m2Base > 0) {
                                                    $varAcumulada = (($m2Cur - $m2Base) / $m2Base) * 100;
                                                    $plusvaliaAcumulada = number_format($varAcumulada, 2) . '%';
                                                } else {
                                                    $plusvaliaAcumulada = '—';
                                                }

                                                echo "<tr class='text-center'>";
                                                    echo "<td data-label='Departamento'>" . htmlspecialchars($dpto) . "</td>";
                                                    echo "<td data-label='Superficie'>" . htmlspecialchars($m2) . "</td>";
                                                    echo "<td data-label='Plusvalía (m/m)'>" . $plusvaliaMensual . "</td>";
                                                    echo "<td data-label='Plusvalía Acumulada'>" . $plusvaliaAcumulada . "</td>";
                                                    
                                                    $fnEdo = trim($fila['File_Comprobante'] ?? '');
                                                    echo '<td data-label="Edo. de Cuenta">';
                                                    if ($fnEdo) {
                                                        $url = $baseUrl . rawurlencode($fnEdo);
                                                        echo '<a class="btn btn-outline-primary btn-sm" href="'.$url.'" target="_blank" rel="noopener" download>Descargar</a>';
                                                    } else {
                                                        echo '<button class="btn btn-outline-secondary btn-sm" disabled>Sin archivo</button>';
                                                    }
                                                    echo '</td>';

                                                    $fnPlano = trim($fila['File_Planos'] ?? '');
                                                    echo '<td data-label="Planos">';
                                                    if ($fnPlano) {
                                                        $url = $baseUrl . rawurlencode($fnPlano);
                                                        echo '<a class="btn btn-outline-primary btn-sm" href="'.$url.'" target="_blank" rel="noopener" download>Descargar</a>';
                                                    } else {
                                                        echo '<button class="btn btn-outline-secondary btn-sm" disabled>Sin archivo</button>';
                                                    }
                                                    echo '</td>';
                                                    
                                                    // Datos de pagos
                                                    $pagoInfo = $pagosData[$dpto] ?? null;
                                                    echo "<td data-label='Pagos Pendientes'>" . ($pagoInfo ? $pagoInfo['PagosPendientes'] : '0') . "</td>";
                                                    echo "<td data-label='Importe Pendiente'>" . ($pagoInfo ? '$'.number_format($pagoInfo['ImportePendiente'], 2) : '$0.00') . "</td>";
                                                    echo "<td data-label='Próximo Pago'>" . ($pagoInfo && $pagoInfo['ProximoPago'] ? date('d/m/Y', strtotime($pagoInfo['ProximoPago'])) : '—') . "</td>";
                                                echo "</tr>";
                                            }
                                        echo '</tbody>';
                                    echo '</table>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                } else {
                    echo "<div class='alert alert-info'>No se encontraron departamentos para este desarrollo y usuario.</div>";
                }
                $stmt->close();

            } // Cierre correcto del if principal
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
    <script src="js/vendor/perfect-scrollbar.min.js"></script>
    <script src="js/dore.script.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>