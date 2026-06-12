@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
      <h1>Productos</h1>
      <p class="text-muted small mb-0">Menú visual del negocio</p>
    </div>
    <a href="{{ route('productos.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i> Nuevo producto
    </a>
  </div>

  <section class="section">
    <div class="card mb-3">
      <div class="card-body py-3">
        <form method="GET" action="{{ route('productos') }}" class="row g-2 align-items-end">
          <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o código..." value="{{ request('q') }}">
          </div>
          <div class="col-md-3">
            <select name="categoria_id" class="form-select">
              <option value="">Todas las categorías</option>
              @foreach($categorias as $c)
                <option value="{{ $c->id }}" {{ request('categoria_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <button class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
          </div>
          <div class="col-md-3 text-md-end">
            <small class="text-muted">{{ $items->count() }} producto(s)</small>
          </div>
        </form>
      </div>
    </div>

    <div class="row g-3">
      @forelse($items as $item)
        <div class="col-6 col-md-4 col-xl-3">
          <div class="card admin-card h-100 {{ !$item->activo ? 'no-disponible' : '' }}">
            <div class="admin-card-img">
              @if($item->imagen)
                <img src="{{ asset('storage/' . $item->imagen->ruta) }}" alt="{{ $item->nombre }}">
              @else
                <div class="admin-card-img-placeholder"><i class="bi bi-image"></i></div>
              @endif
              @if(!$item->activo)
                <span class="admin-card-tag bg-secondary">No disponible</span>
              @elseif($item->cantidad <= 0)
                <span class="admin-card-tag bg-danger">Agotado</span>
              @elseif($item->stock_bajo)
                <span class="admin-card-tag bg-warning text-dark">Stock bajo</span>
              @endif
            </div>
            <div class="card-body p-3">
              <small class="text-muted text-uppercase">{{ $item->categoria?->nombre ?? '—' }}</small>
              <h6 class="mb-1 fw-bold">{{ $item->nombre }}</h6>
              <div class="d-flex justify-content-between align-items-baseline mb-2">
                <span class="precio fs-5 fw-bold">{{ $negocio['moneda'] }} {{ number_format($item->precio_venta, 2) }}</span>
                <small class="text-muted"><code>{{ $item->codigo ?? '—' }}</code></small>
              </div>
              <div class="d-flex justify-content-between align-items-center small mb-2">
                <span>Stock: <strong class="{{ $item->cantidad <= 0 ? 'text-danger' : ($item->stock_bajo ? 'text-warning' : 'text-success') }}">{{ $item->cantidad }}</strong></span>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input toggle-activo" type="checkbox" data-id="{{ $item->id }}" {{ $item->activo ? 'checked' : '' }} title="Disponible / No disponible">
                </div>
              </div>
              <div class="d-flex gap-1">
                @if($item->imagen)
                  <a href="{{ route('productos.show.image', $item->imagen->id) }}" class="btn btn-sm btn-outline-secondary flex-grow-1" title="Cambiar imagen"><i class="bi bi-image"></i></a>
                @else
                  <a href="{{ route('productos.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary flex-grow-1" title="Subir imagen"><i class="bi bi-image"></i></a>
                @endif
                <a href="{{ route('productos.edit', $item->id) }}" class="btn btn-sm btn-warning flex-grow-1" title="Editar"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('productos.destroy', $item->id) }}" class="d-inline form-eliminar flex-grow-1">
                  @csrf @method('DELETE')
                  <button type="button" class="btn btn-sm btn-danger w-100 btn-confirmar-eliminar" title="Eliminar"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="alert alert-info">No hay productos. <a href="{{ route('productos.create') }}">Crear el primero</a></div>
        </div>
      @endforelse
    </div>
  </section>
</main>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.toggle-activo').forEach(el => {
    el.addEventListener('change', function () {
      const id = this.dataset.id;
      const estado = this.checked ? 1 : 0;
      fetch(`/productos/cambiar-estado/${id}`, {
        method: 'PATCH',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': window.csrfToken, 'Accept':'application/json'},
        body: JSON.stringify({estado})
      }).then(r => r.json()).then(data => {
        if (!data.ok) Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
        else location.reload();
      });
    });
  });

  document.querySelectorAll('.btn-confirmar-eliminar').forEach(btn => {
    btn.addEventListener('click', function () {
      const form = this.closest('form');
      Swal.fire({
        title: '¿Eliminar producto?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
      }).then(r => { if (r.isConfirmed) form.submit(); });
    });
  });
</script>
@endpush
