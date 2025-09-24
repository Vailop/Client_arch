<?php
    require 'config.php';
    // Verificar la sesión para asegurarse de que el usuario esté autenticado
    session_start();

    if (!isset($_SESSION["nombre"]) || !isset($_SESSION["idusuario"])) {
        // Si no está autenticado, redirigir al formulario de login
        echo "<script>window.location.href = 'login.html';</script>";
        exit();
    } else {
        // Conectar a la base de datos 'admon_arch2' usando las constantes del config.php
        $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($conexion->connect_error) {
            die("Error de conexión a la base de datos: " . $conexion->connect_error);
        }

        $idUsuario = $_SESSION["idusuario"];
        $urlAvatar = isset($_SESSION["Avatar"]) ? $_SESSION["Avatar"] : 'img/default-avatar.png';

        // Obtener los parámetros de la URL
        $idDesa = isset($_GET['IdDesarrollo']) ? (int)$_GET['IdDesarrollo'] : null;
        $mesSeleccionado = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date("m");

        $desarrollo = "Desarrollo no encontrado";
        
        if (!empty($idDesa)) {

        }
    }
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" href="/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="css/main.css" />

    <style>
    table {
        border-collapse: collapse;
        margin: 0;
        margin-top: 2%;
        padding: 0;
        width: 100%;
        table-layout: fixed;
    }

    table thead tr th {
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
            <a href="#" class="menu-button d-none d-md-block"><svg class="main" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 9 17">
                    <rect x="0.48" y="0.5" width="7" height="1" />
                    <rect x="0.48" y="7.5" width="7" height="1" />
                    <rect x="0.48" y="15.5" width="7" height="1" />
                </svg><svg class="sub" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 17">
                    <rect x="1.56" y="0.5" width="16" height="1" />
                    <rect x="1.56" y="7.5" width="16" height="1" />
                    <rect x="1.56" y="15.5" width="16" height="1" />
                </svg></a>
            <a href="#" class="menu-button-mobile d-xs-block d-sm-block d-md-none"><svg
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26 17">
                    <rect x="0.5" y="0.5" width="25" height="1" />
                    <rect x="0.5" y="7.5" width="25" height="1" />
                    <rect x="0.5" y="15.5" width="25" height="1" />
                </svg></a>
        </div>
        <a class="navbar-logo" href="Dashboard.Default.html"><span class="logo d-none d-xs-block"></span><span
                class="logo-mobile d-block d-xs-none"></span></a>
        <div class="navbar-right">
            <div class="header-icons d-inline-block align-middle">
                <div class="d-none d-md-inline-block align-text-bottom mr-3">
                    <div class="custom-switch custom-switch-primary-inverse custom-switch-small pl-1"
                        data-toggle="tooltip" data-placement="left" title="Dark Mode"><input class="custom-switch-input"
                            id="switchDark" type="checkbox" checked><label class="custom-switch-btn"
                            for="switchDark"></label></div>
                </div>
                <div class="position-relative d-inline-block"><button class="header-icon btn btn-empty" type="button"
                        id="notificationButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                            class="simple-icon-bell"></i><span class="count">1</span></button>
                    <div class="dropdown-menu dropdown-menu-right mt-3 position-absolute" id="notificationDropdown">
                        <div class="scroll">
                            <div class="d-flex flex-row mb-3 pb-3 border-bottom"><a href="#"><img
                                        src="img/profiles/l-2.jpg" alt="Notification Image"
                                        class="img-thumbnail list-thumbnail xsmall border-0 rounded-circle" /></a>
                                <div class="pl-3"><a href="#">
                                        <p class="font-weight-medium mb-1">Joisse Kaycee just sent a new comment!</p>
                                        <p class="text-muted mb-0 text-small">09.04.2018 - 12:45</p>
                                    </a></div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="header-icon btn btn-empty d-none d-sm-inline-block" type="button"
                    id="fullScreenButton"><i class="simple-icon-size-fullscreen"></i><i
                        class="simple-icon-size-actual"></i></button>
            </div>
            <div class="user d-inline-block"><button class="btn btn-empty p-0" type="button" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false"><span
                        class="name"><?php echo $_SESSION["nombre"];?></span><span><img alt="Profile Picture"
                            src="<?php echo $urlAvatar;?>" /></span></button>
                <div class="dropdown-menu dropdown-menu-right mt-3"><a class="dropdown-item" href="#">Cuenta</a><a
                        class="dropdown-item" href="#">Soporte</a><a class="dropdown-item"
                        href="cerrar_sesion.php">Cerrar sesión</a></div>
            </div>
        </div>
    </nav>

    <div class="menu">
        <div class="main-menu">
            <div class="scroll">
                <ul class="list-unstyled">
                    <li><a href="#dashboard"><i class="iconsminds-hotel"></i><span>Desarrollo (s)</span></a></li>
                </ul>
            </div>
        </div>
        <div class="sub-menu">
            <div class="scroll">
                <ul class="list-unstyled" data-link="dashboard">
                    <li><i class="iconsminds-folder-delete"></i><span class="d-inline-block">No hay desarrollos
                            asignados</span></li>
                </ul>
            </div>
        </div>
    </div>

    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1><?php echo htmlspecialchars($desarrollo, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
                        <ol class="breadcrumb pt-0">
                            <li class="breadcrumb-item"><a href="home.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Departamentos</li>
                        </ol>
                    </nav>
                    <div class="separator mb-5"></div>
                </div>
            </div>

            <?php
            if (empty($idDesa) || empty($idUsuario)) {
                echo "<div class='alert alert-warning'>Error: Faltan parámetros necesarios (IdDesarrollo o IdUsuario). Por favor, haz clic en un desarrollo desde el menú lateral.</div>";
            } else {
                echo "<div class='alert alert-info'>El login funciona correctamente. Ahora debes adaptar esta sección para que lea los datos de tu base de datos 'admon_arch2'.</div>";
            }
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