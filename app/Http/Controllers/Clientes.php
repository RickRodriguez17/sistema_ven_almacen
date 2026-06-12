<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Clientes extends Controller
{
    public function index()
    {
        $titulo = 'Clientes';
        $items = Cliente::orderBy('nombre')->get();

        return view('modules.clientes.index', compact('titulo', 'items'));
    }

    public function create()
    {
        $titulo = 'Nuevo Cliente';

        return view('modules.clientes.create', compact('titulo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
        ]);

        $data['user_id'] = Auth::id();
        $data['apellido'] = $data['apellido'] ?? '';

        try {
            $cliente = Cliente::create($data);

            if ($request->expectsJson() || $request->boolean('json')) {
                return response()->json(['ok' => true, 'cliente' => $cliente]);
            }

            return to_route('clientes')->with('success', 'Cliente creado correctamente.');
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $item = Cliente::findOrFail($id);
        $titulo = 'Editar Cliente';

        return view('modules.clientes.edit', compact('titulo', 'item'));
    }

    public function update(Request $request, string $id)
    {
        $item = Cliente::findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
        ]);

        $data['apellido'] = $data['apellido'] ?? '';

        try {
            $item->update($data);

            return to_route('clientes')->with('success', 'Cliente actualizado.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $item = Cliente::findOrFail($id);
            $item->delete();

            return to_route('clientes')->with('success', 'Cliente eliminado.');
        } catch (\Throwable $e) {
            return to_route('clientes')->with('error', 'Error: '.$e->getMessage());
        }
    }
}
