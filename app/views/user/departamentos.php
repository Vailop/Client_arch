<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel - <?= htmlspecialchars($desarrolloNombre ?? 'Desarrollo') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords"
        content="Archandél, desarrollos, Desarrollos, departamentos, Departamentos, casas, Casas, renta, Renta, venta, Venta, residencias, Residencias, oficinas, Oficinas, proyectos inmobiliarios, lujo, CDMX, área metropolitana">
    <meta name="description"
        content="Archandél Desarrollos Inmobiliarios en CDMX y área metropolitana. Creamos espacios de alto nivel que potencian la calidad de vida, combinando diseño, innovación y exclusividad.">

    <link rel="icon" href="<?= BASE_URL ?>favicon.png" type="image/x-icon">

    <link rel="stylesheet" href="<?= BASE_URL ?>font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/vendor/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/vendor/datatables.responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/main.css" />

    <style>
    /* Estilos de las pestañas del frontend */
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
                    <span>
                        <img alt="Profile Picture" src="<?= $urlAvatar; ?>" />
                    </span>
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
                    <?php else: ?>
                    <li>
                        <i class="iconsminds-folder-delete"></i>
                        <span class="d-inline-block">No hay desarrollos asignados</span>
                    </li>
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
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>home">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Departamentos</li>
                        </ol>
                    </nav>
                    <div class="separator mb-5"></div>
                </div>
            </div>

            <?php if (!empty($departamentos)): ?>
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="departamentoTabs" role="tablist">
                        <?php foreach($departamentos as $index => $depto): ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $index === 0 ? 'active' : '' ?>" id="depto<?= $depto['Dpto'] ?>-tab"
                                data-toggle="tab" href="#depto<?= $depto['Dpto'] ?>" role="tab"
                                aria-controls="depto<?= $depto['Dpto'] ?>"
                                aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                Departamento <?= htmlspecialchars($depto['Dpto']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content" id="departamentoTabsContent">
                        <?php foreach($departamentos as $index => $depto):
                            // -------------------------------------------------------------
                            // Extracción de datos del departamento para la iteración
                            // Los nombres de las variables vienen de la estructura del frontend
                            // que usa las claves de tu simulación de datos.
                            // -------------------------------------------------------------
                            $numDepto = $depto['Dpto'];
                            $precioCompraventa = (float)($depto['Precio_Compraventa'] ?? 0);
                            $superficie = (float)($depto['SuperficieReal'] ?? 0); // Muestra la superficie real
                            $filePlanos = trim($depto['File_Planos'] ?? ''); // Planos individuales del depto
                            // Los archivos de comprobante/recibo aquí son un poco ambiguos.
                            // En el frontend original se usa `File_Comprobante` y `fileRecibo`.
                            // Usaremos la lógica de tu nuevo frontend para mostrar los archivos.
                            // En este punto, solo tenemos el URL base para archivos:
                            $urlPlanos = $filePlanos ? $baseUrl . rawurlencode($filePlanos) : '';

                            // Se necesita un endpoint para el recibo más reciente y el comprobante más reciente
                            // Para mantener la consistencia con el frontend, asumiremos que no hay archivo general 
                            // de comprobante/recibo a nivel de departamento, sino en la tabla de pagos (más abajo).
                            // Ponemos un placeholder para el recibo que se mostrará en la ficha.
                            $fileReciboPlaceholder = ''; // Asume que no hay "Recibo de Pago" general del depto.
                            $urlReciboPlaceholder = ''; // Se cargará de la tabla de pagos.

                            // Cálculo para los KPI: Importe Pagado y Mensualidades Restantes (se mantiene la lógica de tu frontend)
                            $importePagado = 0;
                            $mensualidadesRestantes = 0;
                            $hoy = date('Y-m-d');
                            $saldoRestante = $precioCompraventa; // Inicialmente es el precio total

                            foreach ($depto['historial_pagos'] as $pago_historial) {
                                $monto = (float)$pago_historial['monto_esperado']; // Usamos esperado para el cálculo
                                $estatus_real = $pago_historial['estatus_real'] ?? 'pendiente';

                                // Si el pago está aprobado, se suma al pagado y se resta del restante
                                if ($estatus_real === 'aprobado') {
                                    $importePagado += $monto;
                                    $saldoRestante -= $monto;
                                }
                                // Si está pendiente (no pagado y no aprobado)
                                if (empty($pago_historial['id_pago_realizado'])) {
                                     $mensualidadesRestantes++;
                                }
                                
                                // Para la tarjeta de 'Recibo De Pago' (tercera tarjeta), mostramos el último recibo oficial si existe.
                                if (!empty($pago_historial['recibo_admin_url']) && empty($fileReciboPlaceholder)) {
                                     $fileReciboPlaceholder = $pago_historial['recibo_admin_url'];
                                     $urlReciboPlaceholder = BASE_URL . $fileReciboPlaceholder;
                                }
                            }
                            
                            $saldoRestante = max(0, $saldoRestante); // Aseguramos que no sea negativo
                        ?>
                        <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="depto<?= $numDepto ?>"
                            role="tabpanel" aria-labelledby="depto<?= $numDepto ?>-tab">

                            <div class="row mb-4">
                                <div class="col-lg-3">
                                    <div class="card mb-4 progress-banner">
                                        <div
                                            class="card-body justify-content-between d-flex flex-row align-items-center">
                                            <div>
                                                <i
                                                    class="iconsminds-hotel mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <p class="lead text-white"><?= number_format($superficie, 2) ?> M²
                                                        Superficie</p>
                                                    <small class="text-white-50">Precio:
                                                        $<?= number_format($precioCompraventa, 2) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="card mb-4 progress-banner">
                                        <div
                                            class="card-body justify-content-between d-flex flex-row align-items-center">
                                            <div>
                                                <i
                                                    class="iconsminds-financial mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <p class="lead text-white">$<?= number_format($importePagado, 2) ?>
                                                        Importe Pagado</p>
                                                    <small class="text-white-50">Restante:
                                                        $<?= number_format($saldoRestante, 2) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="card mb-4 progress-banner">
                                        <div
                                            class="card-body justify-content-between d-flex flex-row align-items-center">
                                            <div>
                                                <i
                                                    class="iconsminds-blueprint mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <small class="text-white-50">Documento del Dpto.</small>
                                                    <p class="lead text-white">Planos</p>
                                                </div>
                                            </div>
                                            <div>
                                                <?php if ($filePlanos): ?>
                                                <a href="<?= $urlPlanos ?>" target="_blank" rel="noopener" download
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
                                        <div
                                            class="card-body justify-content-between d-flex flex-row align-items-center">
                                            <div>
                                                <i
                                                    class="iconsminds-receipt-3 mr-2 text-white align-text-bottom d-inline-block"></i>
                                                <div>
                                                    <small class="text-white-50">Último Recibo Oficial</small>
                                                    <p class="lead text-white">Recibo De Pago</p>
                                                </div>
                                            </div>
                                            <div>
                                                <?php if ($fileReciboPlaceholder): ?>
                                                <a href="<?= $urlReciboPlaceholder ?>" target="_blank" rel="noopener"
                                                    download class="btn btn-light btn-sm">
                                                    <i class="simple-icon-cloud-download"></i> Descargar
                                                </a>
                                                <?php else: ?>
                                                <button class="btn btn-outline-light btn-sm" disabled>
                                                    <i class="simple-icon-ban"></i> Sin recibo
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Historial de Pagos - Departamento
                                                <?= $numDepto ?></h5>
                                            <?php if (empty($depto['historial_pagos'])): ?>
                                            <div class='alert alert-warning'>No hay historial de pagos programados para
                                                este departamento.</div>
                                            <?php else: ?>
                                            <table class="data-table data-table-feature responsive-table">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha Vencimiento</th>
                                                        <th>Estatus</th>
                                                        <th>Concepto</th>
                                                        <th>Monto Esperado</th>
                                                        <th>Comprobantes / Recibos</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    // Se reutiliza la variable $saldoRestante del cálculo de KPI para la columna "Restante"
                                                    $saldoRestanteTabla = $precioCompraventa; 

                                                    foreach($depto['historial_pagos'] as $pago): 
                                                        $estatus_real = $pago['estatus_real'] ?? '';
                                                        $montoEsperado = (float)$pago['monto_esperado'];
                                                        $fechaVencimiento = date('Y-m-d', strtotime($pago['fecha_vencimiento']));
                                                        
                                                        // 1. Determinar Estatus y Clase
                                                        $estatus = 'Pendiente';
                                                        $estatusClass = 'badge-primary';
                                                        
                                                        if ($estatus_real === 'aprobado') {
                                                            $estatus = 'Pagado / Aprobado';
                                                            $estatusClass = 'badge-success';
                                                            $saldoRestanteTabla -= $montoEsperado;
                                                        } elseif ($estatus_real === 'pendiente') {
                                                            $estatus = 'En Revisión';
                                                            $estatusClass = 'badge-warning';
                                                        } elseif ($estatus_real === 'rechazado') {
                                                            $estatus = 'Rechazado';
                                                            $estatusClass = 'badge-danger';
                                                        } elseif ($fechaVencimiento < $hoy && $estatus_real !== 'aprobado') {
                                                            $estatus = 'Vencido';
                                                            $estatusClass = 'badge-danger';
                                                        }
                                                        
                                                        // 2. Lógica para el botón 'Pagar'
                                                        $puede_pagar = ($estatus_real !== 'aprobado' && $estatus_real !== 'pendiente');
                                                        
                                                        // 3. URLs de archivos (si existen)
                                                        $urlReciboAdmin = !empty($pago['recibo_admin_url']) ? BASE_URL . $pago['recibo_admin_url'] : null;
                                                        $urlComprobanteCliente = !empty($pago['comprobante_cliente_url']) ? BASE_URL . $pago['comprobante_cliente_url'] : null;
                                                    ?>
                                                    <tr>
                                                        <td data-label="Vencimiento">
                                                            <?= date('d/m/Y', strtotime($fechaVencimiento)) ?></td>
                                                        <td data-label="Estatus"><span
                                                                class="badge <?= $estatusClass ?>"><?= $estatus ?></span>
                                                        </td>
                                                        <td data-label="Concepto">
                                                            <?= htmlspecialchars($pago['concepto'] ?? 'Cuota de Pago') ?>
                                                        </td>
                                                        <td data-label="Monto">$<?= number_format($montoEsperado, 2) ?>
                                                        </td>

                                                        <td data-label="Comprobantes">
                                                            <?php if ($urlReciboAdmin): ?>
                                                            <a href="<?= $urlReciboAdmin ?>" target="_blank"
                                                                class="btn btn-sm btn-success mb-1"
                                                                title="Recibo Oficial de la Empresa">
                                                                <i class="iconsminds-receipt-3"></i> Recibo Admin
                                                            </a>
                                                            <?php endif; ?>
                                                            <?php if ($urlComprobanteCliente): ?>
                                                            <a href="<?= $urlComprobanteCliente ?>" target="_blank"
                                                                class="btn btn-sm btn-info mb-1"
                                                                title="Comprobante Subido por el Cliente">
                                                                <i class="iconsminds-upload-1"></i> Comprobante Cliente
                                                            </a>
                                                            <?php endif; ?>
                                                            <?php if (!$urlReciboAdmin && !$urlComprobanteCliente): ?>
                                                            <span class="text-muted">Sin documentos</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td data-label="Acción">
                                                            <?php if ($puede_pagar): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                                data-toggle="modal" data-target="#subirPagoModal"
                                                                data-desarrollo-id="<?= htmlspecialchars($idDesa); ?>"
                                                                data-departamento-no="<?= htmlspecialchars($numDepto); ?>"
                                                                data-cronograma-id="<?= htmlspecialchars($pago['id_cronograma_pago']); ?>"
                                                                data-monto-esperado="<?= number_format($montoEsperado, 2, '.', ''); ?>"
                                                                data-vencimiento="<?= $fechaVencimiento; ?>"
                                                                onclick="abrirModalPago(this)">
                                                                <i class="iconsminds-credit-card"></i> Pagar
                                                            </button>
                                                            <?php else: ?>
                                                            <span class="text-muted small">
                                                                <?= ($estatus_real === 'pendiente') ? 'En revisión' : 'Pago Cubierto'; ?>
                                                            </span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <?php endif; ?>
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

    <div class="modal fade" id="subirPagoModal" tabindex="-1" role="dialog" aria-labelledby="subirPagoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subirPagoModalLabel">Registrar Pago Pendiente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?= BASE_URL ?>pagos/subir" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id_desarrollo" id="modalIdDesarrollo">
                        <input type="hidden" name="departamento_no" id="modalDepartamentoNo">
                        <input type="hidden" name="id_pago_programado" id="modalIdCronogramaPago">

                        <div class="card bg-dark text-white border-0 shadow-sm mb-3">
                            <div class="card-body p-3">
                                <h6 class="text-uppercase text-white-50 mb-2">Resumen del Pago</h6>
                                <p class="mb-1">
                                    <i class="iconsminds-building mr-1 text-primary"></i>
                                    Dpto.: <strong id="modalDepartamentoTitulo">N/A</strong>
                                </p>
                                <p class="mb-0">
                                    <i class="iconsminds-financial mr-1 text-success"></i>
                                    Monto requerido: <strong id="modalMontoRequerido">N/A</strong>
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="monto_real" class="font-weight-bold">Monto Pagado:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text text-success"><i class="iconsminds-dollar"></i></span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="monto_real" name="monto_real"
                                    placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="fecha_real" class="font-weight-bold">Fecha de Pago:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text text-info"><i
                                            class="iconsminds-calendar-4"></i></span>
                                </div>
                                <input type="date" class="form-control" id="fecha_real" name="fecha_real" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comprobante" class="font-weight-bold">Comprobante:</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="comprobante" name="comprobante"
                                    accept=".pdf,.jpg,.jpeg,.png" required>
                                <label class="custom-file-label" for="comprobante">Elige un archivo...</label>
                            </div>
                            <small class="form-text text-muted">Formatos permitidos: PDF, JPG, PNG</small>
                        </div>

                        <div class="form-group">
                            <label for="comentarios" class="font-weight-bold">Comentarios (Opcional):</label>
                            <textarea class="form-control" id="comentarios" name="comentarios" rows="3"
                                placeholder="Escribe aquí alguna nota..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            <i class="iconsminds-close"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="iconsminds-upload-to-cloud"></i> Subir Pago
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


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

    <script src="<?= BASE_URL ?>js/vendor/jquery-3.3.1.min.js"></script>
    <script src="<?= BASE_URL ?>js/vendor/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>js/vendor/perfect-scrollbar.min.js"></script>
    <script src="<?= BASE_URL ?>js/vendor/datatables.min.js"></script>
    <script src="<?= BASE_URL ?>js/dore.script.js"></script>
    <script src="<?= BASE_URL ?>js/scripts.js"></script>

    <script>
    // Función para pasar datos del botón al modal (Se mantiene tu lógica)
    function abrirModalPago(button) {
        const modal = document.getElementById('subirPagoModal');

        const idDesarrollo = button.getAttribute('data-desarrollo-id');
        const departamentoNo = button.getAttribute('data-departamento-no');
        const idCronogramaPago = button.getAttribute('data-cronograma-id');
        const montoEsperado = button.getAttribute('data-monto-esperado');
        const fechaVencimiento = button.getAttribute('data-vencimiento');

        modal.querySelector('#modalIdDesarrollo').value = idDesarrollo;
        modal.querySelector('#modalDepartamentoNo').value = departamentoNo;
        modal.querySelector('#modalIdCronogramaPago').value = idCronogramaPago;
        modal.querySelector('#modalDepartamentoTitulo').textContent = departamentoNo;
        modal.querySelector('#modalMontoRequerido').textContent = `$${parseFloat(montoEsperado).toFixed(2)}`;
        modal.querySelector('#monto_real').value = parseFloat(montoEsperado).toFixed(2);
        modal.querySelector('#fecha_real').value = '<?= $hoy ?>'; // Establecer fecha actual por defecto

    }

    var dataTableInstances = {};

    $(document).ready(function() {

        // Función para inicializar o reajustar una tabla por su contenedor de pestaña
        function handleDataTable(tabContentSelector) {
            const $table = $(tabContentSelector).find('.data-table');

            // 1. Verificar si la tabla ya está inicializada
            if (!dataTableInstances[tabContentSelector]) {
                // Si NO está inicializada, la inicializamos y guardamos la instancia de la API
                const dt = $table.DataTable({
                    "responsive": true,
                    "ordering": false,
                    "paging": false,
                    "info": false,
                    "searching": false
                });
                dataTableInstances[tabContentSelector] = dt;
            } else {
                // 2. Si YA está inicializada, solo la ajustamos
                const dt = dataTableInstances[tabContentSelector];
                dt.columns.adjust().responsive.recalc();
            }
        }

        // Inicializar la tabla de la primera pestaña visible al cargar
        // Usamos '#deptoXXX' del primer departamento activo (asumiendo que es el primero)
        handleDataTable($('.tab-pane.active').attr('id'));


        // Escuchar el evento de cambio de pestaña
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            // El selector del contenido de la pestaña activa es el valor del atributo href
            const tabId = $(e.target).attr('href');
            // Manejar la DataTables para el contenido de la nueva pestaña
            handleDataTable(tabId);
        });

        // Lógica para actualizar el nombre del archivo en el campo de Bootstrap (se mantiene)
        $('#comprobante').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
            if (fileName === '') {
                $(this).next('.custom-file-label').html('Elige un archivo...');
            }
        });
    });
    </script>
</body>

</html>