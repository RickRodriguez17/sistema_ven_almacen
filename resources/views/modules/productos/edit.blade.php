@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Editar Producto</h1></div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Datos del producto</h5>

        <form action="{{ route('productos.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
          @csrf @method('PUT')

          <div class="col-md-6">
            <label class="form-label">Categoría *</label>
            <select name="categoria_id" class="form-select" required>
              @foreach($categorias as $c)
                <option value="{{ $c->id }}" {{ $item->categoria_id == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Código</label>
            <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $item->codigo) }}">
          </div>
          <div class="col-12">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $item->nombre) }}">
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" rows="3" class="form-control">{{ old('descripcion', $item->descripcion) }}</textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Stock mínimo</label>
            <input type="number" min="0" name="stock_minimo" class="form-control" value="{{ old('stock_minimo', $item->stock_minimo) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Precio compra</label>
            <input type="number" step="0.01" min="0" name="precio_compra" class="form-control" value="{{ old('precio_compra', $item->precio_compra) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Precio venta *</label>
            <input type="number" step="0.01" min="0" name="precio_venta" class="form-control" required value="{{ old('precio_venta', $item->precio_venta) }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Imagen del producto</label>
            @if($item->imagen)
              <div class="mb-2">
                <img src="{{ asset('storage/'.$item->imagen->ruta) }}" alt="{{ $item->nombre }}" class="img-thumbnail" style="max-height:120px;">
                <small class="d-block text-muted">Imagen actual. Sube una nueva para reemplazarla.</small>
              </div>
            @endif
            <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted">JPG, PNG o WebP. Máximo 4 MB.</small>
          </div>

          <div class="col-12 alert alert-info small mb-0">
            <i class="bi bi-info-circle"></i> El stock no se modifica desde aquí. Usa <a href="{{ route('inventario.create') }}">Movimientos de inventario</a> para ajustarlo.
          </div>

          <div class="col-12">
            <button class="btn btn-warning"><i class="bi bi-check-circle me-1"></i> Actualizar</button>
            <a href="{{ route('productos') }}" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>
@endsection