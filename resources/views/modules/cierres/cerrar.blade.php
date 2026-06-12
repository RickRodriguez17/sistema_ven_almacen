@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Cerrar turno de ventas</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Cerrar turno</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Resumen del turno</h5>
            <dl class="row mb-0">
              <dt class="col-sm-5">Apertura</dt>
              <dd class="col-sm-7">{{ $cierre->fecha_apertura->format('d/m/Y H:i') }}</dd>
              <dt class="col-sm-5">Cajero</dt>
              <dd class="col-sm-7">{{ $cierre->user->name }}</dd>
              <dt class="col-sm-5">Fondo inicial</dt>
              <dd class="col-sm-7">{{ $negocio['moneda'] }} {{ number_format($cierre->fondo_inicial, 2) }}</dd>
              <dt class="col-sm-5">Cantidad de ventas</dt>
              <dd class="col-sm-7">{{ $resumen['cantidad_ventas'] }}</dd>
              <dt class="col-sm-5"><strong>Total ventas</strong></dt>
              <dd class="col-sm-7"><strong>{{ $negocio['moneda'] }} {{ number_format($resumen['total_ventas'], 2) }}</strong></dd>
            </dl>
            <hr>
            <h6>Por método de pago</h6>
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-cash-stack"></i> Efectivo</span><strong>{{ $negocio['moneda'] }} {{ number_format($resumen['total_efectivo'], 2) }}</strong></li>
              <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-credit-card"></i> Tarjeta</span><strong>{{ $negocio['moneda'] }} {{ number_format($resumen['total_tarjeta'], 2) }}</strong></li>
              <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-phone"></i> Yape</span><strong>{{ $negocio['moneda'] }} {{ number_format($resumen['total_yape'], 2) }}</strong></li>
            </ul>
            @if($resumen['total_gastos'] > 0)
              <hr>
              <h6>Gastos del turno</h6>
              <ul class="list-group list-group-flush mb-2">
                @foreach($gastos as $g)
                  <li class="list-group-item d-flex justify-content-between py-1">
                    <small>{{ $g->created_at->format('H:i') }} — {{ $g->concepto }}</small>
                    <small class="text-danger">− {{ $negocio['moneda'] }} {{ number_format($g->monto, 2) }}</small>
                  </li>
                @endforeach
                <li class="list-group-item d-flex justify-content-between fw-bold bg-light">
                  <span>Total gastos</span>
                  <span class="text-danger">− {{ $negocio['moneda'] }} {{ number_format($resumen['total_gastos'], 2) }}</span>
                </li>
              </ul>
            @endif

            @if(isset($anuladas) && $anuladas->count())
              <hr>
              <h6 class="text-danger">
                <i class="bi bi-x-octagon"></i> Ventas anuladas en este turno ({{ $anuladas->count() }})
              </h6>
              <p class="small text-muted">
                Monto anulado: <strong>{{ $negocio['moneda'] }} {{ number_format($anuladas->sum('total_venta'), 2) }}</strong>.
                Este monto NO se incluye en el total del turno.
              </p>
              <ul class="list-group list-group-flush mb-2">
                @foreach($anuladas as $a)
                  <li class="list-group-item d-flex justify-content-between py-1">
                    <small>
                      #{{ $a->numero_ticket }} · {{ $a->created_at->format('H:i') }}
                      @if($a->motivo_anulacion) — <em>{{ $a->motivo_anulacion }}</em>@endif
                    </small>
                    <small class="text-danger"><s>{{ $negocio['moneda'] }} {{ number_format($a->total_venta, 2) }}</s></small>
                  </li>
                @endforeach
              </ul>
            @endif

            <div class="alert alert-info mt-3 mb-0">
              <strong>Efectivo esperado en caja:</strong>
              {{ $negocio['moneda'] }} {{ number_format($cierre->fondo_inicial + $resumen['total_efectivo'] - $resumen['total_gastos'], 2) }}
              <small class="d-block text-muted">
                (fondo inicial {{ $negocio['moneda'] }} {{ number_format($cierre->fondo_inicial, 2) }}
                + efectivo recibido {{ $negocio['moneda'] }} {{ number_format($resumen['total_efectivo'], 2) }}
                @if($resumen['total_gastos'] > 0)
                  − gastos {{ $negocio['moneda'] }} {{ number_format($resumen['total_gastos'], 2) }}
                @endif)
              </small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Cuadre de caja</h5>
            <form method="POST" action="{{ route('cierres.cerrar') }}">
              @csrf
              <div class="mb-3">
                <label class="form-label">Efectivo contado en caja ({{ $negocio['moneda'] }})</label>
                <input type="number" step="0.01" min="0" name="efectivo_contado" class="form-control form-control-lg" required autofocus>
                @error('efectivo_contado') <small class="text-danger">{{ $message }}</small> @enderror
                <small class="text-muted">Cuenta los billetes y monedas físicas y digita aquí el total.</small>
              </div>
              <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" rows="3" class="form-control" placeholder="Notas sobre faltantes, sobrantes, etc."></textarea>
              </div>
              <button class="btn btn-danger w-100">
                <i class="bi bi-stop-fill"></i> Cerrar turno
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
