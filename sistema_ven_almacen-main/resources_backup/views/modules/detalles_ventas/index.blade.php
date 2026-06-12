@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Historial de Ventas</h1></div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
          <div class="col-md-2"><input type="date" name="desde" class="form-control" value="{{ request('desde') }}"></div>
          <div class="col-md-2"><input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}"></div>
          <div class="col-md-2">
            <select name="estado" class="form-select">
              <option value="">Todos los estados</option>
              <option value="pagada" @selected(request('estado')==='pagada')>Pagadas</option>
              <option value="pendiente" @selected(request('estado')==='pendiente')>Pendientes</option>
              <option value="anulada" @selected(request('estado')==='anulada')>Anuladas</option>
            </select>
          </div>
          <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
          <div class="col-md-4 text-end">
            <span class="badge bg-primary fs-6">Total: {{ $negocio['moneda'] }} {{ number_format($totales['monto'], 2) }}</span>
            <span class="badge bg-secondary fs-6">{{ $totales['cantidad'] }} ventas</span>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Ticket</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Cajero</th>
                <th>Pago</th>
                <th>Estado</th>
                <th class="text-end">Total</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($ventas as $v)
                <tr class="{{ $v->estado === 'anulada' ? 'table-secondary text-muted' : '' }}">
                  <td><code>{{ $v->numero_ticket }}</code></td>
                  <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                  <td>
                    <span class="badge bg-light text-dark border">{{ $v->tipoPedidoLabel() }}</span>
                    @if($v->mesa) <small class="text-muted">{{ $v->mesa }}</small>@endif
                  </td>
                  <td>
                    @if($v->cliente){{ $v->cliente->nombre.' '.$v->cliente->apellido }}
                    @elseif($v->nombre_cliente_libre){{ $v->nombre_cliente_libre }}
                    @else<span class="text-muted">Consumidor final</span>
                    @endif
                  </td>
                  <td>{{ $v->user?->name }}</td>
                  <td><span class="badge bg-secondary">{{ ucfirst($v->metodo_pago) }}</span></td>
                  <td>
                    @if($v->estado === 'pagada')
                      <span class="badge bg-success">Pagada</span>
                    @elseif($v->estado === 'pendiente')
                      <span class="badge bg-warning text-dark">Pendiente</span>
                    @else
                      <span class="badge bg-danger" title="{{ $v->motivo_anulacion }}">Anulada</span>
                    @endif
                  </td>
                  <td class="text-end fw-bold">{{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}</td>
                  <td class="text-center">
                    <a href="{{ route('detalle-venta.show', $v->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    @if($v->estado === 'pagada')
                      <a href="{{ route('ventas.ticket', $v->id) }}?ancho=80" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></a>
                      <a href="{{ route('ventas.ticket.pdf', $v->id) }}?ancho=80" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i></a>
                      @if(auth()->user()->esAdmin() || auth()->user()->esCajero())
                        <button class="btn btn-sm btn-outline-warning btn-anular" data-id="{{ $v->id }}" title="Anular venta"><i class="bi bi-x-circle"></i></button>
                        <form method="POST" action="{{ route('ventas.anular', $v->id) }}" id="form-anular-{{ $v->id }}" class="d-none">
                          @csrf <input type="hidden" name="motivo">
                        </form>
                      @endif
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">No hay ventas registradas.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{ $ventas->links() }}
      </div>
    </div>
  </section>
</main>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-anular').forEach(btn => btn.addEventListener('click', async () => {
  const id = btn.dataset.id;
  const r = await Swal.fire({
    title: '¿Anular venta?',
    text: 'Esto restituirá el stock vendido. La acción es irreversible.',
    input: 'text',
    inputLabel: 'Motivo de anulación',
    inputPlaceholder: 'Ej: error en el pedido',
    showCancelButton: true,
    confirmButtonText: 'Anular',
    confirmButtonColor: '#d33',
    inputValidator: v => (!v || v.length < 3) ? 'Indica un motivo' : null
  });
  if (r.isConfirmed) {
    const form = document.getElementById('form-anular-' + id);
    form.querySelector('input[name="motivo"]').value = r.value;
    form.submit();
  }
}));
</script>
@endpush
