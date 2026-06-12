<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Inventario extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Movimientos de Inventario';

        $query = MovimientoInventario::with(['producto', 'user'])->latest();

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $movimientos = $query->paginate(30)->withQueryString();
        $productos = Producto::orderBy('nombre')->get();
        $tipos = MovimientoInventario::TIPOS;

        return view('modules.inventario.index', compact('titulo', 'movimientos', 'productos', 'tipos'));
    }

    public function create()
    {
        $titulo = 'Registrar Movimiento de Inventario';
        $productos = Producto::orderBy('nombre')->get();
        $tipos = MovimientoInventario::TIPOS;

        return view('modules.inventario.create', compact('titulo', 'productos', 'tipos'));
    }

    public function store(Request $request, InventarioService $inventario)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'tipo' => ['required', Rule::in(array_keys(MovimientoInventario::TIPOS))],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $producto = Producto::findOrFail($data['producto_id']);
            $inventario->registrarMovimiento(
                producto: $producto,
                tipo: $data['tipo'],
                cantidad: $data['cantidad'],
                motivo: $data['motivo'] ?? null,
                referenciaTipo: 'manual',
            );

            return to_route('inventario')->with('success', 'Movimiento registrado.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function stockBajo()
    {
        $titulo = 'Productos con Stock Bajo';
        $items = Producto::with('categoria')
            ->whereColumn('cantidad', '<=', 'stock_minimo')
            ->where('activo', true)
            ->orderBy('cantidad')
            ->get();

        return view('modules.inventario.stock_bajo', compact('titulo', 'items'));
    }
}
