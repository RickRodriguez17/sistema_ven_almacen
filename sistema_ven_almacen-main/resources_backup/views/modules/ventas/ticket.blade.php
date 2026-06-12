<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Ticket {{ $venta->numero_ticket }} (Cocina)</title>
  <style>
    @page { margin: 0; }
    body { font-family: 'Courier New', monospace; margin: 0; padding: 6px; color: #000; }
    .ticket { width: {{ $ancho == '58' ? '58mm' : '80mm' }}; margin: 0 auto; font-size: {{ $ancho == '58' ? '11px' : '12px' }}; }
    .center { text-align: center; }
    .b { font-weight: bold; }
    .tag {
      display: inline-block; border: 2px solid #000; padding: 4px 12px;
      font-weight: bold; letter-spacing: 1px; margin: 4px 0;
    }
    .qty-box {
      display: inline-block; border: 1px solid #000; padding: 1px 6px;
      font-weight: bold; min-width: 18px; text-align: center;
    }
    hr { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
    .item-cocina { font-size: 1.05em; padding: 4px 0; border-bottom: 1px dotted #999; }
    .item-cocina:last-child { border-bottom: 0; }
    .item-cocina .nota { font-size: 0.9em; padding-left: 24px; font-style: italic; }
    h2, h3 { margin: 2px 0; }
    .actions { margin: 12px auto; text-align: center; }
    @media print {
      .actions { display: none; }
      body { padding: 0; }
    }
  </style>
</head>
<body>

  <div class="ticket">
    <div class="center">
      <span class="tag">COCINA / DESPACHO</span>
    </div>

    <div class="center b">
      <h3>{{ $negocio['nombre'] }}</h3>
    </div>

    <hr>

    <div>
      <div><span class="b">Ticket:</span> {{ $venta->numero_ticket }}</div>
      <div><span class="b">Hora:</span> {{ $venta->created_at->format('d/m/Y H:i') }}</div>
      <div><span class="b">Cajero:</span> {{ $venta->user?->name }}</div>
      <div class="b" style="font-size: 1.15em;">{{ strtoupper($venta->tipoPedidoLabel()) }}@if($venta->mesa) - MESA {{ $venta->mesa }}@endif</div>
      @if($venta->direccion_delivery)<div><span class="b">Dirección:</span> {{ $venta->direccion_delivery }}</div>@endif
      @if($venta->cliente)<div><span class="b">Cliente:</span> {{ $venta->cliente->nombre }} {{ $venta->cliente->apellido }}</div>
      @elseif($venta->nombre_cliente_libre)<div><span class="b">Cliente:</span> {{ $venta->nombre_cliente_libre }}</div>@endif
      @if($venta->notas)<div class="b">>> {{ $venta->notas }}</div>@endif
    </div>

    <hr>

    <div class="b center" style="margin-bottom: 6px;">PEDIDO A PREPARAR</div>

    @foreach($venta->detalles as $d)
      @php
        $nom = $d->combo_id ? '[COMBO] '.$d->combo?->nombre : $d->producto?->nombre;
      @endphp
      <div class="item-cocina">
        <span class="qty-box">{{ $d->cantidad }}</span>
        <span class="b">{{ $nom ?? '—' }}</span>
        @if($d->combo_id && $d->combo && $d->combo->items->count() > 0)
          <div class="nota">
            @foreach($d->combo->items as $ci)
              · {{ $ci->cantidad * $d->cantidad }}× {{ $ci->producto?->nombre }}@if(! $loop->last)<br>@endif
            @endforeach
          </div>
        @endif
        @if($d->notas)<div class="nota">>> {{ $d->notas }}</div>@endif
      </div>
    @endforeach

    <hr>

    <div class="center">
      <span class="b">{{ $venta->detalles->sum('cantidad') }} ítem(s)</span>
    </div>

    <div class="actions">
      <button onclick="window.print()" style="padding:6px 12px;">Imprimir</button>
    </div>
  </div>

  <script>
    window.addEventListener('load', () => setTimeout(() => window.print(), 200));
  </script>
</body>
</html>
