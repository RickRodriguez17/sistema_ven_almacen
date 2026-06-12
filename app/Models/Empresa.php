<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'razon_social',
        'nit',
        'direccion',
        'telefono',
        'email',
        'moneda',
        'mensaje_ticket',
        'iva_porcentaje',
        'logo_path',
    ];

    protected $casts = [
        'iva_porcentaje' => 'decimal:2',
    ];

    public static function actual(): self
    {
        return Cache::remember('empresa.actual', 300, function () {
            return self::query()->orderBy('id')->first() ?? new self([
                'nombre' => config('negocio.nombre'),
                'direccion' => config('negocio.direccion'),
                'telefono' => config('negocio.telefono'),
                'nit' => config('negocio.ruc'),
                'moneda' => config('negocio.moneda'),
                'mensaje_ticket' => config('negocio.mensaje_ticket'),
                'iva_porcentaje' => config('negocio.iva_porcentaje', 0),
            ]);
        });
    }

    public static function limpiarCache(): void
    {
        Cache::forget('empresa.actual');
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::limpiarCache());
        static::deleted(fn () => self::limpiarCache());
    }

    public function toNegocio(): array
    {
        return [
            'nombre' => $this->nombre ?? config('negocio.nombre'),
            'razon_social' => $this->razon_social,
            'direccion' => $this->direccion ?? config('negocio.direccion'),
            'telefono' => $this->telefono ?? config('negocio.telefono'),
            'ruc' => $this->nit ?? config('negocio.ruc'),
            'nit' => $this->nit,
            'email' => $this->email,
            'moneda' => $this->moneda ?? config('negocio.moneda'),
            'mensaje_ticket' => $this->mensaje_ticket ?? config('negocio.mensaje_ticket'),
            'iva_porcentaje' => $this->iva_porcentaje ?? config('negocio.iva_porcentaje', 0),
            'logo_path' => $this->logo_path,
        ];
    }
}
