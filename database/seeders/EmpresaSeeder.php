<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        if (Empresa::count() === 0) {
            Empresa::create([
                'nombre' => 'Pollos Mafu',
                'razon_social' => 'Pollos Mafu S.R.L.',
                'nit' => '',
                'direccion' => 'Av. Principal 123',
                'telefono' => '+591 700 000 000',
                'email' => 'contacto@pollosmafu.bo',
                'moneda' => 'Bs',
                'mensaje_ticket' => '¡Gracias por su compra!',
                'iva_porcentaje' => 0,
            ]);
        }
    }
}
