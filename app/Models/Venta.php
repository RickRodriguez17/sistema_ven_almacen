<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    public const ESTADO_PAGADA = 'pagada';

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_ANULADA = 'anulada';

    public const TIPO_MESA = 'mesa';

    public const TIPO_LLEVAR = 'llevar';

    public const TIPO_DELIVERY = 'delivery';

    public const TIPOS_PEDIDO = [
        self::TIPO_LLEVAR => 'Para llevar',
        self::TIPO_MESA => 'Mesa',
        self::TIPO_DELIVERY => 'Delivery',
    ];

    protected $fillable = [
        'user_id',
        'cierre_caja_id',
        'cliente_id',
        'numero_ticket',
        'metodo_pago',
        'efectivo_recibido',
        'cambio',
        'total_venta',
        'tipo_pedido',
        'mesa',
        'direccion_delivery',
        'estado',
        'motivo_anulacion',
        'anulada_at',
        'anulada_por_user_id',
        'notas',
        'nombre_cliente_libre',
    ];

    protected $casts = [
        'total_venta' => 'float',
        'efectivo_recibido' => 'float',
        'cambio' => 'float',
        'anulada_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cierreCaja(): BelongsTo
    {
        return $this->belongsTo(CierreCaja::class, 'cierre_caja_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function anuladaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulada_por_user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoVenta::class);
    }

    public function scopePagadas(Builder $q): Builder
    {
        return $q->where('estado', self::ESTADO_PAGADA);
    }

    public function scopePendientes(Builder $q): Builder
    {
        return $q->where('estado', self::ESTADO_PENDIENTE);
    }

    public function estaAnulada(): bool
    {
        return $this->estado === self::ESTADO_ANULADA;
    }

    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function tipoPedidoLabel(): string
    {
        return self::TIPOS_PEDIDO[$this->tipo_pedido] ?? $this->tipo_pedido;
    }

    public function totalPagado(): float
    {
        return (float) $this->pagos->sum('monto');
    }
}
