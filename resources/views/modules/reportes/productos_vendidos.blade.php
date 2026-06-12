@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Productos vendidos</h1>
    @if(auth()->user()->puedeImprimirReportes())
      <a target="_blank" class="btn btn-outline-primary" href="{{ route('reportes.pdf', ['tipo' => 'productos-vendidos', 'desde' => $desde, 'hasta' => $hasta, 'categoria_id' => $categoriaIdSeleccionada]) }}">
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
          <div class="col-md-3"><label class="form-label">Categoría</label>
            <select name="categoria_id" class="form-select">
              <option value="">— Todas —</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" @selected($categoriaIdSeleccionada == $cat->id)>{{ $cat->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2"><button class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button></div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead><tr><th>#</th><th>Producto</th><th>Categoría</th><th class="text-end">Unidades</th><th class="text-end">Ingreso</th></tr></thead>
            <tbody>
              @forelse($productos as $i => $p)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td>{{ $p->nombre }}</td>
                  <td>{{ $p->categoria ?? '—' }}</td>
                  <td class="text-end">{{ $p->total_unidades }}</td>
                  <td class="text-end">{{ $negocio['moneda'] }} {{ number_format($p->total_ingreso, 2) }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Sin datos en este periodo.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
