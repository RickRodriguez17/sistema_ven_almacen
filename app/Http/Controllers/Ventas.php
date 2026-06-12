<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\CierreCaja;
use App\Models\Cliente;
use App\Models\Combo;
use App\Models\DetalleVenta;
use App\Models\Empresa;
use App\Models\MovimientoInventario;
use App\Models\PagoVenta;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\InventarioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Ventas extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Punto de Venta';

        $turnoAbierto = CierreCaja::abiertoDe(Auth::id());

        $categorias = Categoria::orderBy('nombre')->get();

        $productos = Producto::with(['categoria', 'imagen'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $combos = Combo::with(['items.producto'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $clientes = Cliente::orderBy('nombre')->get();

        $pendientes = Venta::pendientes()
            ->with('user')
            ->where('user_id', Auth::id())
            ->latest()
            ->limit(20)
            ->get();

        return view('modules.ventas.index', compact('titulo', 'productos', 'combos', 'categorias', 'clientes', 'pendientes', 'turnoAbierto'));
    }

    public function store(Request $request, InventarioService $inventario)
    {
        $data = $this->validarVenta($request);

        $turno = $this->requerirTurnoAbierto();
        if (! $turno instanceof CierreCaja) {
            return $turno;
        }

        try {
            $venta = DB::transaction(function () use ($data, $inventario, $turno) {
                [$venta, $total] = $this->crearVentaConItems($data, $inventario, $turno);

                $estado = $data['pendiente'] ?? false ? Venta::ESTADO_PENDIENTE : Venta::ESTADO_PAGADA;
                $venta->estado = $estado;

                if ($estado === Venta::ESTADO_PAGADA) {
                    $this->registrarPagos($venta, $data['pagos'] ?? null, $total, $data['metodo_pago'] ?? 'efectivo', $data['efectivo_recibido'] ?? null);
                }

                $venta->save();

                return $venta;
            });

            return response()->json([
                'ok' => true,
                'venta_id' => $venta->id,
                'numero_ticket' => $venta->numero_ticket,
                'estado' => $venta->estado,
                'total' => $venta->total_venta,
                'cambio' => $venta->cambio,
                'ticket_url' => $venta->estado === Venta::ESTADO_PAGADA ? route('ventas.ticket', $venta->id) : null,
                'pdf_url' => $venta->estado === Venta::ESTADO_PAGADA ? route('ventas.ticket.pdf', $venta->id) : null,
                'redirect' => $venta->estado === Venta::ESTADO_PENDIENTE ? route('ventas-nueva') : null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Devuelve el CierreCaja abierto del usuario actual o una respuesta JSON 422
     * si no hay turno abierto. Toda venta debe registrarse contra un turno.
     */
    protected function requerirTurnoAbierto()
    {
        $turno = CierreCaja::abiertoDe(Auth::id());

        if (! $turno) {
            return response()->json([
                'ok' => false,
                'message' => 'No tienes un turno de caja abierto. Inicia tu turno antes de registrar ventas.',
                'redirect' => route('cierres.iniciar.form'),
            ], 422);
        }

        return $turno;
    }

    public function pendientes()
    {
        $titulo = 'Pedidos pendientes';
        $user = Auth::user();
        $query = Venta::pendientes()->with(['user', 'cliente', 'detalles']);
        if (! $user->esAdmin()) {
            $query->where('user_id', $user->id);
        }
        $pendientes = $query->latest()->paginate(20);

        return view('modules.ventas.pendientes', compact('titulo', 'pendientes'));
    }

    public function agregarItems(Request $request, int $id, InventarioService $inventario)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['nullable', 'exists:productos,id'],
            'items.*.combo_id' => ['nullable', 'exists:combos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.notas' => ['nullable', 'string', 'max:200'],
        ]);

        $user = Auth::user();

        $turno = $this->requerirTurnoAbierto();
        if (! $turno instanceof CierreCaja) {
            return $turno;
        }

        try {
            $venta = DB::transaction(function () use ($data, $id, $inventario, $user) {
                $venta = Venta::pendientes()->lockForUpdate()->findOrFail($id);
                if (! $user->esAdmin() && $venta->user_id !== $user->id) {
                    abort(403, 'No puedes modificar pedidos pendientes de otro cajero.');
                }
                $totalNuevo = $this->procesarItems($venta, $data['items'], $inventario);
                $venta->total_venta = round($venta->total_venta + $totalNuevo, 2);
                $venta->save();

                return $venta;
            });

            return response()->json([
                'ok' => true,
                'venta_id' => $venta->id,
                'total' => $venta->total_venta,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cobrar(Request $request, int $id)
    {
        $data = $request->validate([
            'pagos' => ['required', 'array', 'min:1'],
            'pagos.*.metodo_pago' => ['required', Rule::in(PagoVenta::METODOS)],
            'pagos.*.monto' => ['required', 'numeric', 'min:0.01'],
            'pagos.*.efectivo_recibido' => ['nullable', 'numeric', 'min:0'],
            'pagos.*.referencia' => ['nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();

        $turno = $this->requerirTurnoAbierto();
        if (! $turno instanceof CierreCaja) {
            return $turno;
        }

        try {
            $venta = DB::transaction(function () use ($data, $id, $user, $turno) {
                $venta = Venta::pendientes()->lockForUpdate()->findOrFail($id);
                if (! $user->esAdmin() && $venta->user_id !== $user->id) {
                    abort(403, 'No puedes cobrar pedidos pendientes de otro cajero.');
                }
                $this->registrarPagos($venta, $data['pagos'], (float) $venta->total_venta);
                $venta->estado = Venta::ESTADO_PAGADA;
                // El cobro siempre se registra al turno actual del cajero (en caso
                // de que el pendiente se haya creado en otro turno).
                $venta->cierre_caja_id = $turno->id;
                $venta->save();

                return $venta;
            });

            return response()->json([
                'ok' => true,
                'venta_id' => $venta->id,
                'numero_ticket' => $venta->numero_ticket,
                'ticket_url' => route('ventas.ticket', $venta->id),
                'pdf_url' => route('ventas.ticket.pdf', $venta->id),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function anular(Request $request, int $id, InventarioService $inventario)
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        if (! $user->esAdmin() && ! $user->esCajero()) {
            abort(403);
        }

        // Cualquier admin o cajero puede anular cualquier venta del local.
        // A veces la venta queda registrada con un usuario distinto (turnos
        // compartidos, error de captura) y la cajera de turno necesita poder
        // corregirla en el momento.

        try {
            $venta = DB::transaction(function () use ($data, $id, $inventario) {
                $venta = Venta::with('detalles.combo.items', 'detalles.producto')
                    ->lockForUpdate()
                    ->findOrFail($id);

                if ($venta->estaAnulada()) {
                    throw new \RuntimeException('La venta ya está anulada.');
                }

                foreach ($venta->detalles as $det) {
                    if ($det->combo_id && $det->combo) {
                        foreach ($det->combo->items as $ci) {
                            $producto = Producto::lockForUpdate()->find($ci->producto_id);
                            if ($producto) {
                                $inventario->registrarMovimiento(
                                    producto: $producto,
                                    tipo: MovimientoInventario::TIPO_ENTRADA,
                                    cantidad: $ci->cantidad * ($det->cantidad_combos ?? $det->cantidad),
                                    motivo: "Anulación venta #{$venta->numero_ticket}",
                                    referenciaTipo: 'venta_anulada',
                                    referenciaId: $venta->id,
                                );
                            }
                        }
                    } elseif ($det->producto_id && $det->producto) {
                        $producto = Producto::lockForUpdate()->find($det->producto_id);
                        if ($producto) {
                            $inventario->registrarMovimiento(
                                producto: $producto,
                                tipo: MovimientoInventario::TIPO_ENTRADA,
                                cantidad: $det->cantidad,
                                motivo: "Anulación venta #{$venta->numero_ticket}",
                                referenciaTipo: 'venta_anulada',
                                referenciaId: $venta->id,
                            );
                        }
                    }
                }

                $venta->estado = Venta::ESTADO_ANULADA;
                $venta->motivo_anulacion = $data['motivo'];
                $venta->anulada_at = now();
                $venta->anulada_por_user_id = Auth::id();
                $venta->save();

                return $venta;
            });

            return back()->with('success', 'Venta anulada y stock restituido.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function ticket($id)
    {
        $venta = Venta::with(['detalles.producto', 'detalles.combo', 'cliente', 'user', 'pagos'])->findOrFail($id);
        $negocio = $this->datosNegocio();
        $ancho = request('ancho', '80');

        return view('modules.ventas.ticket', compact('venta', 'negocio', 'ancho'));
    }

    public function ticketDoble($id)
    {
        // Compatibilidad: el nombre 'doble' es histórico (antes imprimía 2 copias).
        // Hoy es el mismo ticket de cocina/despacho que ticket(). Lo mantenemos
        // para no romper URLs viejas o vistas que aún apunten a esta ruta.
        return $this->ticket($id);
    }

    public function ticketPdf($id)
    {
        $venta = Venta::with(['detalles.producto', 'detalles.combo', 'cliente', 'user', 'pagos'])->findOrFail($id);
        $negocio = $this->datosNegocio();
        $ancho = request('ancho', '80');

        $pdf = Pdf::loadView('modules.ventas.ticket_pdf', compact('venta', 'negocio', 'ancho'));
        $width = $ancho == '58' ? 164 : 226;
        $pdf->setPaper([0, 0, $width, 800]);

        return $pdf->stream("ticket-{$venta->numero_ticket}.pdf");
    }

    protected function validarVenta(Request $request): array
    {
        return $request->validate([
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'nombre_cliente_libre' => ['nullable', 'string', 'max:120'],
            'tipo_pedido' => ['required', Rule::in(array_keys(Venta::TIPOS_PEDIDO))],
            'mesa' => ['nullable', 'string', 'max:30'],
            'direccion_delivery' => ['nullable', 'string', 'max:500'],
            'notas' => ['nullable', 'string', 'max:500'],
            'pendiente' => ['nullable', 'boolean'],
            'metodo_pago' => ['nullable', Rule::in(PagoVenta::METODOS)],
            'efectivo_recibido' => ['nullable', 'numeric', 'min:0'],
            'pagos' => ['nullable', 'array'],
            'pagos.*.metodo_pago' => ['required_with:pagos', Rule::in(PagoVenta::METODOS)],
            'pagos.*.monto' => ['required_with:pagos', 'numeric', 'min:0.01'],
            'pagos.*.efectivo_recibido' => ['nullable', 'numeric', 'min:0'],
            'pagos.*.referencia' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['nullable', 'exists:productos,id'],
            'items.*.combo_id' => ['nullable', 'exists:combos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.notas' => ['nullable', 'string', 'max:200'],
        ]);
    }

    protected function crearVentaConItems(array $data, InventarioService $inventario, ?CierreCaja $turno = null): array
    {
        $venta = Venta::create([
            'user_id' => Auth::id(),
            'cierre_caja_id' => $turno?->id,
            'cliente_id' => $data['cliente_id'] ?? null,
            'numero_ticket' => $this->generarNumeroTicket(),
            'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
            'efectivo_recibido' => 0,
            'cambio' => 0,
            'total_venta' => 0,
            'tipo_pedido' => $data['tipo_pedido'],
            'mesa' => $data['mesa'] ?? null,
            'direccion_delivery' => $data['direccion_delivery'] ?? null,
            'estado' => Venta::ESTADO_PENDIENTE,
            'notas' => $data['notas'] ?? null,
            'nombre_cliente_libre' => $data['nombre_cliente_libre'] ?? null,
        ]);

        $total = $this->procesarItems($venta, $data['items'], $inventario);
        $venta->total_venta = $total;
        $venta->save();

        return [$venta, $total];
    }

    protected function procesarItems(Venta $venta, array $items, InventarioService $inventario): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            if (! empty($item['combo_id'])) {
                $combo = Combo::with('items.producto')->where('activo', true)->lockForUpdate()->findOrFail($item['combo_id']);
                $cantCombos = (int) $item['cantidad'];
                $subtotal = round($combo->precio * $cantCombos, 2);

                foreach ($combo->items as $ci) {
                    $producto = Producto::lockForUpdate()->findOrFail($ci->producto_id);
                    $aDescontar = $ci->cantidad * $cantCombos;
                    if ($producto->cantidad < $aDescontar) {
                        throw new \RuntimeException("Stock insuficiente para {$producto->nombre} (combo {$combo->nombre}). Disponible: {$producto->cantidad}");
                    }
                    $inventario->registrarMovimiento(
                        producto: $producto,
                        tipo: MovimientoInventario::TIPO_SALIDA,
                        cantidad: $aDescontar,
                        motivo: "Combo {$combo->nombre} (Venta #{$venta->numero_ticket})",
                        referenciaTipo: 'venta',
                        referenciaId: $venta->id,
                    );
                }

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'combo_id' => $combo->id,
                    'cantidad_combos' => $cantCombos,
                    'cantidad' => $cantCombos,
                    'precio_unitario' => $combo->precio,
                    'subtotal' => $subtotal,
                    'notas' => $item['notas'] ?? null,
                ]);

                $total += $subtotal;
            } elseif (! empty($item['producto_id'])) {
                $producto = Producto::lockForUpdate()->findOrFail($item['producto_id']);
                if ($producto->cantidad < $item['cantidad']) {
                    throw new \RuntimeException("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->cantidad}");
                }
                $subtotal = round($producto->precio_venta * $item['cantidad'], 2);

                $inventario->registrarMovimiento(
                    producto: $producto,
                    tipo: MovimientoInventario::TIPO_SALIDA,
                    cantidad: (int) $item['cantidad'],
                    motivo: "Venta #{$venta->numero_ticket}",
                    referenciaTipo: 'venta',
                    referenciaId: $venta->id,
                );

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio_venta,
                    'subtotal' => $subtotal,
                    'notas' => $item['notas'] ?? null,
                ]);

                $total += $subtotal;
            } else {
                throw new \RuntimeException('Cada ítem debe tener producto_id o combo_id.');
            }
        }

        return round($total, 2);
    }

    protected function registrarPagos(Venta $venta, ?array $pagos, float $total, string $metodoFallback = 'efectivo', ?float $efectivoRecibidoFallback = null): void
    {
        // Si no se enviaron pagos, construir uno con el método/efectivo del fallback.
        if (empty($pagos)) {
            $pagos = [[
                'metodo_pago' => $metodoFallback,
                'monto' => $total,
                'efectivo_recibido' => $metodoFallback === 'efectivo'
                    ? (float) ($efectivoRecibidoFallback ?? 0)
                    : 0,
                'referencia' => null,
            ]];
        }

        // 1) Validar suma exacta y reglas por método ANTES de crear nada.
        $sumaPagos = 0.0;
        foreach ($pagos as $i => $pago) {
            $metodo = $pago['metodo_pago'];
            $monto = round((float) $pago['monto'], 2);
            $efectivoRecibido = round((float) ($pago['efectivo_recibido'] ?? 0), 2);

            if ($monto <= 0) {
                throw new \RuntimeException("El monto del pago #{$i} debe ser mayor a 0.");
            }

            if ($metodo === 'efectivo') {
                if ($efectivoRecibido < $monto - 0.001) {
                    throw new \RuntimeException(sprintf(
                        'Efectivo recibido (Bs %.2f) es menor al monto del pago en efectivo (Bs %.2f).',
                        $efectivoRecibido,
                        $monto
                    ));
                }
            }

            $sumaPagos += $monto;
        }

        $totalRedondeado = round($total, 2);
        $sumaRedondeada = round($sumaPagos, 2);

        if ($sumaRedondeada < $totalRedondeado - 0.01) {
            throw new \RuntimeException(sprintf(
                'Los pagos (Bs %.2f) no cubren el total (Bs %.2f). Falta Bs %.2f.',
                $sumaRedondeada,
                $totalRedondeado,
                $totalRedondeado - $sumaRedondeada
            ));
        }

        // 2) Crear los registros de pago.
        $totalCambio = 0.0;
        foreach ($pagos as $pago) {
            $metodo = $pago['metodo_pago'];
            $monto = round((float) $pago['monto'], 2);
            $efectivoRecibido = $metodo === 'efectivo'
                ? round((float) ($pago['efectivo_recibido'] ?? 0), 2)
                : 0;
            $cambio = $metodo === 'efectivo' && $efectivoRecibido > $monto
                ? round($efectivoRecibido - $monto, 2)
                : 0;
            $totalCambio += $cambio;

            PagoVenta::create([
                'venta_id' => $venta->id,
                'metodo_pago' => $metodo,
                'monto' => $monto,
                'efectivo_recibido' => $efectivoRecibido,
                'cambio' => $cambio,
                'referencia' => $metodo === 'efectivo' ? null : ($pago['referencia'] ?? null),
            ]);
        }

        $venta->metodo_pago = count($pagos) === 1 ? $pagos[0]['metodo_pago'] : 'mixto';
        $venta->efectivo_recibido = (float) collect($pagos)
            ->where('metodo_pago', 'efectivo')
            ->sum(fn ($p) => (float) ($p['efectivo_recibido'] ?? 0));
        $venta->cambio = round($totalCambio, 2);
    }

    public function ventaLibre()
    {
        $titulo = 'Venta Libre';
        $turnoAbierto = CierreCaja::abiertoDe(Auth::id());
        $clientes = Cliente::orderBy('nombre')->get();

        return view('modules.ventas.venta_libre', compact('titulo', 'turnoAbierto', 'clientes'));
    }

    public function storeVentaLibre(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'nombre_cliente_libre' => ['nullable', 'string', 'max:120'],
            'notas' => ['nullable', 'string', 'max:500'],
            'metodo_pago' => ['nullable', Rule::in(PagoVenta::METODOS)],
            'efectivo_recibido' => ['nullable', 'numeric', 'min:0'],
            'pagos' => ['nullable', 'array'],
            'pagos.*.metodo_pago' => ['required_with:pagos', Rule::in(PagoVenta::METODOS)],
            'pagos.*.monto' => ['required_with:pagos', 'numeric', 'min:0.01'],
            'pagos.*.efectivo_recibido' => ['nullable', 'numeric', 'min:0'],
            'pagos.*.referencia' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nombre' => ['required', 'string', 'max:200'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0.01'],
        ]);

        $turno = $this->requerirTurnoAbierto();
        if (! $turno instanceof CierreCaja) {
            return $turno;
        }

        try {
            $venta = DB::transaction(function () use ($data, $turno) {
                $total = 0.0;

                $venta = Venta::create([
                    'user_id' => Auth::id(),
                    'cierre_caja_id' => $turno->id,
                    'cliente_id' => $data['cliente_id'] ?? null,
                    'numero_ticket' => $this->generarNumeroTicket(),
                    'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
                    'efectivo_recibido' => 0,
                    'cambio' => 0,
                    'total_venta' => 0,
                    'tipo_pedido' => Venta::TIPO_LLEVAR,
                    'estado' => Venta::ESTADO_PAGADA,
                    'notas' => $data['notas'] ?? null,
                    'nombre_cliente_libre' => $data['nombre_cliente_libre'] ?? null,
                ]);

                foreach ($data['items'] as $item) {
                    $subtotal = round((float) $item['precio_unitario'] * (int) $item['cantidad'], 2);
                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'cantidad' => (int) $item['cantidad'],
                        'precio_unitario' => (float) $item['precio_unitario'],
                        'subtotal' => $subtotal,
                        'nombre_libre' => $item['nombre'],
                    ]);
                    $total += $subtotal;
                }

                $venta->total_venta = round($total, 2);

                $this->registrarPagos(
                    $venta,
                    $data['pagos'] ?? null,
                    $venta->total_venta,
                    $data['metodo_pago'] ?? 'efectivo',
                    $data['efectivo_recibido'] ?? null,
                );

                $venta->save();

                return $venta;
            });

            return response()->json([
                'ok' => true,
                'venta_id' => $venta->id,
                'numero_ticket' => $venta->numero_ticket,
                'total' => $venta->total_venta,
                'cambio' => $venta->cambio,
                'ticket_url' => route('ventas.ticket', $venta->id),
                'pdf_url' => route('ventas.ticket.pdf', $venta->id),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    protected function generarNumeroTicket(): string
    {
        $fecha = now()->format('Ymd');
        $ultimo = Venta::whereDate('created_at', now())->count() + 1;

        return $fecha.'-'.str_pad($ultimo, 4, '0', STR_PAD_LEFT);
    }

    protected function datosNegocio(): array
    {
        $base = config('negocio');
        $empresa = Empresa::actual();

        return $empresa ? array_merge($base, $empresa->toNegocio()) : $base;
    }
}
