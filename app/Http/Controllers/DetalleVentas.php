<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class DetalleVentas extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Historial de Ventas';

        $query = Venta::with(['user', 'cliente', 'detalles'])->latest();

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $ventas = $query->paginate(20)->withQueryString();

        $totalQuery = Venta::query()
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado));

        $totales = [
            'cantidad' => $ventas->total(),
            'monto' => (float) $totalQuery->where('estado', '!=', Venta::ESTADO_ANULADA)->sum('total_venta'),
        ];

        return view('modules.detalles_ventas.index', compact('titulo', 'ventas', 'totales'));
    }

    public function show($id)
    {
        $venta = Venta::with(['user', 'cliente', 'detalles.producto', 'detalles.combo', 'pagos', 'anuladaPor'])->findOrFail($id);
        $titulo = "Venta #{$venta->numero_ticket}";

        return view('modules.detalles_ventas.show', compact('titulo', 'venta'));
    }
}
