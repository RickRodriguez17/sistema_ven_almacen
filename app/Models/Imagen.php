<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Imagen extends Model
{
    use HasFactory;

    protected $table = 'imagenes';

    protected $fillable = ['producto_id', 'nombre', 'ruta'];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
