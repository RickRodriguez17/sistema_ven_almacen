<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Imagen;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Productos extends Controller
{
    public function index(Request $request)
    {
        $titulo = 'Productos';
        $categorias = Categoria::orderBy('nombre')->get();

        $query = Producto::with(['categoria', 'imagen'])->orderBy('nombre');

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo', 'like', "%{$q}%");
            });
        }

        $items = $query->get();

        return view('modules.productos.index', compact('titulo', 'items', 'categorias'));
    }

    public function create()
    {
        $titulo = 'Crear producto';
        $categorias = Categoria::orderBy('nombre')->get();

        return view('modules.productos.create', compact('titulo', 'categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'categoria_id' => ['required', 'exists:categorias,id'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'cantidad' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        try {
            DB::transaction(function () use ($request, $data) {
                $producto = Producto::create([
                    'user_id' => Auth::id(),
                    'categoria_id' => $data['categoria_id'],
                    'codigo' => $data['codigo'] ?? null,
                    'nombre' => $data['nombre'],
                    'descripcion' => $data['descripcion'] ?? null,
                    'cantidad' => $data['cantidad'] ?? 0,
                    'stock_minimo' => $data['stock_minimo'] ?? 0,
                    'precio_compra' => $data['precio_compra'] ?? 0,
                    'precio_venta' => $data['precio_venta'],
                    'activo' => true,
                ]);

                $this->subirImagen($request, $producto->id);
            });

            return to_route('productos')->with('success', 'Producto creado exitosamente.');
        } catch (\Throwable $th) {
            return back()->withInput()->with('error', 'Error al crear producto: '.$th->getMessage());
        }
    }

    protected function subirImagen(Request $request, int $productoId): ?Imagen
    {
        if (! $request->hasFile('imagen')) {
            return null;
        }

        $rutaImagen = $request->file('imagen')->store('imagenes', 'public');

        return Imagen::create([
            'producto_id' => $productoId,
            'nombre' => basename($rutaImagen),
            'ruta' => $rutaImagen,
        ]);
    }

    public function show(string $id)
    {
        $titulo = 'Eliminar producto';
        $items = Producto::with('categoria')->findOrFail($id);

        return view('modules.productos.show', compact('titulo', 'items'));
    }

    public function edit(string $id)
    {
        $titulo = 'Editar producto';
        $categorias = Categoria::orderBy('nombre')->get();
        $item = Producto::findOrFail($id);

        return view('modules.productos.edit', compact('titulo', 'item', 'categorias'));
    }

    public function update(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'categoria_id' => ['required', 'exists:categorias,id'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $producto->update($data);

            return to_route('productos')->with('success', 'Producto actualizado exitosamente.');
        } catch (\Throwable $th) {
            return back()->withInput()->with('error', 'Error al actualizar: '.$th->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $producto = Producto::findOrFail($id);
            foreach ($producto->imagenes as $img) {
                if ($img->ruta && Storage::disk('public')->exists($img->ruta)) {
                    Storage::disk('public')->delete($img->ruta);
                }
            }
            $producto->delete();

            return to_route('productos')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Throwable $th) {
            return to_route('productos')->with('error', 'Error al eliminar: '.$th->getMessage());
        }
    }

    public function estado(Request $request, $id)
    {
        $estado = $request->input('estado', $request->route('estado'));
        $producto = Producto::findOrFail($id);
        $producto->activo = (bool) $estado;
        $producto->save();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'activo' => $producto->activo]);
        }

        return back()->with('success', 'Estado actualizado.');
    }

    public function show_image($id)
    {
        $titulo = 'Editar imagen';
        $item = Imagen::findOrFail($id);

        return view('modules.productos.show-image', compact('titulo', 'item'));
    }

    public function update_image(Request $request, $id)
    {
        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        try {
            $item = Imagen::findOrFail($id);

            if ($item->ruta && Storage::disk('public')->exists($item->ruta)) {
                Storage::disk('public')->delete($item->ruta);
            }

            $rutaImagen = $request->file('imagen')->store('imagenes', 'public');
            $item->update([
                'ruta' => $rutaImagen,
                'nombre' => basename($rutaImagen),
            ]);

            return to_route('productos')->with('success', 'Imagen actualizada exitosamente.');
        } catch (\Throwable $th) {
            return back()->with('error', 'No se pudo actualizar la imagen: '.$th->getMessage());
        }
    }
}
