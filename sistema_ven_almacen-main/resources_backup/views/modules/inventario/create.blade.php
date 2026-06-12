@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle"><h1>Nuevo Movimiento</h1></div>
  <section class="section">
    <div class="card"><div class="card-body">
      <form action="{{ route('inventario.store') }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-6">
          <label class="form-label">Producto *</label>
          <select name="producto_id" class="form-select" required>
            <option value="">— Selecciona —</option>
            @foreach($productos as $p)
              <option value="{{ $p->id }}">{{ $p->nombre }} (Stock: {{ $p->cantidad }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tipo *</label>
          <select name="tipo" class="form-select" required>
            @foreach($tipos as $key => $label)
              <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Cantidad *</label>
          <input type="number" min="1" name="cantidad" class="form-control" required value="1">
        </div>
        <div class="col-12">
          <label class="form-label">Motivo</label>
          <input type="text" name="motivo" class="form-control" placeholder="Merma, ingreso adicional, ajuste por inventario, etc.">
        </div>
        <div class="col-12 alert alert-info small mb-0">
          <strong>Entrada:</strong> suma al stock. <strong>Salida:</strong> resta del stock. <strong>Ajuste:</strong> establece el stock al valor indicado.
        </div>
        <div class="col-12">
          <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Registrar</button>
          <a href="{{ route('inventario') }}" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div></div>
  </section>
</main>
@endsection
