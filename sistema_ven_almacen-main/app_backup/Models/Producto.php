<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'user_id',
        'categoria_id',
        'codigo',
        'nombre',
        'descripcion',
        'cantidad',
        'stock_minimo',
        'precio_compra',
        'precio_venta',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio_compra' => 'float',
        'precio_venta' => 'float',
        'cantidad' => 'integer',
        'stock_minimo' => 'integer',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imagen(): HasOne
    {
        return $this->hasOne(Imagen::class)->latestOfMany();
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(Imagen::class);
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function getStockBajoAttribute(): bool
    {
        return $this->cantidad <= $this->stock_minimo;
    }
}
