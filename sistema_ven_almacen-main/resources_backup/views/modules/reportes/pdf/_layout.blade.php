<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $titulo }}</title>
<style>
  * { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
  h1, h2, h3 { margin: 0 0 4px 0; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ccc; padding: 4px 6px; }
  th { background: #f3f3f3; text-align: left; }
  .right { text-align: right; }
  .header { text-align: center; margin-bottom: 14px; }
  .header h1 { font-size: 16px; }
  .meta { color: #555; font-size: 10px; margin-bottom: 10px; }
  .total-row { font-weight: bold; background: #fafafa; }
</style>
</head>
<body>
<div class="header">
  <h1>{{ $negocio['nombre'] ?? 'Negocio' }}</h1>
  @if(!empty($negocio['direccion'])) <div>{{ $negocio['direccion'] }}</div> @endif
  @if(!empty($negocio['telefono'])) <div>Tel: {{ $negocio['telefono'] }}</div> @endif
  <h2>{{ $titulo }}</h2>
</div>
<div class="meta">Generado: {{ $generado->format('d/m/Y H:i') }}</div>

@yield('content')
</body>
</html>
