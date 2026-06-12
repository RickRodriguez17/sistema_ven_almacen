<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cierre de caja #{{ $cierre->id }}</title>
<style>
  * { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
  h1, h2, h3 { margin: 0 0 6px 0; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ccc; padding: 4px 6px; }
  th { background: #f3f3f3; text-align: left; }
  .right { text-align: right; }
  .header { text-align: center; margin-bottom: 14px; }
  .header h1 { font-size: 16px; }
  .summary td { border: none; padding: 2px 6px; }
  .summary .label { color: #555; }
  .total-row { font-weight: bold; background: #fafafa; }
  hr { border: none; border-top: 1px solid #ddd; margin: 10px 0; }
</style>
</head>
<body>
<div class="header">
  <h1>{{ $negocio['nombre'] ?? 'Negocio' }}</h1>
  @if(!empty($negocio['direccion'])) <div>{{ $negocio['direccion'] }}</div> @endif
  @if(!empty($negocio['telefono'])) <div>Tel: {{ $negocio['telefono'] }}</div> @endif
  <h2>Cierre de caja #{{ str_pad($cierre->id, 5, '0', STR_PAD_LEFT) }}</h2>
</div>

<table class="summary">
  <tr><td class="label">Cajero:</td><td>{{ $cierre->user->name }}</td><td class="label">Apertura:</td><td>{{ $cierre->fecha_apertura->format('d/m/Y H:i') }}</td></tr>
  <tr><td class="label">Estado:</td><td>{{ ucfirst($cierre->estado) }}</td><td class="label">Cierre:</td><td>{{ $cierre->fecha_cierre?->format('d/m/Y H:i') ?? '—' }}</td></tr>
  <tr><td class="label">Fondo inicial:</td><td>{{ $negocio['moneda'] }} {{ number_format($cierre->fondo_inicial, 2) }}</td><td class="label">Cantidad ventas:</td><td>{{ $cierre->cantidad_ventas }}</td></tr>
  <tr><td class="label">Total ventas:</td><td><strong>{{ $negocio['moneda'] }} {{ number_format($cierre->total_ventas, 2) }}</strong></td><td class="label">Efectivo contado:</td><td>{{ $negocio['moneda'] }} {{ number_format($cierre->efectivo_contado ?? 0, 2) }}</td></tr>
  <tr><td class="label">Diferencia:</td><td colspan="3"><strong>{{ $negocio['moneda'] }} {{ number_format($cierre->diferencia ?? 0, 2) }}</strong></td></tr>
</table>

<hr>

<h3>Por método de pago</h3>
<table>
  <thead><tr><th>Método</th><th class="right">Total</th></tr></thead>
  <tbody>
    <tr><td>Efectivo</td><td class="right">{{ $negocio['moneda'] }} {{ number_format($cierre->total_efectivo, 2) }}</td></tr>
    <tr><td>Tarjeta</td><td class="right">{{ $negocio['moneda'] }} {{ number_format($cierre->total_tarjeta, 2) }}</td></tr>
    <tr><td>Yape</td><td class="right">{{ $negocio['moneda'] }} {{ number_format($cierre->total_yape, 2) }}</td></tr>
    <tr class="total-row"><td>TOTAL</td><td class="right">{{ $negocio['moneda'] }} {{ number_format($cierre->total_ventas, 2) }}</td></tr>
    @if(($cierre->total_gastos ?? 0) > 0)
      <tr><td>Gastos del turno</td><td class="right">− {{ $negocio['moneda'] }} {{ number_format($cierre->total_gastos, 2) }}</td></tr>
    @endif
  </tbody>
</table>

@if(isset($gastos) && $gastos->count())
<hr>
<h3>Gastos del turno</h3>
<table>
  <thead><tr><th>Hora</th><th>Concepto</th><th>Cajero</th><th class="right">Monto</th></tr></thead>
  <tbody>
    @foreach($gastos as $g)
      <tr>
        <td>{{ $g->created_at->format('H:i') }}</td>
        <td>{{ $g->concepto }}</td>
        <td>{{ $g->user?->name }}</td>
        <td class="right">− {{ $negocio['moneda'] }} {{ number_format($g->monto, 2) }}</td>
      </tr>
    @endforeach
    <tr class="total-row"><td colspan="3" class="right">Total gastos</td><td class="right">− {{ $negocio['moneda'] }} {{ number_format($cierre->total_gastos ?? $gastos->sum('monto'), 2) }}</td></tr>
  </tbody>
</table>
@endif

<hr>

<h3>Ventas del turno</h3>
<p style="font-size: 10px; color: #666; margin: 2px 0 6px 0;">
  Las ventas anuladas aparecen tachadas y NO se suman al total del cierre.
</p>
<table>
  <thead>
    <tr><th>Ticket</th><th>Hora</th><th>Estado</th><th>Método</th><th class="right">Items</th><th class="right">Total</th></tr>
  </thead>
  <tbody>
    @foreach($ventas as $v)
      @php $anul = $v->estado === \App\Models\Venta::ESTADO_ANULADA; @endphp
      <tr @if($anul) style="background:#fdecec;color:#888;" @endif>
        <td>{{ $v->numero_ticket }}</td>
        <td>{{ $v->created_at->format('H:i') }}</td>
        <td>{{ $anul ? 'ANULADA' : ucfirst($v->estado) }}</td>
        <td>{{ ucfirst($v->metodo_pago) }}</td>
        <td class="right">{{ $v->detalles->sum('cantidad') }}</td>
        <td class="right">
          @if($anul)<s>{{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}</s>
          @else{{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}@endif
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

@if(isset($anuladas) && $anuladas->count())
<hr>
<h3>Detalle de ventas anuladas ({{ $anuladas->count() }})</h3>
<p style="font-size: 10px; color: #666; margin: 2px 0 6px 0;">
  Monto total anulado: <strong>{{ $negocio['moneda'] }} {{ number_format($anuladas->sum('total_venta'), 2) }}</strong>
  — no incluido en el total del cierre.
</p>
<table>
  <thead>
    <tr>
      <th>Ticket</th>
      <th>Hora</th>
      <th>Anulada por</th>
      <th>Motivo</th>
      <th class="right">Monto</th>
    </tr>
  </thead>
  <tbody>
    @foreach($anuladas as $v)
      <tr>
        <td>{{ $v->numero_ticket }}</td>
        <td>{{ $v->created_at->format('H:i') }}</td>
        <td>
          {{ $v->anuladaPor?->name ?? $v->user?->name }}
          @if($v->anulada_at)<br><span style="color:#888;font-size:9px;">{{ $v->anulada_at->format('d/m H:i') }}</span>@endif
        </td>
        <td>{{ $v->motivo_anulacion ?? '—' }}</td>
        <td class="right"><s>{{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}</s></td>
      </tr>
    @endforeach
  </tbody>
</table>
@endif

@if($cierre->observaciones)
  <hr>
  <strong>Observaciones:</strong>
  <p>{{ $cierre->observaciones }}</p>
@endif

</body>
</html>
