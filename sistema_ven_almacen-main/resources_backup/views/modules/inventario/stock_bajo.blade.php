@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1><i class="bi bi-exclamation-triangle text-warning"></i> Stock Bajo</h1></div>
  <section class="section">
    <div class="card"><div class="card-body">
      <p class="text-muted">Productos con stock por debajo o igual al mínimo.</p>
      <table class="table">
        <thead><tr><th>Producto</th><th>Categoría</th><th class="text-center">Stock actual</th><th class="text-center">Stock mínimo</th><th class="text-center">Acción</th></tr></thead>
        <tbody>
          @forelse($items as $p)
            <tr>
              <td>{{ $p->nombre }}</td>
              <td>{{ $p->categoria?->nombre }}</td>
              <td class="text-center"><span class="badge {{ $p->cantidad <= 0 ? 'badge-sin-stock' : 'badge-stock-bajo' }}">{{ $p->cantidad }}</span></td>
              <td class="text-center">{{ $p->stock_minimo }}</td>
              <td class="text-center">
                <a href="{{ route('inventario.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-circle"></i> Reponer</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center py-4 text-success">¡Todo en orden! No hay productos con stock bajo.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div></div>
  </section>
</main>
@endsection