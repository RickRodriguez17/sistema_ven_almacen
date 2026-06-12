@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <div><h1>{{ $titulo }}</h1></div>
    <div>
      <a href="{{ route('ventas.ticket', $venta->id) }}?ancho=80" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Imprimir</a>
      <a href="{{ route('ventas.ticket.pdf', $venta->id) }}?ancho=80" target="_blank" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
    </div>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Información</h5>
            <ul class="list-unstyled small">
              <li><strong>Ticket:</strong> {{ $venta->numero_ticket }}</li>
              <li><strong>Fecha:</strong> {{ $venta->created_at->format('d/m/Y H:i') }}</li>
              <li><strong>Cajero:</strong> {{ $venta->user?->name }}</li>
              <li><strong>Cliente:</strong> {{ $venta->cliente ? $venta->cliente->nombre . ' ' . $venta->cliente->apellido : 'Consumidor final' }}</li>
              <li><strong>Método de pago:</strong> {{ ucfirst($venta->metodo_pago) }}</li>
              @if($venta->metodo_pago === 'efectivo')
                <li><strong>Recibido:</strong> {{ $negocio['moneda'] }} {{ number_format($venta->efectivo_recibido, 2) }}</li>
                <li><strong>Cambio:</strong> {{ $negocio['moneda'] }} {{ number_format($venta->cambio, 2) }}</li>
              @endif
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Detalle de productos</h5>
            <table class="table table-sm">
              <thead><tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">P. Unit.</th><th class="text-end">Subtotal</th></tr></thead>
              <tbody>
                @foreach($venta->detalles as $d)
                  <tr>
                    <td>{{ $d->combo_id ? '[Combo] '.($d->combo?->nombre ?? '—') : ($d->producto?->nombre ?? ($d->nombre_libre ? '[Libre] '.$d->nombre_libre : '—')) }}</td>
                    <td class="text-center">{{ $d->cantidad }}</td>
                    <td class="text-end">{{ number_format($d->precio_unitario, 2) }}</td>
                    <td class="text-end">{{ number_format($d->subtotal, 2) }}</td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot><tr><th colspan="3" class="text-end">TOTAL</th><th class="text-end">{{ $negocio['moneda'] }} {{ number_format($venta->total_venta, 2) }}</th></tr></tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
