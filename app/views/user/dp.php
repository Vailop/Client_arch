<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel - <?= htmlspecialchars($desarrolloNombre) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <link rel="icon" href="<?= BASE_URL ?>favicon.png" type="image/x-icon">


    <link rel="stylesheet" href="<?= BASE_URL ?>font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/vendor/perfect-scrollbar.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/vendor/component-custom-switch.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/main.css" />


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
                    <li><a href="#dashboard"><i class="iconsminds-hotel"></i><span>Desarrollo (s)</span></a></li>
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
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>home">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Departamentos</li>
                        </ol>
                    </nav>
                    <div class="separator mb-5"></div>
                </div>
            </div>


            <div class="row mb-4">
                <div class="col-12">
                    <?php
                    $depto_principal = $departamentos_list[0] ?? null;
                    if ($depto_principal): ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#subirPagoModal"
                        data-desarrollo-id="<?= htmlspecialchars($idDesarrollo); ?>"
                        data-departamento-no="<?= htmlspecialchars($depto_principal['departamento_no']); ?>"
                        onclick="abrirModalPago(this)">
                        <i class="iconsminds-check"></i> Subir Comprobante de Pago
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <h3>Documentación General (<?= htmlspecialchars($desarrolloNombre); ?>)</h3>
                    <p>
                        <?php if (!empty($plano_url_general)): ?>
                        <a href="<?= BASE_URL . $plano_url_general; ?>" target="_blank"
                            class="btn btn-sm btn-outline-info mr-2">
                            <i class="iconsminds-folder"></i> Plano Arquitectónico
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($manual_url_general)): ?>
                        <a href="<?= BASE_URL . $manual_url_general; ?>" target="_blank"
                            class="btn btn-sm btn-outline-info">
                            <i class="iconsminds-download-1"></i> Manual de Estufa
                        </a>
                        <?php endif; ?>
                        <?php if (empty($plano_url_general) && empty($manual_url_general)): ?>
                        <span class="text-muted">No hay documentos generales disponibles.</span>
                        <?php endif; ?>
                    </p>

                    <h3 class="mt-4">Historial de Pagos y recios pendientes del Dpto.
                        (<?= htmlspecialchars($departamentos_list[0]['departamento_no'] ?? 'N/A'); ?>)</h3>

                    <?php if (empty($historial_pagos)): ?>
                    <div class='alert alert-warning'>No hay historial de pagos programados para este departamento.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table data-table responsive-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>
                                        <h5>Vencimiento</h5>
                                    </th>
                                    <th>
                                        <h5>Monto Esperado</h5>
                                    </th>
                                    <th>
                                        <h5>Estado de la Cuota</h5>
                                    </th>
                                    <th>
                                        <h5>Comprobantes y Recibos</h5>
                                    </th>
                                    <th>
                                        <h5>Pagar</h5>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial_pagos as $pago): ?>
                                <tr>
                                    <td data-label="Vencimiento">
                                        <?= date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?></td>
                                    <td data-label="Monto Esperado">$<?= number_format($pago['monto_esperado'], 2); ?>
                                    </td>

                                    <td data-label="Estado de la Cuota">
                                        <?php if (empty($pago['id_pago_realizado'])): ?>
                                        <span class="badge badge-danger">PENDIENTE</span>
                                        <?php elseif ($pago['estatus_real'] === 'aprobado'): ?>
                                        <span class="badge badge-success">APROBADO</span>
                                        <?php elseif ($pago['estatus_real'] === 'pendiente'): ?>
                                        <span class="badge badge-warning">EN REVISIÓN</span>
                                        <?php elseif ($pago['estatus_real'] === 'rechazado'): ?>
                                        <span class="badge badge-danger">RECHAZADO</span>
                                        <?php endif; ?>
                                    </td>

                                    <td data-label="Comprobantes">
                                        <?php if (!empty($pago['recibo_admin_url'])): ?>
                                        <a href="<?= BASE_URL . $pago['recibo_admin_url']; ?>" target="_blank"
                                            class="btn btn-sm btn-success" title="Recibo Oficial de la Empresa">
                                            <i class="iconsminds-receipt-3"></i> Recibo Admin
                                        </a>
                                        <?php endif; ?>

                                        <?php if (!empty($pago['comprobante_cliente_url'])): ?>
                                        <a href="<?= BASE_URL . $pago['comprobante_cliente_url']; ?>" target="_blank"
                                            class="btn btn-sm btn-info" title="Comprobante Subido por el Cliente">
                                            <i class="iconsminds-upload-1"></i> Comprobante Cliente
                                        </a>
                                        <?php endif; ?>

                                        <?php if (empty($pago['recibo_admin_url']) && empty($pago['comprobante_cliente_url'])): ?>
                                        <span class="text-muted">Ningún documento subido.</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Acción de Pago">
                                        <?php
                                                // Lógica para que el botón solo aparezca si no está aprobado
                                                $puede_pagar = ($pago['estatus_real'] !== 'aprobado' && $pago['estatus_real'] !== 'pendiente');
                                                ?>

                                        <?php if ($puede_pagar): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                            data-target="#subirPagoModal"
                                            data-desarrollo-id="<?= htmlspecialchars($idDesarrollo); ?>"
                                            data-departamento-no="<?= htmlspecialchars($departamentos_list[0]['departamento_no']); ?>"
                                            data-cronograma-id="<?= htmlspecialchars($pago['id_cronograma_pago']); ?>"
                                            data-monto-esperado="<?= htmlspecialchars($pago['monto_esperado']); ?>"
                                            data-vencimiento="<?= date('Y-m-d', strtotime($pago['fecha_vencimiento'])); ?>"
                                            onclick="abrirModalPago(this)">
                                            <i class="iconsminds-credit-card"></i> Pagar
                                        </button>
                                        <?php else: ?>
                                        <span class="text-muted small">
                                            <?php if ($pago['estatus_real'] === 'pendiente') echo 'En revisión';
                                                        else echo 'Pago Cubierto'; ?>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
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
                                <!-- <p class="mb-1">
                                    <i class="iconsminds-tag mr-1 text-info"></i>
                                    Cuota ID: <strong id="modalCuotaId">N/A</strong>
                                </p> -->
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


    <script src="<?= BASE_URL ?>js/vendor/jquery-3.3.1.min.js"></script>
    <script src="<?= BASE_URL ?>js/vendor/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>js/vendor/perfect-scrollbar.min.js"></script>
    <script src="<?= BASE_URL ?>js/dore.script.js"></script>
    <script src="<?= BASE_URL ?>js/scripts.js"></script>

    <script>
    // Función para pasar datos del botón al modal y cargar los pagos pendientes
    function abrirModalPago(button) {
        const modal = document.getElementById('subirPagoModal');

        // Capturamos todos los datos del botón
        const idDesarrollo = button.getAttribute('data-desarrollo-id');
        const departamentoNo = button.getAttribute('data-departamento-no');
        // CLAVE: Capturamos el ID de la cuota programada (que viene del bucle de la tabla)
        const idCronogramaPago = button.getAttribute('data-cronograma-id');
        const montoEsperado = button.getAttribute('data-monto-esperado');

        // Inyectar IDs en campos ocultos (para el controlador)
        modal.querySelector('#modalIdDesarrollo').value = idDesarrollo;
        modal.querySelector('#modalDepartamentoNo').value = departamentoNo;
        modal.querySelector('#modalIdCronogramaPago').value = idCronogramaPago;

        // Inyectar información visible para el cliente
        modal.querySelector('#modalDepartamentoTitulo').textContent = departamentoNo;
        modal.querySelector('#modalCuotaId').textContent = idCronogramaPago;
        modal.querySelector('#modalMontoRequerido').textContent = montoEsperado;

        // YA NO SE NECESITA AJAX NI LA FUNCIÓN cargarPagosPendientes
    }

    async function cargarPagosPendientes(idUsuario, idDesarrollo, departamentoNo) {
        const select = document.getElementById('pago_pendiente');
        select.innerHTML = '<option value="">Cargando pagos...</option>';
        select.disabled = true;

        try {
            const response = await fetch(
                `<?= BASE_URL ?>api/pagos_pendientes?idUsuario=${idUsuario}&idDesarrollo=${idDesarrollo}&departamentoNo=${departamentoNo}`
            );

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            const pagos = await response.json();
            select.innerHTML = '';
            select.disabled = false;

            if (pagos.length === 0) {
                select.innerHTML = '<option value="">No hay pagos pendientes para este Dpto.</option>';
                select.disabled = true;
            } else {
                select.innerHTML = '<option value="">Seleccione un pago a cubrir</option>';
                pagos.forEach(pago => {
                    const option = document.createElement('option');
                    option.value = pago.id_cronograma_pago;
                    option.textContent =
                        `Vencimiento: ${pago.fecha_vencimiento} - Monto: $${pago.monto_esperado}`;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error al cargar pagos pendientes:', error);
            select.innerHTML = '<option value="">Error de conexión / API</option>';
        }
    }

    // Lógica para actualizar el nombre del archivo en el campo de Bootstrap
    $(document).ready(function() {
        // Escucha cuando el valor del input de archivo cambia
        $('#comprobante').on('change', function() {
            // Obtiene el nombre del archivo seleccionado
            var fileName = $(this).val().split('\\').pop();

            // Busca la etiqueta del campo de archivo y actualiza su texto
            $(this).next('.custom-file-label').html(fileName);

            // Si no se selecciona ningún archivo, vuelve a poner el texto por defecto.
            if (fileName === '') {
                $(this).next('.custom-file-label').html('Elige un archivo...');
            }
        });
    });
    </script>
</body>

</html>