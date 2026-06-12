<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

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

    public function ventas()
    {
        return Venta::query()
            ->where(function ($q) {
                $q->where('cierre_caja_id', $this->id)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('cierre_caja_id')
                            ->where('user_id', $this->user_id)
                            ->whereBetween('created_at', [
                                $this->fecha_apertura,
                                $this->fecha_cierre ?? now(),
                            ]);
                    });
            });
    }

    public function calcularResumenEnVivo(): array
    {
        $ventas = $this->ventas()
            ->with('pagos')
            ->where('estado', Venta::ESTADO_PAGADA)
            ->get();

        $totalGastos = round((float) $this->gastos()->sum('monto'), 2);

        $resumen = [
            'cantidad_ventas' => $ventas->count(),
            'total_ventas' => round($ventas->sum('total_venta'), 2),
            'total_efectivo' => 0,
            'total_tarjeta' => 0,
            'total_transferencia' => 0,
            'total_yape' => 0,
            'total_plin' => 0,
            'total_otros' => 0,
            'total_gastos' => $totalGastos,
        ];

        foreach ($ventas as $v) {
            if ($v->pagos && $v->pagos->count() > 0) {
                foreach ($v->pagos as $p) {
                    $key = 'total_'.$p->metodo_pago;
                    if (array_key_exists($key, $resumen)) {
                        $resumen[$key] += $p->monto;
                    } else {
                        $resumen['total_otros'] += $p->monto;
                    }
                }
            } else {
                $key = 'total_'.$v->metodo_pago;
                if (array_key_exists($key, $resumen)) {
                    $resumen[$key] += $v->total_venta;
                } else {
                    $resumen['total_otros'] += $v->total_venta;
                }
            }
        }

        foreach ($resumen as $k => $val) {
            if (str_starts_with($k, 'total_')) {
                $resumen[$k] = round((float) $val, 2);
            }
        }

        return $resumen;
    }
}
