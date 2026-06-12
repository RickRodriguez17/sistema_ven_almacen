@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Crear Producto</h1></div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Datos del producto</h5>

        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
          @csrf
          <div class="col-md-6">
            <label class="form-label">Categoría *</label>
            <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
              <option value="">— Selecciona —</option>
              @foreach($categorias as $c)
                <option value="{{ $c->id }}" {{ old('categoria_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
              @endforeach
            </select>
            @error('categoria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Código</label>
            <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}" placeholder="HAM-001">
          </div>

          <div class="col-12">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" required value="{{ old('nombre') }}">
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" rows="3" class="form-control">{{ old('descripcion') }}</textarea>
          </div>

          <div class="col-md-3">
            <label class="form-label">Stock inicial</label>
            <input type="number" min="0" name="cantidad" class="form-control" value="{{ old('cantidad', 0) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Stock mínimo</label>
            <input type="number" min="0" name="stock_minimo" class="form-control" value="{{ old('stock_minimo', 5) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Precio compra</label>
            <input type="number" step="0.01" min="0" name="precio_compra" class="form-control" value="{{ old('precio_compra', 0) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Precio venta *</label>
            <input type="number" step="0.01" min="0" name="precio_venta" class="form-control @error('precio_venta') is-invalid @enderror" required value="{{ old('precio_venta', 0) }}">
            @error('precio_venta')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label">Imagen</label>
            <input type="file" name="imagen" class="form-control" accept="image/*">
          </div>

          <div class="col-12">
            <button class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Guardar</button>
            <a href="{{ route('productos') }}" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>
@endsection
