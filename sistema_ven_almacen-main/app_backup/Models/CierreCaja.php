<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierres_caja';

    public const ESTADO_ABIERTO = 'abierto';

    public const ESTADO_CERRADO = 'cerrado';

    protected $fillable = [
        'user_id',
        'fecha_apertura',
        'fecha_cierre',
        'fondo_inicial',
        'total_ventas',
        'total_efectivo',
        'total_tarjeta',
        'total_transferencia',
        'total_yape',
        'total_plin',
        'total_otros',
        'total_gastos',
        'cantidad_ventas',
        'efectivo_contado',
        'diferencia',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'fondo_inicial' => 'float',
        'total_ventas' => 'float',
        'total_efectivo' => 'float',
        'total_tarjeta' => 'float',
        'total_transferencia' => 'float',
        'total_yape' => 'float',
        'total_plin' => 'float',
        'total_otros' => 'float',
        'total_gastos' => 'float',
        'efectivo_contado' => 'float',
        'diferencia' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'cierre_caja_id');
    }

    public function estaAbierto(): bool
    {
        return $this->estado === self::ESTADO_ABIERTO;
    }

    public static function abiertoDe(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->where('estado', self::ESTADO_ABIERTO)
            ->latest('fecha_apertura')
            ->first();
    }
}
