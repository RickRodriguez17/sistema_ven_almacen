<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Categorias extends Controller
{
    public function index()
    {
        $titulo = 'Administrar Categorías';
        $items = Categoria::withCount('productos')->orderBy('nombre')->get();

        return view('modules.categorias.index', compact('titulo', 'items'));
    }

    public function create()
    {
        $titulo = 'Crear Categoría';

        return view('modules.categorias.create', compact('titulo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias,nombre'],
        ]);

        try {
            Categoria::create([
                'user_id' => Auth::id(),
                'nombre' => $data['nombre'],
            ]);

            return to_route('categorias')->with('success', 'Categoría creada exitosamente.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error al crear: '.$e->getMessage());
        }
    }

    public function show(string $id)
    {
        $item = Categoria::findOrFail($id);
        $titulo = 'Eliminar Categoría';

        return view('modules.categorias.show', compact('titulo', 'item'));
    }

    public function edit(string $id)
    {
        $item = Categoria::findOrFail($id);
        $titulo = 'Editar Categoría';

        return view('modules.categorias.edit', compact('titulo', 'item'));
    }

    public function update(Request $request, string $id)
    {
        $item = Categoria::findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias,nombre,'.$id],
        ]);

        try {
            $item->update($data);

            return to_route('categorias')->with('success', 'Categoría actualizada exitosamente.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error al actualizar: '.$e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $item = Categoria::findOrFail($id);
            if ($item->productos()->exists()) {
                return to_route('categorias')->with('error', 'No se puede eliminar: la categoría tiene productos asociados.');
            }
            $item->delete();

            return to_route('categorias')->with('success', 'Categoría eliminada exitosamente.');
        } catch (\Throwable $e) {
            return to_route('categorias')->with('error', 'Error al eliminar: '.$e->getMessage());
        }
    }
}
