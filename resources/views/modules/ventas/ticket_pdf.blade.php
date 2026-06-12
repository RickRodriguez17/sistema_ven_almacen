<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #000; margin: 0; padding: 4px; }
    .center { text-align: center; }
    .right { text-align: right; }
    .b { font-weight: bold; }
    hr { border: 0; border-top: 1px dashed #000; margin: 4px 0; }
    table { width: 100%; border-collapse: collapse; }
    table td { padding: 1px 0; vertical-align: top; }
  </style>
</head>
<body>
  <div class="center b">
    {{ $negocio['nombre'] }}<br>
    @if($negocio['direccion']){{ $negocio['direccion'] }}<br>@endif
    @if($negocio['telefono'])Tel: {{ $negocio['telefono'] }}<br>@endif
    @if(!empty($negocio['ruc']))RUC: {{ $negocio['ruc'] }}<br>@endif
  </div>

  <hr>
  <div>
    <b>Ticket:</b> {{ $venta->numero_ticket }}<br>
    <b>Fecha:</b> {{ $venta->created_at->format('d/m/Y H:i') }}<br>
    <b>Cajero:</b> {{ $venta->user?->name }}<br>
    <b>Tipo:</b> {{ $venta->tipoPedidoLabel() }}@if($venta->mesa) - Mesa {{ $venta->mesa }}@endif<br>
    @if($venta->direccion_delivery)<b>Dirección:</b> {{ $venta->direccion_delivery }}<br>@endif
    @if($venta->cliente)<b>Cliente:</b> {{ $venta->cliente->nombre }} {{ $venta->cliente->apellido }}<br>
    @elseif($venta->nombre_cliente_libre)<b>Cliente:</b> {{ $venta->nombre_cliente_libre }}<br>@endif
    @if($venta->notas)<b>Notas:</b> {{ $venta->notas }}<br>@endif
  </div>
  <hr>

  <table>
    <tr class="b"><td>Cant</td><td>Producto</td><td class="right">P.U.</td><td class="right">Subt</td></tr>
    @foreach($venta->detalles as $d)
      @php $nom = $d->combo_id ? '[C] '.$d->combo?->nombre : $d->producto?->nombre; @endphp
      <tr>
        <td>{{ $d->cantidad }}</td>
        <td>{{ Str::limit($nom ?? '—', 18) }}</td>
        <td class="right">{{ number_format($d->precio_unitario, 2) }}</td>
        <td class="right">{{ number_format($d->subtotal, 2) }}</td>
      </tr>
    @endforeach
  </table>

  <hr>

  <table>
    <tr class="b"><td>TOTAL</td><td class="right">{{ $negocio['moneda'] }} {{ number_format($venta->total_venta, 2) }}</td></tr>
    @if($venta->pagos->count() > 0)
      @foreach($venta->pagos as $pago)
        <tr><td>{{ ucfirst($pago->metodo_pago) }}</td><td class="right">{{ number_format($pago->monto, 2) }}</td></tr>
        @if($pago->metodo_pago === 'efectivo' && $pago->efectivo_recibido > 0)
          <tr><td>  Recibido</td><td class="right">{{ number_format($pago->efectivo_recibido, 2) }}</td></tr>
          @if($pago->cambio > 0)<tr><td>  Cambio</td><td class="right">{{ number_format($pago->cambio, 2) }}</td></tr>@endif
        @endif
      @endforeach
    @else
      <tr><td>Pago</td><td class="right">{{ ucfirst($venta->metodo_pago) }}</td></tr>
      @if($venta->metodo_pago === 'efectivo')
        <tr><td>Recibido</td><td class="right">{{ number_format($venta->efectivo_recibido, 2) }}</td></tr>
        <tr><td>Cambio</td><td class="right">{{ number_format($venta->cambio, 2) }}</td></tr>
      @endif
    @endif
  </table>

  <hr>
  <div class="center">{{ $negocio['mensaje_ticket'] }}</div>
</body>
</html>
