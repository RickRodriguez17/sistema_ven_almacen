@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Nueva Categoría</h1></div>
  <section class="section">
    <div class="card"><div class="card-body">
      <form action="{{ route('categorias.store') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label">Nombre *</label>
          <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" required value="{{ old('nombre') }}">
          @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Guardar</button>
        <a href="{{ route('categorias') }}" class="btn btn-secondary">Cancelar</a>
      </form>
    </div></div>
  </section>
</main>
@endsection
