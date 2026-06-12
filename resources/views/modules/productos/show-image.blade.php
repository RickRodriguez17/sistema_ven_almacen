@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Cambiar imagen</h1></div>
  <section class="section">
    <div class="card"><div class="card-body">
      <p class="text-muted">Imagen actual:</p>
      <img src="{{ asset('storage/' . $item->ruta) }}" alt="" style="max-width:240px;border-radius:8px;" class="mb-3">
      <form action="{{ route('productos.update.image', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="mb-3">
          <label class="form-label">Nueva imagen *</label>
          <input type="file" name="imagen" class="form-control" accept="image/*" required>
        </div>
        <button class="btn btn-warning"><i class="bi bi-check-circle"></i> Actualizar imagen</button>
        <a href="{{ route('productos') }}" class="btn btn-secondary">Cancelar</a>
      </form>
    </div></div>
  </section>
</main>
@endsection
