@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Movimientos de Inventario</h1>
    <a href="{{ route('inventario.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo movimiento</a>
  </div>

  <section class="section">
    <div class="card"><div class="card-body">
      <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
          <select name="producto_id" class="form-select">
            <option value="">Todos los productos</option>
            @foreach($productos as $p)
              <option value="{{ $p->id }}" {{ request('producto_id') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="tipo" class="form-select">
            <option value="">Todos los tipos</option>
            @foreach($tipos as $key => $label)
              <option value="{{ $key }}" {{ request('tipo') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
      </form>

      <table class="table table-hover">
        <thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th class="text-center">Cantidad</th><th class="text-center">Anterior</th><th class="text-center">Nuevo</th><th>Motivo</th><th>Usuario</th></tr></thead>
        <tbody>
          @forelse($movimientos as $m)
            <tr>
              <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ $m->producto?->nombre }}</td>
              <td>
                @if($m->tipo === 'entrada') <span class="badge bg-success">Entrada</span>
                @elseif($m->tipo === 'salida') <span class="badge bg-danger">Salida</span>
                @else <span class="badge bg-warning text-dark">Ajuste</span>
                @endif
              </td>
              <td class="text-center fw-bold">{{ $m->cantidad }}</td>
              <td class="text-center text-muted">{{ $m->stock_anterior }}</td>
              <td class="text-center"><strong>{{ $m->stock_nuevo }}</strong></td>
              <td><small>{{ $m->motivo ?? '—' }}</small></td>
              <td>{{ $m->user?->name }}</td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">Sin movimientos registrados.</td></tr>
          @endforelse
        </tbody>
      </table>
      {{ $movimientos->links() }}
    </div></div>
  </section>
</main>
@endsection
