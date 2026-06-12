<?php

namespace App\Http\Controllers;

use App\Models\Combo;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Combos extends Controller
{
    public function index()
    {
        $titulo = 'Combos / Promos';
        $items = Combo::with('items.producto')->orderBy('nombre')->get();

        return view('modules.combos.index', compact('titulo', 'items'));
    }

    public function create()
    {
        $titulo = 'Nuevo combo';
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();

        return view('modules.combos.form', compact('titulo', 'productos'));
    }

    public function edit(int $id)
    {
        $titulo = 'Editar combo';
        $combo = Combo::with('items')->findOrFail($id);
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();

        return view('modules.combos.form', compact('titulo', 'combo', 'productos'));
    }

    public function store(Request $request)
    {
        $data = $this->validarRequest($request);

        DB::transaction(function () use ($data, $request) {
            $combo = Combo::create([
                'nombre' => $data['nombre'],
                'codigo' => $data['codigo'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'precio' => $data['precio'],
                'activo' => $data['activo'] ?? true,
            ]);

            if ($request->hasFile('imagen')) {
                $combo->imagen_path = $request->file('imagen')->store('combos', 'public');
                $combo->save();
            }

            foreach ($data['items'] as $item) {
                $combo->items()->create([
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                ]);
            }
        });

        return redirect()->route('combos')->with('success', 'Combo creado.');
    }

    public function update(Request $request, int $id)
    {
        $combo = Combo::findOrFail($id);
        $data = $this->validarRequest($request, $combo->id);

        DB::transaction(function () use ($combo, $data, $request) {
            $combo->fill([
                'nombre' => $data['nombre'],
                'codigo' => $data['codigo'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'precio' => $data['precio'],
                'activo' => $data['activo'] ?? false,
            ]);

            if ($request->hasFile('imagen')) {
                if ($combo->imagen_path) {
                    Storage::disk('public')->delete($combo->imagen_path);
                }
                $combo->imagen_path = $request->file('imagen')->store('combos', 'public');
            }

            $combo->save();

            $combo->items()->delete();
            foreach ($data['items'] as $item) {
                $combo->items()->create([
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                ]);
            }
        });

        return redirect()->route('combos')->with('success', 'Combo actualizado.');
    }

    public function destroy(int $id)
    {
        $combo = Combo::findOrFail($id);
        if ($combo->imagen_path) {
            Storage::disk('public')->delete($combo->imagen_path);
        }
        $combo->delete();

        return back()->with('success', 'Combo eliminado.');
    }

    public function toggleEstado(int $id)
    {
        $combo = Combo::findOrFail($id);
        $combo->activo = ! $combo->activo;
        $combo->save();

        return response()->json(['ok' => true, 'activo' => $combo->activo]);
    }

    protected function validarRequest(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:30', 'unique:combos,codigo'.($ignoreId ? ','.$ignoreId : '')],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio' => ['required', 'numeric', 'min:0'],
            'activo' => ['nullable', 'boolean'],
            'imagen' => ['nullable', 'image', 'max:2048'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'distinct', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);
    }
}
