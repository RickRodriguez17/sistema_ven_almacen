@extends('modules.reportes.pdf._layout')
@section('content')
<div class="meta">Periodo: {{ $desde }} a {{ $hasta }} — {{ $totales['cantidad'] }} ventas — Total {{ $negocio['moneda'] }} {{ number_format($totales['total'], 2) }}</div>

<table>
  <thead><tr><th>Fecha</th><th>Ticket</th><th>Cajero</th><th>Método</th><th class="right">Total</th></tr></thead>
  <tbody>
    @foreach($ventas as $v)
      <tr>
        <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
        <td>{{ $v->numero_ticket }}</td>
        <td>{{ $v->user->name ?? '—' }}</td>
        <td>{{ ucfirst($v->metodo_pago) }}</td>
        <td class="right">{{ $negocio['moneda'] }} {{ number_format($v->total_venta, 2) }}</td>
      </tr>
    @endforeach
    <tr class="total-row"><td colspan="4">TOTAL</td><td class="right">{{ $negocio['moneda'] }} {{ number_format($totales['total'], 2) }}</td></tr>
  </tbody>
</table>
@endsection
