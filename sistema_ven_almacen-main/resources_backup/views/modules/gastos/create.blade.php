@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Registrar gasto urgente</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('gastos') }}">Gastos</a></li>
        <li class="breadcrumb-item active">Nuevo</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <p class="text-muted small">
              Anota un gasto urgente que sale de la caja (compras pequeñas, combustible, propinas, etc.).
              El monto se descontará automáticamente del efectivo esperado al cerrar el turno.
            </p>

            @if(session('error'))
              <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('gastos.store') }}" method="POST" class="row g-3">
              @csrf

              <div class="col-12">
                <label class="form-label">Concepto / detalle *</label>
                <input type="text" name="concepto" class="form-control" maxlength="255"
                       placeholder="Ej: Compra de gas, propina al delivery, taxi..."
                       required autofocus value="{{ old('concepto') }}">
                @error('concepto') <small class="text-danger">{{ $message }}</small> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Monto ({{ $negocio['moneda'] }}) *</label>
                <input type="number" step="0.01" min="0.01" name="monto" class="form-control form-control-lg"
                       required value="{{ old('monto') }}">
                @error('monto') <small class="text-danger">{{ $message }}</small> @enderror
              </div>

              <div class="col-12">
                <button class="btn btn-danger">
                  <i class="bi bi-check-circle"></i> Registrar gasto
                </button>
                <a href="{{ route('gastos') }}" class="btn btn-secondary">Cancelar</a>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">Turno actual</h6>
            <dl class="row mb-0">
              <dt class="col-sm-5">Cajero</dt>
              <dd class="col-sm-7">{{ $abierto->user->name }}</dd>
              <dt class="col-sm-5">Apertura</dt>
              <dd class="col-sm-7">{{ $abierto->fecha_apertura->format('d/m/Y H:i') }}</dd>
              <dt class="col-sm-5">Fondo inicial</dt>
              <dd class="col-sm-7">{{ $negocio['moneda'] }} {{ number_format($abierto->fondo_inicial, 2) }}</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
