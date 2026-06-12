@extends('modules.reportes.pdf._layout')
@section('content')
<table>
  <thead><tr><th>Código</th><th>Producto</th><th>Categoría</th><th class="right">Stock</th><th class="right">Mínimo</th></tr></thead>
  <tbody>
    @foreach($productos as $p)
      <tr>
        <td>{{ $p->codigo }}</td>
        <td>{{ $p->nombre }}</td>
        <td>{{ $p->categoria?->nombre ?? '—' }}</td>
        <td class="right">{{ $p->cantidad }}</td>
        <td class="right">{{ $p->stock_minimo }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
