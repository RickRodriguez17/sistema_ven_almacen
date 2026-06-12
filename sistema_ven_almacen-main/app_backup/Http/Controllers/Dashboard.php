<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Dashboard extends Controller
{
    public function index()
    {
        $titulo = 'Dashboard';
        $user = Auth::user();

        $ventasHoy = Venta::whereDate('created_at', today())->sum('total_venta');
        $cantidadVentasHoy = Venta::whereDate('created_at', today())->count();
        $ventasMes = Venta::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_venta');
        $totalProductos = Producto::where('activo', true)->count();
        $productosVendidosHoy = (int) DB::table('detalle_venta')
            ->join('ventas', 'ventas.id', '=', 'detalle_venta.venta_id')
            ->whereDate('ventas.created_at', today())
            ->sum('detalle_venta.cantidad');

        $stockBajo = Producto::with('categoria')
            ->whereColumn('cantidad', '<=', 'stock_minimo')
            ->where('activo', true)
            ->orderBy('cantidad')
            ->limit(8)
            ->get();

        $topProductos = DB::table('detalle_venta')
            ->join('productos', 'productos.id', '=', 'detalle_venta.producto_id')
            ->select('productos.nombre', DB::raw('SUM(detalle_venta.cantidad) as total_vendido'), DB::raw('SUM(detalle_venta.subtotal) as total_ingreso'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        $ventasUltimos7Dias = Venta::selectRaw('date(created_at) as fecha, SUM(total_venta) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $cierreAbierto = CierreCaja::abiertoDe($user->id);

        return view('modules.dashboard.home', compact(
            'titulo',
            'ventasHoy',
            'cantidadVentasHoy',
            'ventasMes',
            'totalProductos',
            'productosVendidosHoy',
            'stockBajo',
            'topProductos',
            'ventasUltimos7Dias',
            'cierreAbierto'
        ));
    }
}
