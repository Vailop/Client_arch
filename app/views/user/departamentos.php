<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" href="/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="/font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="/font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="/css/main.css" />

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
        <a class="navbar-logo" href="/home">
            <span class="logo d-none d-xs-block"></span>
            <span class="logo-mobile d-block d-xs-none"></span>
        </a>
        <div class="navbar-right">
            <div class="header-icons d-inline-block align-middle">
                <div class="d-none d-md-inline-block align-text-bottom mr-3">
                    <div class="custom-switch custom-switch-primary-inverse custom-switch-small pl-1"
                        data-toggle="tooltip" data-placement="left" title="Dark Mode">
                        <input class="custom-switch-input" id="switchDark" type="checkbox" checked>
                        <label class="custom-switch-btn" for="switchDark"></label>
                    </div>
                </div>
                <div class="position-relative d-inline-block">
                    <button class="header-icon btn btn-empty" type="button" id="notificationButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="simple-icon-bell"></i>
                        <span class="count">1</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right mt-3 position-absolute" id="notificationDropdown">
                    </div>
                </div>
                <button class="header-icon btn btn-empty d-none d-sm-inline-block" type="button" id="fullScreenButton">
                    <i class="simple-icon-size-fullscreen"></i>
                    <i class="simple-icon-size-actual"></i>
                </button>
            </div>
            <div class="user d-inline-block">
                <button class="btn btn-empty p-0" type="button" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <span class="name"><?= htmlspecialchars($nombreUsuario); ?></span>
                    <span><img alt="Profile Picture" src="<?= $urlAvatar; ?>" /></span>
                </button>
                <div class="dropdown-menu dropdown-menu-right mt-3">
                    <a class="dropdown-item" href="#">Cuenta</a>
                    <a class="dropdown-item" href="#">Soporte</a>
                    <a class="dropdown-item" href="<?= BASE_URL ?>logout">Cerrar sesión</a>
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
        <div class="sub-menu">
            <div class="scroll">
                <ul class="list-unstyled" data-link="dashboard">
                    <?php if (!empty($desarrollos_list)): ?>
                    <?php foreach ($desarrollos_list as $desarrollo): ?>
                    <li>
                        <a href="<?= BASE_URL ?>departamentos?IdUsuario=<?= $idUsuario; ?>&IdDesarrollo=<?= $desarrollo['id_desarrollo']; ?>&mes=<?= $mesAnterior; ?>"
                            data-idDesarrollo="<?= $desarrollo['id_desarrollo']; ?>">
                            <i class="iconsminds-folders"></i>
                            <span class="d-inline-block"><?= htmlspecialchars($desarrollo['nombre']); ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1><?= htmlspecialchars($desarrolloNombre); ?></h1>
                    <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
                        <ol class="breadcrumb pt-0">
                            <li class="breadcrumb-item"><a href="/home">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Departamentos</li>
                        </ol>
                    </nav>
                    <div class="separator mb-5"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?php if (empty($departamentos_list)): ?>
                    <div class='alert alert-info'>No tienes departamentos asignados a este desarrollo.</div>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <h5>Departamento</h5>
                                </th>
                                <th>
                                    <h5>Estado</h5>
                                </th>
                                <th>
                                    <h5>Acciones</h5>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departamentos_list as $departamento): ?>
                            <tr>
                                <td data-label="Departamento"><?= htmlspecialchars($departamento['departamento_no']); ?>
                                </td>
                                <td data-label="Estado">Aqui estaran los archivos</td>
                                <td data-label="Acciones">
                                    <a href="#" title="Ver detalles"><i class="iconsminds-folder"></i></a>
                                    <a href="#" title="Ver Avance"><i class="iconsminds-chart-bar-3"></i></a>
                                    <a href="#" title="Ver Pagos"><i class="iconsminds-wallet"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="page-footer">
    </footer>

    <script src="/js/vendor/jquery-3.3.1.min.js"></script>
    <script src="/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="/js/vendor/perfect-scrollbar.min.js"></script>
    <script src="/js/dore.script.js"></script>
    <script src="/js/scripts.js"></script>
</body>

</html>