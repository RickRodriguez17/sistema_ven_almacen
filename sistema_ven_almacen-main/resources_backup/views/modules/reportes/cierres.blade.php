@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Historial de cierres</h1></div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
          <div class="col-md-3"><label class="form-label">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="form-control"></div>
          <div class="col-md-3"><label class="form-label">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="form-control"></div>
          <div class="col-md-3"><label class="form-label">Cajero</label>
            <select name="user_id" class="form-select">
              <option value="">— Todos —</option>
              @foreach($usuarios as $u)
                <option value="{{ $u->id }}" @selected($userIdSeleccionado == $u->id)>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2"><button class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button></div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead><tr><th>#</th><th>Cajero</th><th>Apertura</th><th>Cierre</th><th>Ventas</th><th class="text-end">Total</th><th class="text-end">Diferencia</th><th>Estado</th><th></th></tr></thead>
            <tbody>
              @forelse($cierres as $c)
                <tr>
                  <td>{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }}</td>
                  <td>{{ $c->user->name }}</td>
                  <td>{{ $c->fecha_apertura->format('d/m/Y H:i') }}</td>
                  <td>{{ $c->fecha_cierre?->format('d/m/Y H:i') ?? '—' }}</td>
                  <td>{{ $c->cantidad_ventas }}</td>
                  <td class="text-end">{{ $negocio['moneda'] }} {{ number_format($c->total_ventas, 2) }}</td>
                  <td class="text-end">{{ $c->diferencia !== null ? $negocio['moneda'] . ' ' . number_format($c->diferencia, 2) : '—' }}</td>
                  <td>
                    @if($c->estado === 'abierto')
                      <span class="badge bg-warning text-dark">Abierto</span>
                    @else
                      <span class="badge bg-secondary">Cerrado</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('cierres.show', $c->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    @if(auth()->user()->puedeImprimirReportes() && $c->estado === 'cerrado')
                      <a href="{{ route('cierres.pdf', $c->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></a>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="9" class="text-center text-muted py-3">Sin cierres en este rango.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
