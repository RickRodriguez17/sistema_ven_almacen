@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Eliminar Categoría</h1></div>
  <section class="section">
    <div class="card"><div class="card-body">
      <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> ¿Estás seguro de eliminar esta categoría?</p>
      <form action="{{ route('categorias.destroy', $item->id) }}" method="POST">
        @csrf @method('DELETE')
        <div class="mb-3">
          <label class="form-label">Categoría</label>
          <input type="text" class="form-control" readonly value="{{ $item->nombre }}">
        </div>
        <button class="btn btn-danger"><i class="bi bi-trash"></i> Eliminar</button>
        <a href="{{ route('categorias') }}" class="btn btn-secondary">Cancelar</a>
      </form>
    </div></div>
  </section>
</main>
@endsection
