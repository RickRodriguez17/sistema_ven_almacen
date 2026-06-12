@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">

  <div class="pagetitle">
    <h1><i class="bi bi-hourglass-split"></i> Pedidos pendientes</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Pendientes</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      @forelse($pendientes as $p)
        <div class="col-md-6 col-lg-4 mb-3" id="venta-{{ $p->id }}">
          <div class="card h-100 border-warning">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-0">#{{ $p->numero_ticket }}</h6>
                  <small class="text-muted">{{ $p->created_at->format('d/m H:i') }} · {{ $p->user?->nombre }}</small>
                </div>
                <span class="badge bg-warning text-dark">{{ Str::upper($p->tipoPedidoLabel()) }}</span>
              </div>

              @if($p->mesa)<div class="small mb-1"><i class="bi bi-cup-hot"></i> Mesa <strong>{{ $p->mesa }}</strong></div>@endif
              @if($p->direccion_delivery)<div class="small mb-1"><i class="bi bi-geo-alt"></i> {{ $p->direccion_delivery }}</div>@endif
              @if($p->cliente)<div class="small mb-1"><i class="bi bi-person"></i> {{ $p->cliente->nombre }} {{ $p->cliente->apellido }}</div>
              @elseif($p->nombre_cliente_libre)<div class="small mb-1"><i class="bi bi-person"></i> {{ $p->nombre_cliente_libre }}</div>@endif
              @if($p->notas)<div class="small text-muted mb-1"><i class="bi bi-chat-left-text"></i> {{ $p->notas }}</div>@endif

              <ul class="list-unstyled mb-2 small mt-2">
                @foreach($p->detalles as $d)
                  <li>{{ $d->cantidad }}× {{ $d->combo_id ? '🌟 ' : '' }}{{ $d->producto?->nombre ?? $d->combo?->nombre ?? '—' }} <span class="text-muted">({{ $negocio['moneda'] }} {{ number_format($d->subtotal, 2) }})</span></li>
                @endforeach
              </ul>

              <div class="d-flex justify-content-between align-items-center">
                <strong class="fs-5">{{ $negocio['moneda'] }} {{ number_format($p->total_venta, 2) }}</strong>
              </div>

              <div class="d-flex gap-2 mt-2">
                <button class="btn btn-pos btn-sm flex-grow-1 btn-cobrar-pend" data-id="{{ $p->id }}" data-total="{{ $p->total_venta }}">
                  <i class="bi bi-cash-coin"></i> Cobrar
                </button>
                <form method="POST" action="{{ route('ventas.anular', $p->id) }}" class="form-anular">
                  @csrf
                  <input type="hidden" name="motivo" value="">
                  <button type="button" class="btn btn-outline-danger btn-sm btn-anular-pend">
                    <i class="bi bi-x-circle"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="alert alert-info">No hay pedidos pendientes.</div>
        </div>
      @endforelse
    </div>

    <div>{{ $pendientes->links() }}</div>
  </section>

</main>

<!-- Modal cobrar -->
<div class="modal fade" id="cobrarModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-cash-stack"></i> Cobrar pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between mb-3">
          <span>Total</span><strong id="cm-total" class="fs-5">—</strong>
        </div>
        <div id="cm-pagos"></div>
        <button type="button" class="btn btn-outline-primary btn-sm w-100" id="cm-add"><i class="bi bi-plus-circle"></i> Otro método</button>
        <hr>
        <div class="d-flex justify-content-between"><span>Pagado</span><strong id="cm-pagado">—</strong></div>
        <div class="d-flex justify-content-between"><span>Faltante</span><strong id="cm-faltante" class="text-danger">—</strong></div>
        <div class="d-flex justify-content-between"><span>Cambio</span><strong id="cm-cambio" class="text-success">—</strong></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-pos" id="cm-confirmar" disabled><i class="bi bi-check-circle"></i> Confirmar</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const moneda = @json($negocio['moneda']);
  const fmt = n => moneda + ' ' + Number(n).toFixed(2);
  let ventaId = null;
  let total = 0;
  const modalEl = document.getElementById('cobrarModal');
  const modal = new bootstrap.Modal(modalEl);
  const pagosWrap = document.getElementById('cm-pagos');
  const totalEl = document.getElementById('cm-total');
  const pagadoEl = document.getElementById('cm-pagado');
  const faltanteEl = document.getElementById('cm-faltante');
  const cambioEl = document.getElementById('cm-cambio');
  const btnAdd = document.getElementById('cm-add');
  const btnConf = document.getElementById('cm-confirmar');

