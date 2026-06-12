@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Editar Usuario</h1></div>
  <section class="section">
    <div class="card"><div class="card-body">
      <form action="{{ route('usuarios.update', $item->id) }}" method="POST" class="row g-3">
        @csrf @method('PUT')
        <div class="col-md-6"><label class="form-label">Nombre *</label><input type="text" name="name" class="form-control" required value="{{ old('name', $item->name) }}"></div>
        <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="{{ old('email', $item->email) }}"></div>
        <div class="col-md-6">
          <label class="form-label">Rol *</label>
          <select name="rol" class="form-select" required>
            @foreach($roles as $key => $label)
              <option value="{{ $key }}" {{ $item->rol === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12">
          <button class="btn btn-warning"><i class="bi bi-check-circle"></i> Actualizar</button>
          <a href="{{ route('usuarios') }}" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div></div>
  </section>
</main>
@endsection
