@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Editar Cliente</h1></div>
  <section class="section">
    <div class="card"><div class="card-body">
      <form action="{{ route('clientes.update', $item->id) }}" method="POST" class="row g-3">
        @csrf @method('PUT')
        <div class="col-md-6"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $item->nombre) }}"></div>
        <div class="col-md-6"><label class="form-label">Apellido</label><input type="text" name="apellido" class="form-control" value="{{ old('apellido', $item->apellido) }}"></div>
        <div class="col-md-6"><label class="form-label">Teléfono</label><input type="text" name="telefono" class="form-control" value="{{ old('telefono', $item->telefono) }}"></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $item->email) }}"></div>
        <div class="col-12">
          <button class="btn btn-warning"><i class="bi bi-check-circle"></i> Actualizar</button>
          <a href="{{ route('clientes') }}" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div></div>
  </section>
</main>
@endsection
