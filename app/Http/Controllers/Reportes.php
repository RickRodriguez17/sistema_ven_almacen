<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\CierreCaja;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Reportes extends Controller
{
    public function index()
    {
        return view('modules.reportes.index', [
            'titulo' => 'Reportes',
        ]);
    }

    public function ventasDiarias(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());
        $userId = $request->input('user_id');

        $query = Venta::with(['user', 'detalles.producto.categoria'])
            ->whereDate('created_at', $fecha);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $ventas = $query->orderBy('created_at')->get();

        $totales = $this->totalizarVentas($ventas);

        return view('modules.reportes.ventas_diarias', [
            'titulo' => 'Ventas diarias',
            'fecha' => $fecha,
            'ventas' => $ventas,
            'totales' => $totales,
            'usuarios' => User::orderBy('name')->get(),
            'userIdSeleccionado' => $userId,
        ]);
    }

    public function ventasRango(Request $request)
    {
        $desde = $request->input('desde', now()->subDays(7)->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());
        $userId = $request->input('user_id');

        $query = Venta::with(['user', 'detalles.producto'])
            ->whereBetween('created_at', [
                $desde.' 00:00:00',
                $hasta.' 23:59:59',
            ]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $ventas = $query->orderBy('created_at')->get();
        $totales = $this->totalizarVentas($ventas);

        $porDia = $ventas->groupBy(fn ($v) => $v->created_at->toDateString())
            ->map(fn ($g) => [
                'fecha' => $g->first()->created_at->toDateString(),
                'cantidad' => $g->count(),
                'total' => round($g->sum('total_venta'), 2),
            ])
            ->values();

        return view('modules.reportes.ventas_rango', [
            'titulo' => 'Ventas por rango',
            'desde' => $desde,
            'hasta' => $hasta,
            'ventas' => $ventas,
            'totales' => $totales,
            'porDia' => $porDia,
            'usuarios' => User::orderBy('name')->get(),
            'userIdSeleccionado' => $userId,
        ]);
    }

    public function productosVendidos(Request $request)
    {
        $desde = $request->input('desde', now()->subDays(30)->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());
        $categoriaId = $request->input('categoria_id');

        $query = DB::table('detalle_venta')
            ->join('productos', 'productos.id', '=', 'detalle_venta.producto_id')
            ->join('ventas', 'ventas.id', '=', 'detalle_venta.venta_id')
            ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->whereBetween('ventas.created_at', [
                $desde.' 00:00:00',
                $hasta.' 23:59:59',
            ])
            ->select(
                'productos.id',
                'productos.nombre',
                'categorias.nombre as categoria',
                DB::raw('SUM(detalle_venta.cantidad) as total_unidades'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ingreso')
            )
            ->groupBy('productos.id', 'productos.nombre', 'categorias.nombre')
            ->orderByDesc('total_unidades');

        if ($categoriaId) {
            $query->where('productos.categoria_id', $categoriaId);
        }

        $productos = $query->get();

        return view('modules.reportes.productos_vendidos', [
            'titulo' => 'Productos vendidos',
            'desde' => $desde,
            'hasta' => $hasta,
            'productos' => $productos,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'categoriaIdSeleccionada' => $categoriaId,
        ]);
    }

    public function stockBajo()
    {
        $productos = Producto::with('categoria')
            ->whereColumn('cantidad', '<=', 'stock_minimo')
            ->where('activo', true)
            ->orderBy('cantidad')
            ->get();

        return view('modules.reportes.stock_bajo', [
            'titulo' => 'Productos con stock bajo',
            'productos' => $productos,
        ]);
    }

    public function ingresosDia(Request $request)
    {
        $desde = $request->input('desde', now()->subDays(30)->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());

        $rows = Venta::selectRaw('date(created_at) as fecha, COUNT(*) as cantidad, SUM(total_venta) as total')
            ->whereBetween('created_at', [
                $desde.' 00:00:00',
                $hasta.' 23:59:59',
            ])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return view('modules.reportes.ingresos_dia', [
            'titulo' => 'Ingresos por día',
            'desde' => $desde,
            'hasta' => $hasta,
            'rows' => $rows,
        ]);
    }

    public function cierres(Request $request)
    {
        $desde = $request->input('desde', now()->subDays(30)->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());
        $userId = $request->input('user_id');

        $query = CierreCaja::with('user')
            ->whereBetween('fecha_apertura', [
                $desde.' 00:00:00',
                $hasta.' 23:59:59',
            ])
            ->orderByDesc('fecha_apertura');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $cierres = $query->get();

        return view('modules.reportes.cierres', [
            'titulo' => 'Historial de cierres',
            'desde' => $desde,
            'hasta' => $hasta,
            'cierres' => $cierres,
            'usuarios' => User::orderBy('name')->get(),
            'userIdSeleccionado' => $userId,
        ]);
    }

    public function pdf(Request $request, string $tipo)
    {
        $user = Auth::user();
        if (! $user->puedeImprimirReportes()) {
            abort(403);
        }

        $negocio = config('negocio');
        if ($empresa = Empresa::actual()) {
            $negocio = array_merge($negocio, $empresa->toNegocio());
        }

        $data = ['negocio' => $negocio, 'generado' => now()];

        switch ($tipo) {
            case 'ventas-diarias':
                $fecha = $request->input('fecha', now()->toDateString());
                $ventas = Venta::with(['user', 'detalles.producto'])
                    ->whereDate('created_at', $fecha)
                    ->orderBy('created_at')
                    ->get();
                $data += [
                    'titulo' => 'Ventas diarias - '.$fecha,
                    'ventas' => $ventas,
                    'totales' => $this->totalizarVentas($ventas),
                ];
                $view = 'modules.reportes.pdf.ventas_diarias';
                break;

            case 'ventas-rango':
                $desde = $request->input('desde', now()->subDays(7)->toDateString());
                $hasta = $request->input('hasta', now()->toDateString());
                $ventas = Venta::with(['user', 'detalles.producto'])
                    ->whereBetween('created_at', [
                        $desde.' 00:00:00',
                        $hasta.' 23:59:59',
                    ])
                    ->orderBy('created_at')
                    ->get();
                $data += [
                    'titulo' => "Ventas $desde a $hasta",
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'ventas' => $ventas,
                    'totales' => $this->totalizarVentas($ventas),
                ];
                $view = 'modules.reportes.pdf.ventas_rango';
                break;

            case 'productos-vendidos':
                $desde = $request->input('desde', now()->subDays(30)->toDateString());
                $hasta = $request->input('hasta', now()->toDateString());
                $productos = DB::table('detalle_venta')
                    ->join('productos', 'productos.id', '=', 'detalle_venta.producto_id')
                    ->join('ventas', 'ventas.id', '=', 'detalle_venta.venta_id')
                    ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
                    ->whereBetween('ventas.created_at', [
                        $desde.' 00:00:00',
                        $hasta.' 23:59:59',
                    ])
                    ->select(
                        'productos.nombre',
                        'categorias.nombre as categoria',
                        DB::raw('SUM(detalle_venta.cantidad) as total_unidades'),
                        DB::raw('SUM(detalle_venta.subtotal) as total_ingreso')
                    )
                    ->groupBy('productos.id', 'productos.nombre', 'categorias.nombre')
                    ->orderByDesc('total_unidades')
                    ->get();
                $data += [
                    'titulo' => "Productos vendidos $desde a $hasta",
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'productos' => $productos,
                ];
                $view = 'modules.reportes.pdf.productos_vendidos';
                break;

            case 'stock-bajo':
                $productos = Producto::with('categoria')
                    ->whereColumn('cantidad', '<=', 'stock_minimo')
                    ->where('activo', true)
                    ->orderBy('cantidad')
                    ->get();
                $data += [
                    'titulo' => 'Productos con stock bajo',
                    'productos' => $productos,
                ];
                $view = 'modules.reportes.pdf.stock_bajo';
                break;

            case 'inventario':
                $productos = Producto::with('categoria')
                    ->orderBy('nombre')
                    ->get();
                $data += [
                    'titulo' => 'Inventario completo',
                    'productos' => $productos,
                ];
                $view = 'modules.reportes.pdf.inventario';
                break;

            default:
                abort(404);
        }

        $pdf = Pdf::loadView($view, $data);

        return $pdf->stream("reporte-{$tipo}.pdf");
    }

    protected function totalizarVentas($ventas): array
    {
        $totales = [
            'cantidad' => $ventas->count(),
            'total' => round($ventas->sum('total_venta'), 2),
            'efectivo' => 0,
            'tarjeta' => 0,
            'transferencia' => 0,
            'yape' => 0,
            'plin' => 0,
            'otros' => 0,
        ];

        foreach ($ventas as $v) {
            $m = $v->metodo_pago;
            if (array_key_exists($m, $totales)) {
                $totales[$m] += $v->total_venta;
            } else {
                $totales['otros'] += $v->total_venta;
            }
        }

        foreach ($totales as $k => $val) {
            if ($k !== 'cantidad') {
                $totales[$k] = round($val, 2);
            }
        }

        return $totales;
    }
}
