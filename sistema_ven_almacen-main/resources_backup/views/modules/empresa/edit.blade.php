@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $titulo }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Empresa</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Datos del negocio</h5>
            <p class="text-muted small">Estos datos aparecerán en el ticket de venta, los reportes y la cabecera del sistema.</p>

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0 small">
                  @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('empresa.update') }}" enctype="multipart/form-data" class="row g-3">
              @csrf
              @method('PUT')

              <div class="col-md-6">
                <label class="form-label">Nombre comercial *</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $empresa->nombre) }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Razón social</label>
                <input type="text" name="razon_social" class="form-control" value="{{ old('razon_social', $empresa->razon_social) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label">NIT</label>
                <input type="text" name="nit" class="form-control" value="{{ old('nit', $empresa->nit) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $empresa->telefono) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $empresa->email) }}">
              </div>

              <div class="col-md-12">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $empresa->direccion) }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">Moneda *</label>
                <input type="text" name="moneda" class="form-control" value="{{ old('moneda', $empresa->moneda ?? 'Bs') }}" maxlength="8" required>
              </div>

              <div class="col-md-3">
                <label class="form-label">IVA (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="iva_porcentaje" class="form-control" value="{{ old('iva_porcentaje', $empresa->iva_porcentaje ?? 0) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label">Mensaje del ticket</label>
                <input type="text" name="mensaje_ticket" class="form-control" value="{{ old('mensaje_ticket', $empresa->mensaje_ticket ?? '¡Gracias por su compra!') }}">
              </div>

              <div class="col-md-6">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
                @if($empresa->logo_path)
                  <div class="mt-2">
                    <img src="{{ asset('storage/' . $empresa->logo_path) }}" alt="Logo" style="max-height:60px;">
                  </div>
                @endif
              </div>

              <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('home') }}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Vista previa del ticket</h5>
            <div class="border rounded p-3 small text-center" style="background:#fff;">
              @if($empresa->logo_path)
                <img src="{{ asset('storage/' . $empresa->logo_path) }}" alt="Logo" style="max-height:60px;" class="mb-2">
              @endif
              <div class="fw-bold">{{ $empresa->nombre ?? 'Pollos Mafu' }}</div>
              @if($empresa->razon_social)<div>{{ $empresa->razon_social }}</div>@endif
              @if($empresa->direccion)<div>{{ $empresa->direccion }}</div>@endif
              @if($empresa->telefono)<div>Tel: {{ $empresa->telefono }}</div>@endif
              @if($empresa->nit)<div>NIT: {{ $empresa->nit }}</div>@endif
              <hr class="my-2">
              <div class="text-muted">{{ $empresa->mensaje_ticket ?? '¡Gracias por su compra!' }}</div>
              <div class="mt-2"><strong>{{ $empresa->moneda ?? 'Bs' }}</strong> moneda usada</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
