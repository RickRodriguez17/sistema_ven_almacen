@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Ventas por rango</h1>
    @if(auth()->user()->puedeImprimirReportes())
      <a target="_blank" class="btn btn-outline-primary" href="{{ route('reportes.pdf', ['tipo' => 'ventas-rango', 'desde' => $desde, 'hasta' => $hasta, 'user_id' => $userIdSeleccionado]) }}">
        <i class="bi bi-printer"></i> Imprimir PDF
      </a>
    @endif
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
          <div class="col-md-3"><label class="form-label">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="form-control"></div>
          <div class="col-md-3"><label class="form-label">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="form-control"></div>
          <div class="col-md-3"><label class="form-label">Usuario</label>
            <select name="user_id" class="form-select">
              <option value="">— Todos —</option>
              @foreach($usuarios as $u)
                <option value="{{ $u->id }}" @selected($userIdSeleccionado == $u->id)>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2"><button class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button></div>
        </form>

        <div class="row g-3 mb-3">
          <div class="col-md-3"><div class="card bg-light"><div class="card-body p-3"><small class="text-muted">Cantidad</small><h4 class="mb-0">{{ $totales['cantidad'] }}</h4></div></div></div>
          <div class="col-md-3"><div class="card bg-light"><div class="card-body p-3"><small class="text-muted">Total</small><h4 class="mb-0">{{ $negocio['moneda'] }} {{ number_format($totales['total'], 2) }}</h4></div></div></div>
          <div class="col-md-3"><div class="card bg-light"><div class="card-body p-3"><small class="text-muted">Efectivo</small><h4 class="mb-0">{{ $negocio['moneda'] }} {{ number_format($totales['efectivo'], 2) }}</h4></div></div></div>
          <div class="col-md-3"><div class="card bg-light"><div class="card-body p-3"><small class="text-muted">Otros</small><h4 class="mb-0">{{ $negocio['moneda'] }} {{ number_format($totales['total'] - $totales['efectivo'], 2) }}</h4></div></div></div>
        </div>

        <h6>Resumen por día</h6>
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead><tr><th>Fecha</th><th>Ventas</th><th class="text-end">Total</th></tr></thead>
            <tbody>
              @forelse($porDia as $r)
                <tr><td>{{ $r['fecha'] }}</td><td>{{ $r['cantidad'] }}</td><td class="text-end">{{ $negocio['moneda'] }} {{ number_format($r['total'], 2) }}</td></tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted">Sin ventas en este rango.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
