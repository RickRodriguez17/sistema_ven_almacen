@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Eliminar Producto</h1></div>
  <section class="section">
    <div class="card">
      <div class="card-body">
        <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>

        <ul class="list-group mb-3">
          <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Categoría</span><strong>{{ $items->categoria?->nombre }}</strong></li>
          <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Nombre</span><strong>{{ $items->nombre }}</strong></li>
          <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Stock</span><strong>{{ $items->cantidad }}</strong></li>
          <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Precio venta</span><strong>{{ $negocio['moneda'] }} {{ number_format($items->precio_venta, 2) }}</strong></li>
        </ul>

        <form action="{{ route('productos.destroy', $items->id) }}" method="POST">
          @csrf @method('DELETE')
          <button class="btn btn-danger"><i class="bi bi-trash"></i> Eliminar</button>
          <a href="{{ route('productos') }}" class="btn btn-secondary">Cancelar</a>
        </form>
      </div>
    </div>
  </section>
</main>
@endsection
