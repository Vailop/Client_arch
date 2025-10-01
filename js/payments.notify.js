// payments.notify.js

(function () {
  function tipoPorEstatus(estatus, vencido) {
    if (estatus === 2) return 'success';     // pagado
    if (estatus === 0) return 'secondary';   // cancelado
    if (estatus === 1 && vencido) return 'danger'; // pendiente vencido
    return 'info';                            // pendiente
  }

  function notificarPago(ev, from, align) {
    from  = from  || 'top';
    align = align || 'center';

    // Dependencias mínimas
    if (typeof jQuery === 'undefined') { console.warn('jQuery no cargó'); return; }
    if (typeof $.notify !== 'function') { console.warn('bootstrap-notify no cargó'); return; }

    var fecha   = ev.start ? ev.start.format('DD/MM/YYYY') : '-';
    var vencido = ev.start ? ev.start.format('YYYY-MM-DD') < moment().format('YYYY-MM-DD') : false;
    var tipo    = tipoPorEstatus(ev.estatus, vencido);

    // antes de construir el objeto, define el badge del estatus
    var vencido = ev.start ? ev.start.format('YYYY-MM-DD') < moment().format('YYYY-MM-DD') : false;
    var st = (ev.estatus === 2) ? { txt:'Pagado',    cls:'badge-success'   } :
             (ev.estatus === 0) ? { txt:'Cancelado', cls:'badge-secondary' } :
             (vencido)          ? { txt:'Vencido',   cls:'badge-danger'    } :
                                  { txt:'Pendiente', cls:'badge-info'      };
    $.notify(
      {
        title: '<div class="mb-1" style="font-weight:700;">Pago</div>',
        message:
          '<div class="d-flex align-items-center flex-wrap" ' + 'style="gap:.6rem; white-space:nowrap;">' + 
            (ev.dev ? '<span style="font-weight:600;">' + ev.dev + '</span>' : '') +
                      '<span>· Dpto ' + (ev.dpto || '-') + '</span>' +
                      '<span>· $' + (ev.monto || '0.00') + '</span>' +
                      '<span class="badge ' + st.cls + '">' + st.txt + '</span>' +
            '</div>'
      },
      {
        type: tipo,
        placement: { from: from, align: align },
        delay: 4000,
        z_index: 2000,
        offset: { y: 70, x: 20 },
        allow_dismiss: true
      }
    );
  }

  // Exportar al global
  window.notificarPago = notificarPago;
  window.tipoPorEstatus = tipoPorEstatus;

  // Log para verificar carga
  console.log('payments.notify.js cargado; notificarPago disponible.');
})();
