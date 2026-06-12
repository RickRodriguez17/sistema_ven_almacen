@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Productos con stock bajo</h1>
    @if(auth()->user()->puedeImprimirReportes())
      <a target="_blank" class="btn btn-outline-primary" href="{{ route('reportes.pdf', ['tipo' => 'stock-bajo']) }}">
        <i class="bi bi-printer"></i> Imprimir PDF
      </a>
    @endif
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead><tr><th>Código</th><th>Producto</th><th>Categoría</th><th class="text-end">Stock</th><th class="text-end">Mínimo</th><th>Estado</th></tr></thead>
            <tbody>
              @forelse($productos as $p)
                <tr>
                  <td>{{ $p->codigo }}</td>
                  <td>{{ $p->nombre }}</td>
                  <td>{{ $p->categoria?->nombre ?? '—' }}</td>
                  <td class="text-end"><span class="badge {{ $p->cantidad <= 0 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $p->cantidad }}</span></td>
                  <td class="text-end">{{ $p->stock_minimo }}</td>
                  <td>
                    @if($p->cantidad <= 0)
                      <span class="text-danger">Agotado</span>
                    @else
                      <span class="text-warning">Bajo</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No hay productos con stock bajo. ¡Bien!</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
