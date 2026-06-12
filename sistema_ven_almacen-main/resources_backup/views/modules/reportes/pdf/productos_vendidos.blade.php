@extends('modules.reportes.pdf._layout')
@section('content')
<div class="meta">Periodo: {{ $desde }} a {{ $hasta }}</div>
<table>
  <thead><tr><th>#</th><th>Producto</th><th>Categoría</th><th class="right">Unidades</th><th class="right">Ingreso</th></tr></thead>
  <tbody>
    @foreach($productos as $i => $p)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $p->nombre }}</td>
        <td>{{ $p->categoria ?? '—' }}</td>
        <td class="right">{{ $p->total_unidades }}</td>
        <td class="right">{{ $negocio['moneda'] }} {{ number_format($p->total_ingreso, 2) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
