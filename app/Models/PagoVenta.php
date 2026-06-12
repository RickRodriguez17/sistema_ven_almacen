<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoVenta extends Model
{
    use HasFactory;

    protected $table = 'pagos_venta';

    public const METODOS = ['efectivo', 'tarjeta', 'transferencia', 'yape', 'plin', 'qr', 'otros'];

    protected $fillable = [
        'venta_id',
        'metodo_pago',
        'monto',
        'efectivo_recibido',
        'cambio',
        'referencia',
    ];

    protected $casts = [
        'monto' => 'float',
        'efectivo_recibido' => 'float',
        'cambio' => 'float',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }
}
