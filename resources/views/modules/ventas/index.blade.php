@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">

  <div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1><i class="bi bi-cash-coin"></i> Punto de Venta</h1>
      <p class="text-muted small mb-0">Selecciona productos o combos y arma el pedido</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('ventas.pendientes') }}" class="btn btn-outline-warning btn-sm">
        <i class="bi bi-hourglass-split"></i> Pedidos pendientes
        @if($pendientes->count() > 0)
          <span class="badge bg-warning text-dark">{{ $pendientes->count() }}</span>
        @endif
      </a>
    </div>
  </div>

  @if(! $turnoAbierto)
    <div class="alert alert-warning d-flex align-items-center gap-3 shadow-sm" role="alert" style="border-left: 6px solid var(--mafu-red, #EA3323);">
      <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
      <div class="flex-grow-1">
        <strong>No tienes un turno de caja abierto.</strong>
        Para registrar ventas debes iniciar tu turno primero. Cada venta queda
        vinculada al turno y al cerrar caja se cuadra automáticamente.
      </div>
      <a href="{{ route('cierres.iniciar.form') }}" class="btn btn-warning text-dark fw-bold">
        <i class="bi bi-cash-stack"></i> Iniciar turno
      </a>
    </div>
  @endif

  <section class="section">
    <div class="pos-grid {{ ! $turnoAbierto ? 'pos-locked' : '' }}" @if(! $turnoAbierto) style="opacity:.55; pointer-events:none;" @endif>

      <div>
        <div class="card mb-3">
          <div class="card-body py-3">
            <div class="row g-2 align-items-center">
              <div class="col-md-5">
                <input type="text" id="buscar-producto" class="form-control" placeholder="Buscar producto/combo por nombre o código...">
              </div>
              <div class="col-md-7">
                <div class="d-flex flex-wrap gap-1">
                  <button type="button" class="btn btn-sm btn-outline-secondary filtro-cat active" data-cat="">Todas</button>
                  <button type="button" class="btn btn-sm btn-outline-warning filtro-cat" data-cat="combos">
                    <i class="bi bi-stars"></i> Combos
                  </button>
                  @foreach($categorias as $c)
                    <button type="button" class="btn btn-sm btn-outline-secondary filtro-cat" data-cat="{{ $c->id }}">{{ $c->nombre }}</button>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>

        @if($pendientes->count() > 0)
          <div class="card mb-3 border-warning">
            <div class="card-body py-2">
              <div class="d-flex justify-content-between align-items-center">
                <div><i class="bi bi-hourglass-split text-warning"></i> <strong>Tus pedidos pendientes</strong></div>
                <a href="{{ route('ventas.pendientes') }}" class="small">Ver todos</a>
              </div>
              <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach($pendientes->take(8) as $p)
                  <a href="{{ route('ventas.pendientes') }}#venta-{{ $p->id }}" class="badge bg-light text-dark border" style="font-size:0.85rem; padding:6px 10px;">
                    #{{ $p->numero_ticket }}
                    @if($p->mesa) · Mesa {{ $p->mesa }} @elseif($p->tipo_pedido === 'delivery') · Delivery @endif
                    · {{ $negocio['moneda'] }} {{ number_format($p->total_venta, 2) }}
                  </a>
                @endforeach
              </div>
            </div>
          </div>
        @endif

        <div class="row g-3" id="grid-productos">
          @foreach($combos as $combo)
            <div class="col-6 col-md-4 col-xl-3 producto-wrap"
                 data-cat="combos"
                 data-nombre="{{ strtolower($combo->nombre) }}"
                 data-codigo="{{ strtolower($combo->codigo ?? '') }}">
              <div class="producto-card combo-card"
                   data-tipo="combo"
                   data-id="{{ $combo->id }}"
                   data-nombre="{{ $combo->nombre }}"
                   data-precio="{{ $combo->precio }}"
                   data-img="{{ $combo->imagenUrl() ?? '' }}">
                <div class="img-wrap" @if($combo->imagenUrl()) style="background-image:url('{{ $combo->imagenUrl() }}');" @endif>
                  @if(!$combo->imagenUrl())<i class="bi bi-stars"></i>@endif
                  <span class="combo-tag">COMBO</span>
                </div>
                <div class="body">
                  <div class="nombre">{{ $combo->nombre }}</div>
                  <div class="text-muted small">
                    @foreach($combo->items as $ci)
                      {{ $ci->cantidad }}× {{ $ci->producto?->nombre }}@if(!$loop->last), @endif
                    @endforeach
                  </div>
                  <div class="precio">{{ $negocio['moneda'] }} {{ number_format($combo->precio, 2) }}</div>
                </div>
              </div>
            </div>
          @endforeach

          @forelse($productos as $p)
            <div class="col-6 col-md-4 col-xl-3 producto-wrap"
                 data-cat="{{ $p->categoria_id }}"
                 data-nombre="{{ strtolower($p->nombre) }}"
                 data-codigo="{{ strtolower($p->codigo ?? '') }}">
              <div class="producto-card {{ $p->cantidad <= 0 ? 'sin-stock' : '' }}"
                   data-tipo="producto"
                   data-id="{{ $p->id }}"
                   data-nombre="{{ $p->nombre }}"
                   data-precio="{{ $p->precio_venta }}"
                   data-stock="{{ $p->cantidad }}"
                   data-img="{{ $p->imagen ? asset('storage/' . $p->imagen->ruta) : '' }}">
                <div class="img-wrap" @if($p->imagen) style="background-image:url('{{ asset('storage/' . $p->imagen->ruta) }}');" @endif>
                  @if(!$p->imagen)<i class="bi bi-image"></i>@endif
                </div>
                <div class="body">
                  <div class="nombre">{{ $p->nombre }}</div>
                  <div class="text-muted small">{{ $p->categoria?->nombre }}</div>
                  <div class="precio">{{ $negocio['moneda'] }} {{ number_format($p->precio_venta, 2) }}</div>
                  <div class="stock mt-1">
                    @if($p->cantidad <= 0)
                      <span class="badge badge-sin-stock">Sin stock</span>
                    @elseif($p->cantidad <= $p->stock_minimo)
                      <span class="badge badge-stock-bajo">Stock: {{ $p->cantidad }}</span>
                    @else
                      <span class="badge bg-success">Stock: {{ $p->cantidad }}</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="col-12"><div class="alert alert-info">No hay productos registrados. <a href="{{ route('productos.create') }}">Crear producto</a></div></div>
          @endforelse
        </div>
      </div>

      <div>
        <div class="card sticky-top" style="top: 80px;">
          <div class="card-body">
            <h5 class="card-title mb-3"><i class="bi bi-cart3"></i> Carrito</h5>

            <div class="mb-3">
              <label class="form-label small mb-1">Tipo de pedido</label>
              <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="tipo_pedido" id="tp-llevar" value="llevar" checked>
                <label class="btn btn-outline-primary btn-sm" for="tp-llevar"><i class="bi bi-bag"></i> Llevar</label>

                <input type="radio" class="btn-check" name="tipo_pedido" id="tp-mesa" value="mesa">
                <label class="btn btn-outline-primary btn-sm" for="tp-mesa"><i class="bi bi-cup-hot"></i> Mesa</label>

                <input type="radio" class="btn-check" name="tipo_pedido" id="tp-delivery" value="delivery">
                <label class="btn btn-outline-primary btn-sm" for="tp-delivery"><i class="bi bi-bicycle"></i> Delivery</label>
              </div>
            </div>

            <div id="campo-mesa" class="mb-2" style="display:none;">
              <label class="form-label small mb-1">N° de mesa</label>
              <input type="text" id="mesa" class="form-control form-control-sm" placeholder="Ej: 5">
            </div>

            <div id="campo-delivery" class="mb-2" style="display:none;">
              <label class="form-label small mb-1">Dirección de entrega</label>
              <textarea id="direccion_delivery" rows="2" class="form-control form-control-sm" placeholder="Calle, número, referencia"></textarea>
            </div>

            <div class="mb-2">
              <label class="form-label small mb-1">Cliente registrado <span class="text-muted">(opcional)</span></label>
              <select id="cliente_id" class="form-select form-select-sm">
                <option value="">— Sin cliente / consumidor final —</option>
                @foreach($clientes as $cli)
                  <option value="{{ $cli->id }}">{{ $cli->nombre }} {{ $cli->apellido }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label small mb-1">Nombre del cliente <span class="text-muted">(opcional, sin registrarlo)</span></label>
              <input type="text" id="nombre_cliente_libre" class="form-control form-control-sm" placeholder="Ej: Juan, mesa 5, cliente WhatsApp" maxlength="120">
            </div>

            <div class="mb-2">
              <label class="form-label small mb-1">Notas del pedido</label>
              <input type="text" id="notas" class="form-control form-control-sm" placeholder="Ej: sin cebolla">
            </div>

            <div id="carrito-lista" style="min-height: 100px; max-height: 300px; overflow-y: auto;">
              <div class="carrito-vacio"><i class="bi bi-bag-x fs-1 d-block"></i>Carrito vacío</div>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-muted">Total</span>
              <span class="total-box" id="total-display">{{ $negocio['moneda'] }} 0.00</span>
            </div>

            <button id="btn-cobrar" class="btn btn-pos w-100 fw-bold" disabled>
              <i class="bi bi-check-circle"></i> COBRAR
            </button>
            <button id="btn-pendiente" class="btn btn-outline-warning w-100 mt-2" disabled>
              <i class="bi bi-hourglass-split"></i> Guardar como pendiente
            </button>
            <button id="btn-limpiar" class="btn btn-outline-secondary w-100 mt-2">
              <i class="bi bi-x-circle"></i> Limpiar carrito
            </button>
          </div>
        </div>
      </div>

    </div>
  </section>

</main>

<!-- Modal de pago -->
<div class="modal fade" id="pagoModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-cash-stack"></i> Cobrar venta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between mb-3">
          <span>Total a cobrar</span>
          <strong id="modal-total" class="fs-5">{{ $negocio['moneda'] }} 0.00</strong>
        </div>
        <div id="pagos-lista"></div>
        <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btn-add-pago">
          <i class="bi bi-plus-circle"></i> Agregar otro método de pago
        </button>
        <hr>
        <div class="d-flex justify-content-between">
          <span>Pagado</span>
          <strong id="modal-pagado">{{ $negocio['moneda'] }} 0.00</strong>
        </div>
        <div class="d-flex justify-content-between">
          <span>Faltante</span>
          <strong id="modal-faltante" class="text-danger">{{ $negocio['moneda'] }} 0.00</strong>
        </div>
        <div class="d-flex justify-content-between">
          <span>Cambio</span>
          <strong id="modal-cambio" class="text-success">{{ $negocio['moneda'] }} 0.00</strong>
        </div>
        <div id="modal-aviso" class="alert alert-warning small py-2 mt-2 mb-0" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-pos" id="btn-confirmar-pago" disabled>
          <i class="bi bi-check-circle"></i> Confirmar pago
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const moneda = @json($negocio['moneda']);
  const carrito = new Map();

  const grid = document.getElementById('grid-productos');
  const buscar = document.getElementById('buscar-producto');
  const filtros = document.querySelectorAll('.filtro-cat');
  const lista = document.getElementById('carrito-lista');
  const totalEl = document.getElementById('total-display');
  const btnCobrar = document.getElementById('btn-cobrar');
  const btnPendiente = document.getElementById('btn-pendiente');
  const btnLimpiar = document.getElementById('btn-limpiar');
  const clienteSel = document.getElementById('cliente_id');
  const nombreLibreInput = document.getElementById('nombre_cliente_libre');
  const notasInput = document.getElementById('notas');
  const tpRadios = document.querySelectorAll('input[name="tipo_pedido"]');
  const campoMesa = document.getElementById('campo-mesa');
  const campoDelivery = document.getElementById('campo-delivery');
  const inputMesa = document.getElementById('mesa');
  const inputDireccion = document.getElementById('direccion_delivery');

  const pagoModalEl = document.getElementById('pagoModal');
  const pagoModal = new bootstrap.Modal(pagoModalEl);
  const pagosLista = document.getElementById('pagos-lista');
  const btnAddPago = document.getElementById('btn-add-pago');
  const btnConfirmarPago = document.getElementById('btn-confirmar-pago');
  const modalTotal = document.getElementById('modal-total');
  const modalPagado = document.getElementById('modal-pagado');
  const modalFaltante = document.getElementById('modal-faltante');
  const modalCambio = document.getElementById('modal-cambio');
  const modalAviso = document.getElementById('modal-aviso');

  function fmt(n) { return moneda + ' ' + Number(n).toFixed(2); }

  // Filtros
  function aplicarFiltros() {
    const q = (buscar.value || '').toLowerCase().trim();
    const cat = document.querySelector('.filtro-cat.active').dataset.cat;
    document.querySelectorAll('.producto-wrap').forEach(el => {
      const matchCat = !cat || el.dataset.cat === cat;
      const matchQ = !q || el.dataset.nombre.includes(q) || el.dataset.codigo.includes(q);
      el.style.display = (matchCat && matchQ) ? '' : 'none';
    });
  }
  buscar.addEventListener('input', aplicarFiltros);
  filtros.forEach(b => b.addEventListener('click', () => {
    filtros.forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    aplicarFiltros();
  }));

  // Tipo pedido
  tpRadios.forEach(r => r.addEventListener('change', () => {
    campoMesa.style.display = (r.value === 'mesa' && r.checked) ? '' : campoMesa.style.display;
    const val = document.querySelector('input[name="tipo_pedido"]:checked').value;
    campoMesa.style.display = val === 'mesa' ? '' : 'none';
    campoDelivery.style.display = val === 'delivery' ? '' : 'none';
  }));

  // Agregar item
  grid.addEventListener('click', (e) => {
    const card = e.target.closest('.producto-card');
    if (!card || card.classList.contains('sin-stock')) return;
    const tipo = card.dataset.tipo;
    const key = tipo + '-' + card.dataset.id;
    const item = carrito.get(key) || {
      key,
      tipo,
      id: Number(card.dataset.id),
      nombre: card.dataset.nombre,
      precio: Number(card.dataset.precio),
      stock: tipo === 'producto' ? Number(card.dataset.stock) : 9999,
      img: card.dataset.img || '',
      cantidad: 0,
    };
    if (tipo === 'producto' && item.cantidad + 1 > item.stock) {
      Swal.fire('Sin stock', 'No hay stock suficiente para ' + item.nombre, 'warning');
      return;
    }
    item.cantidad += 1;
    carrito.set(key, item);
    render();
  });

  function render() {
    if (carrito.size === 0) {
      lista.innerHTML = '<div class="carrito-vacio"><i class="bi bi-bag-x fs-1 d-block"></i>Carrito vacío</div>';
      totalEl.textContent = fmt(0);
      btnCobrar.disabled = true;
      btnPendiente.disabled = true;
      return;
    }

    let html = '';
    let total = 0;
    carrito.forEach(it => {
      const sub = it.cantidad * it.precio;
      total += sub;
      const thumb = it.img
        ? `<img class="cart-thumb" src="${it.img}" alt="">`
        : `<div class="cart-thumb cart-thumb-placeholder"><i class="bi ${it.tipo === 'combo' ? 'bi-stars' : 'bi-image'}"></i></div>`;
      const tag = it.tipo === 'combo' ? '<span class="badge bg-warning text-dark ms-1">COMBO</span>' : '';
      html += `
        <div class="carrito-item d-flex align-items-start gap-2" data-key="${it.key}">
          ${thumb}
          <div class="info flex-grow-1">
            <div class="nombre text-truncate">${it.nombre} ${tag}</div>
            <div class="text-muted small">${fmt(it.precio)} c/u — ${fmt(sub)}</div>
            <div class="qty-control mt-1">
              <button type="button" data-act="dec">−</button>
              <input type="number" min="1" max="${it.stock}" value="${it.cantidad}" data-act="set">
              <button type="button" data-act="inc">+</button>
              <button type="button" data-act="del" class="text-danger ms-2"><i class="bi bi-trash"></i></button>
            </div>
          </div>
        </div>`;
    });
    lista.innerHTML = html;
    totalEl.textContent = fmt(total);
    btnCobrar.disabled = false;
    btnPendiente.disabled = false;
  }

  lista.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-act]');
    if (!btn) return;
    const wrap = btn.closest('.carrito-item');
    const key = wrap.dataset.key;
    const item = carrito.get(key);
    if (!item) return;
    const act = btn.dataset.act;
    if (act === 'dec') item.cantidad -= 1;
    if (act === 'inc') {
      if (item.cantidad + 1 > item.stock) {
        Swal.fire('Sin stock', 'No hay stock suficiente', 'warning');
        return;
      }
      item.cantidad += 1;
    }
    if (act === 'del') item.cantidad = 0;
    if (item.cantidad <= 0) carrito.delete(key);
    else carrito.set(key, item);
    render();
  });

  lista.addEventListener('change', (e) => {
    const input = e.target.closest('input[data-act="set"]');
    if (!input) return;
    const wrap = input.closest('.carrito-item');
    const key = wrap.dataset.key;
    const item = carrito.get(key);
    let val = parseInt(input.value, 10) || 0;
    if (val > item.stock) { val = item.stock; Swal.fire('Sin stock', 'Ajustado a stock máximo', 'warning'); }
    if (val <= 0) carrito.delete(key);
    else { item.cantidad = val; carrito.set(key, item); }
    render();
  });

  btnLimpiar.addEventListener('click', () => {
    if (carrito.size === 0) return;
    Swal.fire({title:'¿Limpiar carrito?', icon:'question', showCancelButton:true, confirmButtonText:'Sí'})
      .then(r => { if (r.isConfirmed) { carrito.clear(); render(); } });
  });

  function payloadBase() {
    const tipo = document.querySelector('input[name="tipo_pedido"]:checked').value;
    const items = Array.from(carrito.values()).map(it => ({
      producto_id: it.tipo === 'producto' ? it.id : null,
      combo_id: it.tipo === 'combo' ? it.id : null,
      cantidad: it.cantidad,
    }));
    return {
      cliente_id: clienteSel.value || null,
      nombre_cliente_libre: nombreLibreInput.value.trim() || null,
      tipo_pedido: tipo,
      mesa: tipo === 'mesa' ? (inputMesa.value || null) : null,
      direccion_delivery: tipo === 'delivery' ? (inputDireccion.value || null) : null,
      notas: notasInput.value || null,
      items,
    };
  }

  btnPendiente.addEventListener('click', async () => {
    const payload = { ...payloadBase(), pendiente: true };
    try {
      const resp = await fetch(@json(route('ventas.store')), {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': window.csrfToken,'Accept':'application/json'},
        body: JSON.stringify(payload),
      });
      const data = await resp.json();
      if (!data.ok) {
        if (data.redirect) {
          await Swal.fire({icon:'warning', title:'Turno no abierto', text: data.message, confirmButtonText:'Ir a iniciar turno'});
          location.href = data.redirect;
          return;
        }
        throw new Error(data.message || 'Error al guardar');
      }
      await Swal.fire({icon: 'success', title: 'Pedido pendiente guardado', text: 'Ticket: ' + data.numero_ticket, timer: 2000, showConfirmButton: false});
      location.reload();
    } catch (e) {
      Swal.fire('Error', e.message, 'error');
    }
  });

  // Modal de pago
  function getTotal() {
    return parseFloat(totalEl.textContent.replace(/[^0-9.]/g,'')) || 0;
  }

  function pagoRowHtml(idx) {
    return `
      <div class="pago-row border rounded p-2 mb-2" data-idx="${idx}">
        <div class="row g-2 align-items-end">
          <div class="col-5">
            <label class="form-label small mb-0">Método</label>
            <select class="form-select form-select-sm pago-metodo">
              <option value="efectivo">Efectivo</option>
              <option value="tarjeta">Tarjeta</option>
              <option value="yape">Yape</option>
            </select>
          </div>
          <div class="col-4">
            <label class="form-label small mb-0">Monto</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm pago-monto" placeholder="0.00">
          </div>
          <div class="col-2">
            <button type="button" class="btn btn-sm btn-outline-danger pago-del" ${idx === 0 ? 'disabled' : ''}><i class="bi bi-trash"></i></button>
          </div>
          <div class="col-12 efectivo-extra" style="display:none;">
            <label class="form-label small mb-0 mt-1">Efectivo recibido</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm pago-efectivo">
          </div>
          <div class="col-12 ref-extra" style="display:none;">
            <label class="form-label small mb-0 mt-1">Referencia / N° comprobante</label>
            <input type="text" class="form-control form-control-sm pago-ref" maxlength="100">
          </div>
        </div>
      </div>`;
  }

  function recalcPago() {
    const total = getTotal();
    let pagado = 0;
    let efectivoTotal = 0;
    let efectivoMonto = 0;
    let efectivoFaltante = 0;
    pagosLista.querySelectorAll('.pago-row').forEach(row => {
      const metodo = row.querySelector('.pago-metodo').value;
      const monto = parseFloat(row.querySelector('.pago-monto').value) || 0;
      const ef = parseFloat(row.querySelector('.pago-efectivo').value) || 0;
      const efInput = row.querySelector('.pago-efectivo');
      pagado += monto;
      if (metodo === 'efectivo') {
        efectivoMonto += monto;
        efectivoTotal += ef;
        if (ef + 0.001 < monto) {
          efectivoFaltante += (monto - ef);
          efInput.classList.add('is-invalid');
        } else {
          efInput.classList.remove('is-invalid');
        }
      } else {
        efInput.classList.remove('is-invalid');
      }
      row.querySelector('.efectivo-extra').style.display = metodo === 'efectivo' ? '' : 'none';
      row.querySelector('.ref-extra').style.display = (metodo !== 'efectivo') ? '' : 'none';
    });
    const cambio = Math.max(0, efectivoTotal - efectivoMonto);
    const faltante = Math.max(0, total - pagado);

    modalPagado.textContent = fmt(pagado);
    modalFaltante.textContent = fmt(faltante);
    modalCambio.textContent = fmt(cambio);

    const cubreTotal = (pagado + 0.01 >= total);
    const efectivoOk = (efectivoFaltante < 0.01);
    btnConfirmarPago.disabled = !(cubreTotal && efectivoOk);

    // Mensaje de ayuda
    if (modalAviso) {
      if (!cubreTotal) {
        modalAviso.textContent = `Faltan ${fmt(faltante)} para cubrir el total.`;
        modalAviso.style.display = '';
      } else if (!efectivoOk) {
        modalAviso.textContent = `El efectivo recibido no alcanza para el monto en efectivo. Falta ${fmt(efectivoFaltante)}.`;
        modalAviso.style.display = '';
      } else {
        modalAviso.style.display = 'none';
      }
    }
  }

  function addPagoRow(initialMonto) {
    const idx = pagosLista.querySelectorAll('.pago-row').length;
    pagosLista.insertAdjacentHTML('beforeend', pagoRowHtml(idx));
    if (initialMonto != null) {
      const last = pagosLista.querySelector('.pago-row:last-child .pago-monto');
      last.value = Number(initialMonto).toFixed(2);
    }
    recalcPago();
  }

  pagosLista.addEventListener('input', recalcPago);
  pagosLista.addEventListener('change', recalcPago);
  pagosLista.addEventListener('click', (e) => {
    if (e.target.closest('.pago-del')) {
      e.target.closest('.pago-row').remove();
      recalcPago();
    }
  });
  btnAddPago.addEventListener('click', () => addPagoRow(Math.max(0, getTotal() - sumaPagos())));

  function sumaPagos() {
    let s = 0;
    pagosLista.querySelectorAll('.pago-monto').forEach(i => s += parseFloat(i.value) || 0);
    return s;
  }

  btnCobrar.addEventListener('click', () => {
    if (carrito.size === 0) return;
    pagosLista.innerHTML = '';
    addPagoRow(getTotal());
    modalTotal.textContent = fmt(getTotal());
    pagoModal.show();
  });

  btnConfirmarPago.addEventListener('click', async () => {
    const pagos = [];
    pagosLista.querySelectorAll('.pago-row').forEach(row => {
      const monto = parseFloat(row.querySelector('.pago-monto').value) || 0;
      if (monto <= 0) return;
      pagos.push({
        metodo_pago: row.querySelector('.pago-metodo').value,
        monto,
        efectivo_recibido: parseFloat(row.querySelector('.pago-efectivo').value) || 0,
        referencia: row.querySelector('.pago-ref').value || null,
      });
    });

    if (pagos.length === 0) return Swal.fire('Falta pago', 'Agrega al menos un método de pago', 'warning');

    const payload = { ...payloadBase(), pagos };
    btnConfirmarPago.disabled = true;
    btnConfirmarPago.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
    try {
      const resp = await fetch(@json(route('ventas.store')), {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': window.csrfToken,'Accept':'application/json'},
        body: JSON.stringify(payload),
      });
      const data = await resp.json();
      if (!data.ok) {
        if (data.redirect) {
          await Swal.fire({icon:'warning', title:'Turno no abierto', text: data.message, confirmButtonText:'Ir a iniciar turno'});
          location.href = data.redirect;
          return;
        }
        throw new Error(data.message || 'Error al procesar venta');
      }

      pagoModal.hide();

      // Imprimir automáticamente el ticket de cocina / despacho.
      if (data.ticket_url) {
        window.open(data.ticket_url + '?ancho=80', '_blank');
      }

      const result = await Swal.fire({
        icon: 'success',
        title: '¡Venta registrada!',
        html: `Ticket: <strong>${data.numero_ticket}</strong><br>Total: <strong>${fmt(data.total)}</strong>` + (data.cambio > 0 ? `<br>Cambio: <strong>${fmt(data.cambio)}</strong>` : '') + '<br><small class="text-muted">Ticket de cocina enviado a impresión.</small>',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-printer"></i> Reimprimir 80mm',
        cancelButtonText: 'Continuar',
        showDenyButton: true,
        denyButtonText: '<i class="bi bi-file-earmark-pdf"></i> PDF',
      });

      if (result.isConfirmed) window.open(data.ticket_url + '?ancho=80', '_blank');
      else if (result.isDenied) window.open(data.pdf_url + '?ancho=80', '_blank');

      carrito.clear();
      render();
      setTimeout(() => location.reload(), 1500);
    } catch (e) {
      Swal.fire('Error', e.message, 'error');
    } finally {
      btnConfirmarPago.disabled = false;
      btnConfirmarPago.innerHTML = '<i class="bi bi-check-circle"></i> Confirmar pago';
    }
  });
})();
</script>
@endpush
