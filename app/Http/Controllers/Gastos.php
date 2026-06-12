<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Gasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Gastos extends Controller
{
    public function index()
    {
        $titulo = 'Gastos de Caja';
        $user = Auth::user();

        $query = Gasto::with(['user', 'cierreCaja'])->latest();

        // Cajeros solo ven sus propios gastos. Admin/almacen ven todos.
        if (! $user->esAdmin() && ! $user->esAlmacen()) {
            $query->where('user_id', $user->id);
        }

        $items = $query->paginate(25);

        $abierto = CierreCaja::abiertoDe(Auth::id());

        return view('modules.gastos.index', compact('titulo', 'items', 'abierto'));
    }

    public function create()
    {
        $titulo = 'Registrar gasto urgente';

        $abierto = CierreCaja::abiertoDe(Auth::id());
        if (! $abierto) {
            return redirect()->route('cierres.iniciar.form')
                ->with('mensaje', 'Para registrar un gasto necesitas tener un turno de caja abierto.');
        }

        return view('modules.gastos.create', compact('titulo', 'abierto'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'concepto' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01'],
        ]);

        $abierto = CierreCaja::abiertoDe(Auth::id());
        if (! $abierto) {
            return redirect()->route('cierres.iniciar.form')
                ->with('mensaje', 'Para registrar un gasto necesitas tener un turno de caja abierto.');
        }

        try {
            DB::transaction(function () use ($data, $abierto) {
                Gasto::create([
                    'user_id' => Auth::id(),
                    'cierre_caja_id' => $abierto->id,
                    'concepto' => $data['concepto'],
                    'monto' => $data['monto'],
                ]);
            });

            return to_route('gastos')->with('success', 'Gasto registrado. Se descontará del efectivo al cerrar el turno.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $gasto = Gasto::findOrFail($id);

        // Cajero solo puede borrar sus propios gastos, y solo si su turno sigue abierto.
        if (! $user->esAdmin()) {
            if ($gasto->user_id !== $user->id) {
                return back()->with('error', 'Solo puedes eliminar tus propios gastos.');
            }
            if ($gasto->cierreCaja && ! $gasto->cierreCaja->estaAbierto()) {
                return back()->with('error', 'No puedes eliminar un gasto de un turno ya cerrado.');
            }
        }

        $gasto->delete();

        return back()->with('success', 'Gasto eliminado.');
    }
}
