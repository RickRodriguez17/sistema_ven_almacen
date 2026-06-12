@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Iniciar turno de ventas</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Iniciar turno</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Apertura de caja</h5>
            <p class="text-muted">Ingresa el fondo inicial con el que abres la caja.</p>

            <form method="POST" action="{{ route('cierres.iniciar') }}">
              @csrf
              <div class="mb-3">
                <label class="form-label">Fondo inicial ({{ $negocio['moneda'] }})</label>
                <input type="number" step="0.01" min="0" name="fondo_inicial" class="form-control form-control-lg" value="0" required autofocus>
                @error('fondo_inicial') <small class="text-danger">{{ $message }}</small> @enderror
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-primary btn-pos">
                  <i class="bi bi-play-fill"></i> Iniciar venta
                </button>
                <a href="{{ route('home') }}" class="btn btn-secondary">Cancelar</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