function row(idx) {
  return `<div class="pago-row border rounded p-2 mb-2">
    <div class="row g-2 align-items-end">
      <div class="col-5">
        <select class="form-select form-select-sm pago-metodo">
          <option value="efectivo">Efectivo</option>
          <option value="tarjeta">Tarjeta</option>
          <option value="yape">Yape</option>
        </select>
      </div>

      <div class="col-5">
        <input type="number" step="0.01" class="form-control form-control-sm pago-monto" placeholder="0.00">
      </div>

      <div class="col-2">
        <button class="btn btn-sm btn-outline-danger pago-del" ${idx === 0 ? 'disabled' : ''}>
          <i class="bi bi-trash"></i>
        </button>
      </div>

      <div class="col-12 efectivo-extra" style="display:none;">
        <input type="number" step="0.01" class="form-control form-control-sm pago-efectivo" placeholder="Efectivo recibido">
      </div>

      <div class="col-12 ref-extra" style="display:none;">
        <input type="text" class="form-control form-control-sm pago-ref" placeholder="Referencia">
      </div>
    </div>
  </div>`;
}

  function recalc() {
    let pagado = 0, efRec = 0, efMonto = 0, efFaltante = 0;
    pagosWrap.querySelectorAll('.pago-row').forEach(r => {
      const m = r.querySelector('.pago-metodo').value;
      const mt = parseFloat(r.querySelector('.pago-monto').value) || 0;
      const ef = parseFloat(r.querySelector('.pago-efectivo').value) || 0;
      const efI = r.querySelector('.pago-efectivo');
      pagado += mt;
      if (m === 'efectivo') {
        efMonto += mt; efRec += ef;
        if (ef + 0.001 < mt) { efFaltante += (mt - ef); efI.classList.add('is-invalid'); }
        else { efI.classList.remove('is-invalid'); }
      } else {
        efI.classList.remove('is-invalid');
      }
      r.querySelector('.efectivo-extra').style.display = m === 'efectivo' ? '' : 'none';
      r.querySelector('.ref-extra').style.display = m === 'efectivo' ? 'none' : '';
    });
    pagadoEl.textContent = fmt(pagado);
    faltanteEl.textContent = fmt(Math.max(0, total - pagado));
    cambioEl.textContent = fmt(Math.max(0, efRec - efMonto));
    btnConf.disabled = !(pagado + 0.01 >= total) || efFaltante >= 0.01;
  }

  function addRow(monto) {
    const idx = pagosWrap.querySelectorAll('.pago-row').length;
    pagosWrap.insertAdjacentHTML('beforeend', row(idx));
    if (monto != null) pagosWrap.querySelector('.pago-row:last-child .pago-monto').value = Number(monto).toFixed(2);
    recalc();
  }

  pagosWrap.addEventListener('input', recalc);
  pagosWrap.addEventListener('change', recalc);
  pagosWrap.addEventListener('click', e => {
    if (e.target.closest('.pago-del')) { e.target.closest('.pago-row').remove(); recalc(); }
  });
  btnAdd.addEventListener('click', () => {
    let suma = 0;
    pagosWrap.querySelectorAll('.pago-monto').forEach(i => suma += parseFloat(i.value) || 0);
    addRow(Math.max(0, total - suma));
  });

  document.querySelectorAll('.btn-cobrar-pend').forEach(b => b.addEventListener('click', () => {
    ventaId = b.dataset.id;
    total = parseFloat(b.dataset.total);
    totalEl.textContent = fmt(total);
    pagosWrap.innerHTML = '';
    addRow(total);
    modal.show();
  }));

  btnConf.addEventListener('click', async () => {
    const pagos = [];
    pagosWrap.querySelectorAll('.pago-row').forEach(r => {
      const monto = parseFloat(r.querySelector('.pago-monto').value) || 0;
      if (monto <= 0) return;
      pagos.push({
        metodo_pago: r.querySelector('.pago-metodo').value,
        monto,
        efectivo_recibido: parseFloat(r.querySelector('.pago-efectivo').value) || 0,
        referencia: r.querySelector('.pago-ref').value || null,
      });
    });
    btnConf.disabled = true;
    try {
      const url = '/ventas/' + ventaId + '/cobrar';
      const resp = await fetch(url, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': window.csrfToken,'Accept':'application/json'},
        body: JSON.stringify({ pagos })
      });
      const data = await resp.json();
      if (!data.ok) {
        if (data.redirect) {
          await Swal.fire({icon:'warning', title:'Turno no abierto', text: data.message, confirmButtonText:'Ir a iniciar turno'});
          location.href = data.redirect;
          return;
        }
        throw new Error(data.message);
      }
      modal.hide();
      // Auto-imprimir el ticket de cocina / despacho.
      if (data.ticket_url) {
        window.open(data.ticket_url + '?ancho=80', '_blank');
      }
      const r = await Swal.fire({
        icon: 'success', title: 'Cobrado',
        html: 'Ticket: <strong>' + data.numero_ticket + '</strong><br><small class="text-muted">Ticket de cocina enviado a impresión.</small>',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-printer"></i> Reimprimir',
        cancelButtonText: 'Cerrar'
      });
      if (r.isConfirmed) window.open(data.ticket_url + '?ancho=80', '_blank');
      setTimeout(() => location.reload(), 800);
    } catch (e) {
      Swal.fire('Error', e.message, 'error');
    } finally { btnConf.disabled = false; }
  });

  document.querySelectorAll('.btn-anular-pend').forEach(btn => btn.addEventListener('click', async () => {
    const form = btn.closest('form.form-anular');
    const r = await Swal.fire({
      title: '¿Anular pedido pendiente?',
      input: 'text', inputLabel: 'Motivo de anulación', inputPlaceholder: 'Ej: cliente desistió',
      showCancelButton: true, confirmButtonText: 'Anular', confirmButtonColor: '#d33',
      inputValidator: v => (!v || v.length < 3) ? 'Indica un motivo' : null
    });
    if (r.isConfirmed) {
      form.querySelector('input[name="motivo"]').value = r.value;
      form.submit();
    }
  }));
})();
</script>
@endpush
