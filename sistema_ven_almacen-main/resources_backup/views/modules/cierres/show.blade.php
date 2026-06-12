@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>{{ $titulo }}</h1>
    <div>
      @if(auth()->user()->puedeImprimirReportes() && $cierre->estado === 'cerrado')
        <a href="{{ route('cierres.pdf', $cierre->id) }}" target="_blank" class="btn btn-outline-primary">
          <i class="bi bi-printer"></i> Imprimir PDF
        </a>
      @endif
      <a href="{{ route('cierres') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-5">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Información</h5>
            <dl class="row mb-0">
              <dt class="col-sm-5">Cajero</dt><dd class="col-sm-7">{{ $cierre->user->name }}</dd>
              <dt class="col-sm-5">Apertura</dt><dd class="col-sm-7">{{ $cierre->fecha_apertura->format('d/m/Y H:i') }}</dd>
              <dt class="col-sm-5">Cierre</dt><dd class="col-sm-7">{{ $cierre->fecha_cierre?->format('d/m/Y H:i') ?? 'Aún abierto' }}</dd>
              <dt class="col-sm-5">Estado</dt>
              <dd class="col-sm-7">
                @if($cierre->estado === 'abierto')
                  <span class="badge bg-warning text-dark">Abierto</span>
                @else
                  <span class="badge bg-secondary">Cerrado</span>
                @endif
              </dd>
              <dt class="col-sm-5">Fondo inicial</dt><dd class="col-sm-7">{{ $negocio['moneda'] }} {{ number_format($cierre->fondo_inicial, 2) }}</dd>
              <dt class="col-sm-5">Cantidad ventas</dt><dd class="col-sm-7">{{ $cierre->cantidad_ventas }}</dd>
              <dt class="col-sm-5"><strong>Total ventas</strong></dt><dd class="col-sm-7"><strong>{{ $negocio['moneda'] }} {{ number_format($cierre->total_ventas, 2) }}</strong></dd>
            </dl>
          </div>
        </div>

        <div class="card">
  <div class="card-body">
    <h5 class="card-title">Por método de pago</h5>

    <ul class="list-group list-group-flush">
      <li class="list-group-item d-flex justify-content-between">
        <span>Efectivo</span>
        <strong>{{ $negocio['moneda'] }} {{ number_format($cierre->total_efectivo, 2) }}</strong>
      </li>

      <li class="list-group-item d-flex justify-content-between">
        <span>Tarjeta</span>
        <strong>{{ $negocio['moneda'] }} {{ number_format($cierre->total_tarjeta, 2) }}</strong>
      </li>

      <li class="list-group-item d-flex justify-content-between">
        <span>Yape</span>
        <strong>{{ $negocio['moneda'] }} {{ number_format($cierre->total_yape, 2) }}</strong>
      </li>

      @if(($cierre->total_gastos ?? 0) > 0)
        <li class="list-group-item d-flex justify-content-between">
          <span class="text-danger">Gastos del turno</span>
          <strong class="text-danger">
            − {{ $negocio['moneda'] }} {{ number_format($cierre->total_gastos, 2) }}
          </strong>
        </li>
      @endif
    </ul>

    @if($cierre->efectivo_contado !== null)
      <div class="alert alert-light mt-3 mb-0">
        <div class="d-flex justify-content-between">
          <span>Efectivo contado</span>
          <strong>{{ $negocio['moneda'] }} {{ number_format($cierre->efectivo_contado, 2) }}</strong>
        </div>

        <div class="d-flex justify-content-between">
          <span>Diferencia</span>
          <strong class="{{ $cierre->diferencia == 0 ? 'text-success' : ($cierre->diferencia > 0 ? 'text-info' : 'text-danger') }}">
            {{ $negocio['moneda'] }} {{ number_format($cierre->diferencia, 2) }}
          </strong>
        </div>
      </div>
    @endif

    @if($cierre->observaciones)
      <div class="mt-3">
        <strong>Observaciones:</strong>
        <p class="mb-0">{{ $cierre->observaciones }}</p>
      </div>
    @endif

  </div>
</div>
      </div>

      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Ventas del turno</h5>
            <p class="small text-muted mb-2">
              Las ventas anuladas se muestran tachadas y NO suman al total del cierre.
            </p>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Ticket</th><th>Hora</th><th>Estado</th><th>Items</th><th>Método</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                  @forelse($ventas as $v)
                    @php $anul = $v->estado === \App\Models\Venta::ESTADO_ANULADA; @endphp
                    <tr class="{{ $anul ? 'table-danger text-muted' : '' }}">
                      <td><a href="{{ route('detalle-venta.show', $v->id) }}">#{{ $v->numero_ticket }}</a></td>
                      <td>{{ $v->created_at->format('H:i') }}</td>
                      <td>
                        @if($anul)
                          <span class="badge bg-danger">Anulada</span>
                        @elseif($v->estado === 'pendiente')
                          <span class="badge bg-warning text-dark">Pendiente</span>
                        @else
                          <span class="badge bg-success">Pagada</span>
                        @endif
                      </td>
                      <td>{{ $v->detalles->sum('cantidad') }}</td>
                      <td><span class="badge bg-light text-dark">{{ ucfirst($v->metodo_pago) }}</span></td>
                      <td class="text-end">
                        @if($anul)
                          <s>{{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}</s>
                        @else
                          {{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="6" class="text-center text-muted">Sin ventas en este turno.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        @if(isset($anuladas) && $anuladas->count())
          <div class="card border-danger">
            <div class="card-body">
              <h5 class="card-title text-danger">
                <i class="bi bi-x-octagon"></i> Ventas anuladas en este turno ({{ $anuladas->count() }})
              </h5>
              <p class="small text-muted">
                Estas ventas se cancelaron y NO se incluyen en el total del cierre.
                El monto total anulado fue
                <strong class="text-danger">{{ $negocio['moneda'] }} {{ number_format($anuladas->sum('total_venta'), 2) }}</strong>.
              </p>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Ticket</th>
                      <th>Hora</th>
                      <th>Anulada por</th>
                      <th>Motivo</th>
                      <th class="text-end">Monto</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($anuladas as $v)
                      <tr>
                        <td><a href="{{ route('detalle-venta.show', $v->id) }}">#{{ $v->numero_ticket }}</a></td>
                        <td>{{ $v->created_at->format('H:i') }}</td>
                        <td>
                          {{ $v->anuladaPor?->name ?? $v->user?->name }}
                          @if($v->anulada_at)
                            <small class="text-muted d-block">{{ $v->anulada_at->format('d/m H:i') }}</small>
                          @endif
                        </td>
                        <td><small>{{ $v->motivo_anulacion ?? '—' }}</small></td>
                        <td class="text-end"><s>{{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}</s></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif

        @if(isset($gastos) && $gastos->count())
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Gastos del turno</h5>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead><tr><th>Hora</th><th>Concepto</th><th>Cajero</th><th class="text-end">Monto</th></tr></thead>
                  <tbody>
                    @foreach($gastos as $g)
                      <tr>
                        <td>{{ $g->created_at->format('H:i') }}</td>
                        <td>{{ $g->concepto }}</td>
                        <td>{{ $g->user?->name }}</td>
                        <td class="text-end text-danger">− {{ $negocio['moneda'] }} {{ number_format($g->monto, 2) }}</td>
                      </tr>
                    @endforeach
                    <tr class="fw-bold">
                      <td colspan="3" class="text-end">Total gastos</td>
                      <td class="text-end text-danger">− {{ $negocio['moneda'] }} {{ number_format($cierre->total_gastos ?? $gastos->sum('monto'), 2) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </section>
</main>
@endsection
