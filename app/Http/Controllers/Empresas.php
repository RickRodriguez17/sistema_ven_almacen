<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Empresas extends Controller
{
    public function edit()
    {
        $empresa = Empresa::query()->orderBy('id')->first() ?? new Empresa([
            'nombre' => config('negocio.nombre'),
            'direccion' => config('negocio.direccion'),
            'telefono' => config('negocio.telefono'),
            'moneda' => config('negocio.moneda'),
            'mensaje_ticket' => config('negocio.mensaje_ticket'),
            'iva_porcentaje' => config('negocio.iva_porcentaje', 0),
        ]);

        return view('modules.empresa.edit', [
            'titulo' => 'Datos de la Empresa',
            'empresa' => $empresa,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'razon_social' => ['nullable', 'string', 'max:160'],
            'nit' => ['nullable', 'string', 'max:40'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'moneda' => ['required', 'string', 'max:8'],
            'mensaje_ticket' => ['nullable', 'string', 'max:200'],
            'iva_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $empresa = Empresa::query()->orderBy('id')->first() ?? new Empresa;

        if ($request->hasFile('logo')) {
            if ($empresa->logo_path) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('empresa', 'public');
        }

        unset($data['logo']);

        $empresa->fill($data);
        $empresa->save();

        return redirect()->route('empresa.edit')->with('success', 'Datos de la empresa actualizados.');
    }
}
