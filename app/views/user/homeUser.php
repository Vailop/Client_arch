<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Archandel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords"
        content="Archandél, desarrollos, Desarrollos, departamentos, Departamentos, casas, Casas, renta, Renta, venta, Venta, residencias, Residencias, oficinas, Oficinas, proyectos inmobiliarios, lujo, CDMX, área metropolitana">
    <meta name="description"
        content="Archandél Desarrollos Inmobiliarios en CDMX y área metropolitana. Creamos espacios de alto nivel que potencian la calidad de vida, combinando diseño, innovación y exclusividad.">

    <link rel="icon" href="/favicon.png" type="image/x-icon">

    <link rel="stylesheet" href="/font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="/font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="/css/vendor/fullcalendar.min.css" />
    <link rel="stylesheet" href="/css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="/css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/css/main.css" />

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
                        <a href="/departamentos?IdUsuario=<?= $idUsuario; ?>&IdDesarrollo=<?= $desarrollo['id_desarrollo']; ?>&mes=<?= $mesAnterior; ?>"
                            data-idDesarrollo="<?= $desarrollo['id_desarrollo']; ?>">
                            <i class="iconsminds-folders"></i>
                            <span class="d-inline-block"><?= htmlspecialchars($desarrollo['nombre']); ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <li><i class="iconsminds-folder-delete"></i><span class="d-inline-block">No hay desarrollos
                            asignados</span></li>
                    <?php endif; ?>
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

            <div class="row">
                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-hotel mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $total_desarrollos; ?> Desarrollos</p>
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
                                    <p class="lead text-white"><?= $total_departamentos; ?> Departamentos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-box-close mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $total_m2_formateado; ?> M<sup>2</sup> Totales</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card mb-4 progress-banner">
                        <div class="card-body justify-content-between d-flex flex-row align-items-center">
                            <div>
                                <i class="iconsminds-box-close mr-2 text-white align-text-bottom d-inline-block"></i>
                                <div>
                                    <p class="lead text-white"><?= $total_m2_formateado; ?> M<sup>2</sup> Totales</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <?php foreach ($desarrollos_list as $desarrollo): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card dashboard-progress">
                        <div class="position-absolute card-top-buttons">
                            <button class="btn btn-header-light icon-button" onclick="location.reload()">
                                <i class="simple-icon-refresh"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Avance De Obra <?= htmlspecialchars($desarrollo['nombre']); ?></h5>
                            <?php
                            $datos_avance = $avances_por_desarrollo[$desarrollo['id_desarrollo']] ?? [];
                            foreach ($categorias_orden as $categoria):
                                $avance = $datos_avance[$categoria] ?? ['valorActualFmt' => '0', 'valorObjetivoFmt' => '1', 'porcentaje' => 0];
                            ?>
                            <div class="mb-4">
                                <p class="mb-2"><?= htmlspecialchars($categoria) ?>
                                    <span
                                        class="float-right text-muted"><?= $avance['valorActualFmt']; ?>/<?= $avance['valorObjetivoFmt']; ?></span>
                                </p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"
                                        aria-valuenow="<?= $avance['porcentaje']; ?>" aria-valuemin="0"
                                        aria-valuemax="100" style="width: <?= $avance['porcentaje']; ?>%;"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Calendario De Pagos</h5>
                            <div class="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php foreach ($desarrollos_list as $desarrollo): ?>
            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3" id="salesTitle-<?= $desarrollo['id_desarrollo']; ?>">Plusvalía
                                <?= htmlspecialchars($desarrollo['nombre']); ?></h5>
                            <div style="height:320px">
                                <canvas id="salesChart-<?= $desarrollo['id_desarrollo']; ?>"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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

    <script src="/js/vendor/jquery-3.3.1.min.js"></script>
    <script src="/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="/js/vendor/Chart.bundle.min.js"></script>
    <script src="/js/vendor/chartjs-plugin-datalabels.js"></script>
    <script src="/js/vendor/moment.min.js"></script>
    <script src="/js/vendor/fullcalendar.min.js"></script>
    <script src="/js/vendor/fullcalendar_locale_all.min.js"></script>
    <script src="/js/vendor/perfect-scrollbar.min.js"></script>
    <script src="/js/vendor/progressbar.min.js"></script>
    <script src="/js/vendor/bootstrap-notify.min.js"></script>
    <script src="/js/vendor/mousetrap.min.js"></script>
    <script src="/js/payments.notify.js"></script>
    <script src="/js/dore.script.js"></script>
    <script src="/js/scripts.js"></script>

    <script>
    // Lógica de JavaScript para el dashboard
    (async function() {
        const anio = new Date().getFullYear();
        const canvases = document.querySelectorAll('canvas[id^="salesChart-"]');

        for (const cv of canvases) {
            const parts = cv.id.split('-');
            const idDes = Number(parts[1]);
            if (!idDes) continue;

            try {
                const res = await fetch(`/api/plusvalia?IdDesarrollo=${idDes}&anio=${anio}`);
                const json = await res.json();

                const vals = json.valorM2.filter(v => v != null);
                const min = vals.length ? Math.min(...vals) : 0;
                const max = vals.length ? Math.max(...vals) : 100;
                const pad = Math.max(10, (max - min) * 0.1);

                const ctx = cv.getContext('2d');
                new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: json.labels,
                        datasets: [{
                            label: "Valor m²",
                            data: json.valorM2,
                            borderColor: window.themeColor1 || '#00365a',
                            pointBackgroundColor: window.foregroundColor || '#00365a',
                            pointBorderColor: window.themeColor1 || '#ffffff',
                            pointHoverBackgroundColor: window.themeColor1 || '#ffffff',
                            pointHoverBorderColor: window.foregroundColor || '#ffffff',
                            pointRadius: 6,
                            pointBorderWidth: 2,
                            pointHoverRadius: 8,
                            spanGaps: true,
                            fill: false
                        }]
                    },
                    options: {
                        plugins: {
                            datalabels: {
                                display: false
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        tooltips: {
                            callbacks: {
                                label: function(item) {
                                    var val = item.yLabel;
                                    var varPct = json.varPct[item.index];
                                    var line = ' $/m²: ' + Number(val).toLocaleString('es-MX', {
                                        maximumFractionDigits: 2
                                    });
                                    if (varPct !== null && varPct !== undefined) line += ' (Δ ' +
                                        varPct + '%)';
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
                                    max: Math.ceil(max + pad)
                                }
                            }],
                            xAxes: [{
                                gridLines: {
                                    display: false
                                }
                            }]
                        }
                    }
                });
            } catch (e) {
                console.error('Error cargando plusvalía para IdDesarrollo=' + idDes, e);
            }
        }
    })();

    // Lógica de JavaScript para el Calendario de Pagos
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.querySelector('.calendar');
        // Aseguramos que $idUsuario esté disponible en PHP y lo codificamos a JS
        const idUsuario = <?= json_encode($idUsuario); ?>;

        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                editable: false,
                navLinks: true,

                // Fuente de datos: llama a tu API
                events: function(fetchInfo, successCallback, failureCallback) {
                    const start = moment(fetchInfo.start).format('YYYY-MM-DD');
                    const end = moment(fetchInfo.end).format('YYYY-MM-DD');

                    // Usando BASE_URL en la llamada de fetch del calendario
                    fetch(
                            `<?= BASE_URL ?>api/eventos_pagos?idUsuario=${idUsuario}&start=${start}&end=${end}`
                        )
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error al cargar eventos del calendario.');
                            }
                            return response.json();
                        })
                        .then(data => {
                            successCallback(data);
                        })
                        .catch(error => {
                            console.error('Error del calendario:', error);
                            failureCallback(error);
                        });
                },

                // Personalización de eventos (tooltips, etc.)
                eventDidMount: function(info) {
                    // Puedes agregar lógica para tooltips usando los datos extra
                    // info.event.extendedProps.estatus, etc.
                },
                eventContent: function(arg) {
                    // Muestra el título y estilo
                    const statusClass = arg.event.extendedProps.estatus.toLowerCase().replace(
                        /[^a-z0-9]/g, '');
                    return {
                        html: '<div class="fc-event-main-content ' + statusClass +
                            '" style="background-color: ' + arg.event.backgroundColor +
                            '; color: white; border-color: ' + arg.event.backgroundColor +
                            '; border-radius: 3px; padding: 2px;">' + arg.event.title + '</div>'
                    };
                }
            });
            calendar.render();
        }
    });
    </script>
</body>

</html>