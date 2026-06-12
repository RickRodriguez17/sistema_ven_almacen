@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Nuevo Cliente</h1></div>
  <section class="section">
    <div class="card"><div class="card-body">
      <form action="{{ route('clientes.store') }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-6"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" required value="{{ old('nombre') }}"></div>
        <div class="col-md-6"><label class="form-label">Apellido</label><input type="text" name="apellido" class="form-control" value="{{ old('apellido') }}"></div>
        <div class="col-md-6"><label class="form-label">Teléfono</label><input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}"></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}"></div>
        <div class="col-12">
          <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Guardar</button>
          <a href="{{ route('clientes') }}" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div></div>
  </section>
</main>
@endsection
