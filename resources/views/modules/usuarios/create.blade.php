@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Nuevo Usuario</h1></div>
  <section class="section">
    <div class="card"><div class="card-body">
      <form action="{{ route('usuarios.store') }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-6"><label class="form-label">Nombre *</label><input type="text" name="name" class="form-control" required value="{{ old('name') }}"></div>
        <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="{{ old('email') }}"></div>
        <div class="col-md-6"><label class="form-label">Contraseña * (min 6)</label><input type="password" name="password" class="form-control" required minlength="6"></div>
        <div class="col-md-6">
          <label class="form-label">Rol *</label>
          <select name="rol" class="form-select" required>
            <option value="">— Selecciona —</option>
            @foreach($roles as $key => $label)
              <option value="{{ $key }}" {{ old('rol') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12">
          <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Guardar</button>
          <a href="{{ route('usuarios') }}" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div></div>
  </section>
</main>
@endsection
