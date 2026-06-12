@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">

  <div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1><i class="bi bi-pencil-square"></i> Venta Libre</h1>
      <p class="text-muted small mb-0">Registra ventas de productos no inventariados (bolsas, propinas, huesitos, etc.)</p>
    </div>
    <a href="{{ route('ventas-nueva') }}" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-cash-coin"></i> Ir al POS
    </a>
  </div>

  @if(! $turnoAbierto)
    <div class="alert alert-warning d-flex align-items-center gap-3 shadow-sm" role="alert" style="border-left: 6px solid var(--mafu-red, #EA3323);">
      <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
      <div class="flex-grow-1">
        <strong>No tienes un turno de caja abierto.</strong>
        Para registrar ventas debes iniciar tu turno primero.
      </div>
      <a href="{{ route('cierres.iniciar.form') }}" class="btn btn-warning text-dark fw-bold">
        <i class="bi bi-cash-stack"></i> Iniciar turno
      </a>
    </div>
  @endif

  <section class="section">
    <div class="{{ ! $turnoAbierto ? '' : '' }}" @if(! $turnoAbierto) style="opacity:.55; pointer-events:none;" @endif>
      <div class="row">

        {{-- Columna izquierda: agregar items --}}
        <div class="col-lg-7">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Agregar productos libres</h5>

              <div class="row g-2 align-items-end mb-3" id="formAgregarItem">
                <div class="col-md-5">
                  <label class="form-label small mb-0">Nombre del producto</label>
                  <input type="text" id="itemNombre" class="form-control" placeholder="Ej: Huesitos, Bolsa, Propina..." maxlength="200">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-0">Cantidad</label>
                  <input type="number" id="itemCantidad" class="form-control" value="1" min="1" step="1">
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-0">Precio unit.</label>
                  <input type="number" id="itemPrecio" class="form-control" placeholder="0.00" min="0.01" step="0.01">
                </div>
                <div class="col-md-2">
                  <button type="button" id="btnAgregar" class="btn btn-primary w-100">
                    <i class="bi bi-plus-lg"></i> Agregar
                  </button>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-sm table-hover" id="tablaItems">
                  <thead>
                    <tr>
                      <th>Producto</th>
                      <th class="text-center" style="width:80px;">Cant.</th>
                      <th class="text-end" style="width:120px;">P. Unit.</th>
                      <th class="text-end" style="width:120px;">Subtotal</th>
                      <th style="width:50px;"></th>
                    </tr>
                  </thead>
                  <tbody id="listaItems">
                    <tr id="filaVacia"><td colspan="5" class="text-center text-muted py-4">Agrega productos usando el formulario de arriba.</td></tr>
                  </tbody>
                  <tfoot>
                    <tr class="table-light">
                      <td colspan="3" class="text-end fw-bold">TOTAL</td>
                      <td class="text-end fw-bold" id="totalVenta">{{ $negocio['moneda'] }} 0.00</td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>

        {{-- Columna derecha: datos de la venta y confirmar --}}
        <div class="col-lg-5">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Datos de la venta</h5>

              <div class="mb-3">
                <label class="form-label small">Cliente (opcional)</label>
                <select id="clienteSel" class="form-select form-select-sm">
                  <option value="">— Sin cliente —</option>
                  @foreach($clientes as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label small">Notas (opcional)</label>
                <textarea id="notasInput" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Observaciones de la venta..."></textarea>
              </div>

              <hr>
              <h6>Método de pago</h6>

              <div id="pagosContainer">
                {{-- Se llena dinámicamente --}}
              </div>

              <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddPago">
                  <i class="bi bi-plus"></i> Agregar pago
                </button>
              </div>

              <div class="alert alert-light mb-3 py-2" id="resumenPago" style="display:none;">
                <div class="d-flex justify-content-between"><span>Total:</span><strong id="rpTotal">0.00</strong></div>
                <div class="d-flex justify-content-between"><span>Pagado:</span><strong id="rpPagado">0.00</strong></div>
                <div class="d-flex justify-content-between"><span>Cambio:</span><strong id="rpCambio" class="text-success">0.00</strong></div>
              </div>

              <button type="button" id="btnConfirmar" class="btn btn-success w-100 btn-lg" disabled>
                <i class="bi bi-check-circle"></i> Registrar Venta Libre
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const moneda = @json($negocio['moneda']);
  const items = [];
  let pagoIdx = 0;

  const itemNombre = document.getElementById('itemNombre');
  const itemCantidad = document.getElementById('itemCantidad');
  const itemPrecio = document.getElementById('itemPrecio');
  const btnAgregar = document.getElementById('btnAgregar');
  const listaItems = document.getElementById('listaItems');
  const filaVacia = document.getElementById('filaVacia');
  const totalVenta = document.getElementById('totalVenta');
  const btnConfirmar = document.getElementById('btnConfirmar');
  const pagosContainer = document.getElementById('pagosContainer');
  const btnAddPago = document.getElementById('btnAddPago');
  const resumenPago = document.getElementById('resumenPago');
  const rpTotal = document.getElementById('rpTotal');
  const rpPagado = document.getElementById('rpPagado');
  const rpCambio = document.getElementById('rpCambio');

  function getTotal() {
    return items.reduce((s, it) => s + it.subtotal, 0);
  }

  function renderItems() {
    if (items.length === 0) {
      filaVacia.style.display = '';
    } else {
      filaVacia.style.display = 'none';
    }
    listaItems.querySelectorAll('.item-row').forEach(el => el.remove());

    items.forEach((it, i) => {
      const tr = document.createElement('tr');
      tr.className = 'item-row';
      tr.innerHTML = `
        <td>${it.nombre}</td>
        <td class="text-center">${it.cantidad}</td>
        <td class="text-end">${moneda} ${it.precio_unitario.toFixed(2)}</td>
        <td class="text-end">${moneda} ${it.subtotal.toFixed(2)}</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-del" data-idx="${i}"><i class="bi bi-trash"></i></button></td>
      `;
      listaItems.appendChild(tr);
    });

    totalVenta.textContent = moneda + ' ' + getTotal().toFixed(2);
    recalcPago();
  }

  listaItems.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-del');
    if (!btn) return;
    items.splice(parseInt(btn.dataset.idx), 1);
    renderItems();
  });

  btnAgregar.addEventListener('click', () => {
    const nombre = itemNombre.value.trim();
    const cantidad = parseInt(itemCantidad.value) || 0;
    const precio = parseFloat(itemPrecio.value) || 0;

    if (!nombre) { Swal.fire('', 'Ingresa el nombre del producto', 'warning'); itemNombre.focus(); return; }
    if (cantidad < 1) { Swal.fire('', 'La cantidad debe ser al menos 1', 'warning'); itemCantidad.focus(); return; }
    if (precio <= 0) { Swal.fire('', 'El precio debe ser mayor a 0', 'warning'); itemPrecio.focus(); return; }

    items.push({
      nombre,
      cantidad,
      precio_unitario: precio,
      subtotal: Math.round(cantidad * precio * 100) / 100,
    });

    itemNombre.value = '';
    itemCantidad.value = '1';
    itemPrecio.value = '';
    itemNombre.focus();
    renderItems();
  });

  // Enter to add
  [itemNombre, itemCantidad, itemPrecio].forEach(el => {
    el.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); btnAgregar.click(); } });
  });

  // Pagos
  function pagoRowHtml(idx) {
    return `
      <div class="pago-row border rounded p-2 mb-2" data-idx="${idx}">
        <div class="row g-2 align-items-end">
          <div class="col-5">
            <select class="form-select form-select-sm pago-metodo">
              <option value="efectivo">Efectivo</option>
              <option value="tarjeta">Tarjeta</option>
              <option value="yape">Yape</option>
            </select>
          </div>
          <div class="col-4">
            <input type="number" step="0.01" min="0" class="form-control form-control-sm pago-monto" placeholder="0.00">
          </div>
          <div class="col-2">
            <button type="button" class="btn btn-sm btn-outline-danger pago-del" ${idx === 0 ? 'disabled' : ''}><i class="bi bi-trash"></i></button>
          </div>
          <div class="col-12 efectivo-extra" style="display:none;">
            <input type="number" step="0.01" min="0" class="form-control form-control-sm pago-efectivo mt-1" placeholder="Efectivo recibido">
          </div>
        </div>
      </div>`;
  }

  function addPagoRow() {
    pagosContainer.insertAdjacentHTML('beforeend', pagoRowHtml(pagoIdx++));
    bindPagoEvents();
  }

  function bindPagoEvents() {
    pagosContainer.querySelectorAll('.pago-row').forEach(row => {
      const metodo = row.querySelector('.pago-metodo');
      const efExtra = row.querySelector('.efectivo-extra');
      metodo.onchange = () => { efExtra.style.display = metodo.value === 'efectivo' ? '' : 'none'; recalcPago(); };
      row.querySelector('.pago-monto').oninput = recalcPago;
      const efInput = row.querySelector('.pago-efectivo');
      if (efInput) efInput.oninput = recalcPago;
      row.querySelector('.pago-del').onclick = () => { row.remove(); recalcPago(); };
      metodo.dispatchEvent(new Event('change'));
    });
  }

  btnAddPago.addEventListener('click', addPagoRow);

  function recalcPago() {
    const total = getTotal();
    let pagado = 0;
    let efRec = 0;
    let efMonto = 0;

    pagosContainer.querySelectorAll('.pago-row').forEach(row => {
      const metodo = row.querySelector('.pago-metodo').value;
      const monto = parseFloat(row.querySelector('.pago-monto').value) || 0;
      pagado += monto;
      if (metodo === 'efectivo') {
        efMonto += monto;
        efRec += parseFloat(row.querySelector('.pago-efectivo')?.value) || 0;
      }
    });

    const cambio = efRec > efMonto ? Math.round((efRec - efMonto) * 100) / 100 : 0;

    if (total > 0) {
      resumenPago.style.display = '';
      rpTotal.textContent = moneda + ' ' + total.toFixed(2);
      rpPagado.textContent = moneda + ' ' + pagado.toFixed(2);
      rpCambio.textContent = moneda + ' ' + cambio.toFixed(2);
    } else {
      resumenPago.style.display = 'none';
    }

    btnConfirmar.disabled = items.length === 0 || pagado < total - 0.01;
  }

  addPagoRow();

  // Confirmar
  btnConfirmar.addEventListener('click', async () => {
    if (items.length === 0) return;

    const pagos = [];
    pagosContainer.querySelectorAll('.pago-row').forEach(row => {
      const monto = parseFloat(row.querySelector('.pago-monto').value) || 0;
      if (monto <= 0) return;
      pagos.push({
        metodo_pago: row.querySelector('.pago-metodo').value,
        monto,
        efectivo_recibido: parseFloat(row.querySelector('.pago-efectivo')?.value) || 0,
        referencia: null,
      });
    });

    const payload = {
      cliente_id: document.getElementById('clienteSel').value || null,
      notas: document.getElementById('notasInput').value || null,
      pagos,
      items: items.map(it => ({
        nombre: it.nombre,
        cantidad: it.cantidad,
        precio_unitario: it.precio_unitario,
      })),
    };

    btnConfirmar.disabled = true;
    try {
      const resp = await fetch(@json(route('ventas.libre.store')), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await resp.json();
      if (!data.ok) {
        if (data.redirect) {
          await Swal.fire({ icon: 'warning', title: 'Turno no abierto', text: data.message, confirmButtonText: 'Ir a iniciar turno' });
          location.href = data.redirect;
          return;
        }
        throw new Error(data.message || 'Error al guardar');
      }
      const result = await Swal.fire({
        icon: 'success',
        title: 'Venta libre registrada',
        html: `Ticket: <strong>${data.numero_ticket}</strong><br>Total: <strong>${moneda} ${data.total.toFixed(2)}</strong>${data.cambio > 0 ? '<br>Cambio: <strong>' + moneda + ' ' + data.cambio.toFixed(2) + '</strong>' : ''}`,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-printer"></i> Imprimir ticket',
        cancelButtonText: 'Nueva venta',
      });
      if (result.isConfirmed && data.ticket_url) {
        window.open(data.ticket_url, '_blank', 'width=400,height=600');
      }
      items.length = 0;
      renderItems();
      document.getElementById('notasInput').value = '';
    } catch (e) {
      Swal.fire('Error', e.message, 'error');
    } finally {
      btnConfirmar.disabled = false;
    }
  });

});
</script>
@endpush
