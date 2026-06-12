@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Historial de cierres</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Cierres de caja</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    @if($abierto)
      <div class="alert alert-warning d-flex justify-content-between align-items-center">
        <div>
          <strong>Tienes un turno abierto</strong> desde {{ $abierto->fecha_apertura->format('d/m/Y H:i') }}.
        </div>
        <a href="{{ route('cierres.cerrar.form') }}" class="btn btn-danger btn-sm">Cerrar turno ahora</a>
      </div>
    @endif

    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Cierres registrados</h5>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Cajero</th>
                <th>Apertura</th>
                <th>Cierre</th>
                <th>Ventas</th>
                <th>Total</th>
                <th>Diferencia</th>
                <th>Estado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse($cierres as $c)
              <tr>
                <td>{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $c->user->name ?? '—' }}</td>
                <td>{{ $c->fecha_apertura?->format('d/m/Y H:i') }}</td>
                <td>{{ $c->fecha_cierre?->format('d/m/Y H:i') ?? '—' }}</td>
                <td>{{ $c->cantidad_ventas }}</td>
                <td>{{ $negocio['moneda'] }} {{ number_format($c->total_ventas, 2) }}</td>
                <td>
                  @if($c->diferencia === null)
                    —
                  @elseif($c->diferencia == 0)
                    <span class="badge bg-success">Cuadrada</span>
                  @elseif($c->diferencia > 0)
                    <span class="badge bg-info">+{{ number_format($c->diferencia, 2) }}</span>
                  @else
                    <span class="badge bg-danger">{{ number_format($c->diferencia, 2) }}</span>
                  @endif
                </td>
                <td>
                  @if($c->estado === 'abierto')
                    <span class="badge bg-warning text-dark">Abierto</span>
                  @else
                    <span class="badge bg-secondary">Cerrado</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('cierres.show', $c->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                  @if(auth()->user()->puedeImprimirReportes() && $c->estado === 'cerrado')
                    <a href="{{ route('cierres.pdf', $c->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></a>
                  @endif
                </td>
              </tr>
              @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Sin cierres registrados.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{ $cierres->links() }}
      </div>
    </div>
  </section>
</main>
@endsection
