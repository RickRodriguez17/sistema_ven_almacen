@extends('modules.reportes.pdf._layout')
@section('content')
<table>
  <thead>
    <tr><th>Ticket</th><th>Hora</th><th>Cajero</th><th>Método</th><th class="right">Items</th><th class="right">Total</th></tr>
  </thead>
  <tbody>
    @foreach($ventas as $v)
      <tr>
        <td>{{ $v->numero_ticket }}</td>
        <td>{{ $v->created_at->format('H:i') }}</td>
        <td>{{ $v->user->name ?? '—' }}</td>
        <td>{{ ucfirst($v->metodo_pago) }}</td>
        <td class="right">{{ $v->detalles->sum('cantidad') }}</td>
        <td class="right">{{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}</td>
      </tr>
    @endforeach
    <tr class="total-row">
      <td colspan="5">TOTAL ({{ $totales['cantidad'] }} ventas)</td>
      <td class="right">{{ $negocio['moneda'] }} {{ number_format($totales['total'], 2) }}</td>
    </tr>
  </tbody>
</table>

<h3 style="margin-top:14px;">Por método de pago</h3>
<table>
  <tbody>
    <tr><td>Efectivo</td><td class="right">{{ $negocio['moneda'] }} {{ number_format($totales['efectivo'], 2) }}</td></tr>
    <tr><td>Tarjeta</td><td class="right">{{ $negocio['moneda'] }} {{ number_format($totales['tarjeta'], 2) }}</td></tr>
    <tr><td>Yape</td><td class="right">{{ $negocio['moneda'] }} {{ number_format($totales['yape'], 2) }}</td></tr>
  </tbody>
</table>
@endsection
