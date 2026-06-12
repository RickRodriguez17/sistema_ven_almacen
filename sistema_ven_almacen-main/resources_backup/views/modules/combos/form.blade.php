@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">

  <div class="pagetitle">
    <h1><i class="bi bi-stars"></i> {{ $titulo }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('combos') }}">Combos</a></li>
        <li class="breadcrumb-item active">{{ isset($combo) ? 'Editar' : 'Nuevo' }}</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <form method="POST"
          action="{{ isset($combo) ? route('combos.update', $combo->id) : route('combos.store') }}"
          enctype="multipart/form-data" class="row g-3">
      @csrf
      @if(isset($combo)) @method('PUT') @endif

      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label">Nombre del combo *</label>
                <input type="text" name="nombre" class="form-control" required
                       value="{{ old('nombre', $combo->nombre ?? '') }}">
                @error('nombre')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Código</label>
                <input type="text" name="codigo" class="form-control"
                       value="{{ old('codigo', $combo->codigo ?? '') }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Precio combo (Bs) *</label>
                <input type="number" step="0.01" min="0" name="precio" class="form-control" required
                       value="{{ old('precio', $combo->precio ?? '') }}">
                @error('precio')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select name="activo" class="form-select">
                  <option value="1" {{ old('activo', $combo->activo ?? 1) ? 'selected' : '' }}>Activo</option>
                  <option value="0" {{ ! old('activo', $combo->activo ?? 1) ? 'selected' : '' }}>Inactivo</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Imagen</label>
                <input type="file" name="imagen" accept="image/*" class="form-control">
                @if(isset($combo) && $combo->imagenUrl())
                  <div class="mt-2"><img src="{{ $combo->imagenUrl() }}" style="height:60px;" class="rounded"></div>
                @endif
              </div>
              <div class="col-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" rows="2" class="form-control">{{ old('descripcion', $combo->descripcion ?? '') }}</textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Productos incluidos en el combo</h6>
              <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">
                <i class="bi bi-plus-circle"></i> Agregar producto
              </button>
            </div>
            <div id="items-list">
              @php $existing = isset($combo) ? $combo->items : collect(); @endphp
              @forelse($existing as $i => $it)
                <div class="row g-2 align-items-end mb-2 item-row">
                  <div class="col-md-8">
                    <select name="items[{{ $i }}][producto_id]" class="form-select" required>
                      @foreach($productos as $p)
                        <option value="{{ $p->id }}" {{ $it->producto_id == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <input type="number" name="items[{{ $i }}][cantidad]" min="1" class="form-control" value="{{ $it->cantidad }}" required>
                  </div>
                  <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-del"><i class="bi bi-trash"></i></button>
                  </div>
                </div>
              @empty
              @endforelse
            </div>
            @error('items')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i> Guardar</button>
            <a href="{{ route('combos') }}" class="btn btn-outline-secondary w-100 mt-2">Cancelar</a>
          </div>
        </div>
      </div>
    </form>
  </section>

</main>

<template id="tpl-item">
  <div class="row g-2 align-items-end mb-2 item-row">
    <div class="col-md-8">
      <select name="items[__i__][producto_id]" class="form-select" required>
        @foreach($productos as $p)
          <option value="{{ $p->id }}">{{ $p->nombre }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <input type="number" name="items[__i__][cantidad]" min="1" value="1" class="form-control" required>
    </div>
    <div class="col-md-1">
      <button type="button" class="btn btn-outline-danger btn-del"><i class="bi bi-trash"></i></button>
    </div>
  </div>
</template>
@endsection

@push('scripts')
<script>
(function () {
  const list = document.getElementById('items-list');
  const tpl = document.getElementById('tpl-item').innerHTML;
  let counter = list.querySelectorAll('.item-row').length;

  document.getElementById('btn-add-item').addEventListener('click', () => {
    list.insertAdjacentHTML('beforeend', tpl.replaceAll('__i__', counter++));
  });

  list.addEventListener('click', (e) => {
    if (e.target.closest('.btn-del')) {
      e.target.closest('.item-row').remove();
    }
  });

  if (list.querySelectorAll('.item-row').length === 0) {
    document.getElementById('btn-add-item').click();
  }
})();
</script>
@endpush
