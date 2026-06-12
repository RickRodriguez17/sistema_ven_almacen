@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Ventas diarias</h1>
    @if(auth()->user()->puedeImprimirReportes())
      <a target="_blank" class="btn btn-outline-primary" href="{{ route('reportes.pdf', ['tipo' => 'ventas-diarias', 'fecha' => $fecha, 'user_id' => $userIdSeleccionado]) }}">
        <i class="bi bi-printer"></i> Imprimir PDF
      </a>
    @endif
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
          <div class="col-md-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha" value="{{ $fecha }}" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Usuario</label>
            <select name="user_id" class="form-select">
              <option value="">— Todos —</option>
              @foreach($usuarios as $u)
                <option value="{{ $u->id }}" @selected($userIdSeleccionado == $u->id)>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
          </div>
        </form>

        <div class="row g-3 mb-3">
          <div class="col-md-3"><div class="card bg-light"><div class="card-body p-3"><small class="text-muted">Cantidad</small><h4 class="mb-0">{{ $totales['cantidad'] }}</h4></div></div></div>
          <div class="col-md-3"><div class="card bg-light"><div class="card-body p-3"><small class="text-muted">Total</small><h4 class="mb-0">{{ $negocio['moneda'] }} {{ number_format($totales['total'], 2) }}</h4></div></div></div>
          <div class="col-md-3"><div class="card bg-light"><div class="card-body p-3"><small class="text-muted">Efectivo</small><h4 class="mb-0">{{ $negocio['moneda'] }} {{ number_format($totales['efectivo'], 2) }}</h4></div></div></div>
          <div class="col-md-3"><div class="card bg-light"><div class="card-body p-3"><small class="text-muted">Otros métodos</small><h4 class="mb-0">{{ $negocio['moneda'] }} {{ number_format($totales['total'] - $totales['efectivo'], 2) }}</h4></div></div></div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead><tr><th>Ticket</th><th>Hora</th><th>Cajero</th><th>Estado</th><th>Items</th><th>Método</th><th class="text-end">Total</th></tr></thead>
            <tbody>
              @forelse($ventas as $v)
                @php $anul = $v->estado === 'anulada'; @endphp
                <tr class="{{ $anul ? 'table-danger text-muted' : '' }}">
                  <td><a href="{{ route('detalle-venta.show', $v->id) }}">#{{ $v->numero_ticket }}</a></td>
                  <td>{{ $v->created_at->format('H:i') }}</td>
                  <td>{{ $v->user->name ?? '—' }}</td>
                  <td>
                    @if($anul)
                      <span class="badge bg-danger">Anulada</span>
                    @elseif($v->estado === 'pendiente')
                      <span class="badge bg-warning text-dark">Pendiente</span>
                    @else
                      <span class="badge bg-success">Pagada</span>
                    @endif
                  </td>
                  <td>{{ $v->detalles->sum('cantidad') }}</td>
                  <td><span class="badge bg-light text-dark">{{ ucfirst($v->metodo_pago) }}</span></td>
                  <td class="text-end">
                    @if($anul)
                      <s>{{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}</s>
                    @else
                      {{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Sin ventas en esta fecha.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
