<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Combo extends Model
{
    use HasFactory;

    protected $table = 'combos';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'precio',
        'activo',
        'imagen_path',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ComboItem::class);
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function imagenUrl(): ?string
    {
        return $this->imagen_path ? asset('storage/'.$this->imagen_path) : null;
    }
}
