<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Usuarios extends Controller
{
    public function index()
    {
        $titulo = 'Usuarios';
        $items = User::orderBy('name')->get();
        $roles = User::ROLES;

        return view('modules.usuarios.index', compact('items', 'titulo', 'roles'));
    }

    public function create()
    {
        $titulo = 'Crear Usuario';
        $roles = User::ROLES;

        return view('modules.usuarios.create', compact('titulo', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        try {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'activo' => true,
                'rol' => $data['rol'],
            ]);

            return to_route('usuarios')->with('success', 'Usuario creado correctamente.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error al crear: '.$e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $item = User::findOrFail($id);
        $titulo = 'Editar Usuario';
        $roles = User::ROLES;

        return view('modules.usuarios.edit', compact('item', 'titulo', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $item = User::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($id)],
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        try {
            $item->update($data);

            return to_route('usuarios')->with('success', 'Usuario actualizado correctamente.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error al actualizar: '.$e->getMessage());
        }
    }

    public function tbody()
    {
        $items = User::orderBy('name')->get();

        return view('modules.usuarios.tbody', compact('items'));
    }

    public function estado(Request $request, $id)
    {
        $item = User::findOrFail($id);

        if (auth()->id() === $item->id) {
            return response()->json(['ok' => false, 'message' => 'No puedes cambiar tu propio estado.'], 422);
        }

        $item->activo = $request->boolean('estado', ! $item->activo);
        $item->save();

        return response()->json(['ok' => true, 'activo' => $item->activo]);
    }

    public function cambio_password(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        $item = User::findOrFail($id);
        $item->password = Hash::make($request->password);
        $item->save();

        return response()->json(['ok' => true]);
    }
}
