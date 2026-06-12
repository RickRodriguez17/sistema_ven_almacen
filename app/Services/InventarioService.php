<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class InventarioService
{
    public function registrarMovimiento(
        Producto $producto,
        string $tipo,
        int $cantidad,
        ?string $motivo = null,
        ?string $referenciaTipo = null,
        ?int $referenciaId = null,
        ?int $userId = null,
    ): MovimientoInventario {
        if ($cantidad <= 0) {
            throw new RuntimeException('La cantidad debe ser mayor a cero.');
        }

        $producto->refresh();
        $stockAnterior = $producto->cantidad;

        $stockNuevo = match ($tipo) {
            MovimientoInventario::TIPO_ENTRADA => $stockAnterior + $cantidad,
            MovimientoInventario::TIPO_SALIDA => $stockAnterior - $cantidad,
            MovimientoInventario::TIPO_AJUSTE => $cantidad,
            default => throw new RuntimeException("Tipo de movimiento inválido: {$tipo}"),
        };

        if ($stockNuevo < 0) {
            throw new RuntimeException("Stock insuficiente para {$producto->nombre}. Disponible: {$stockAnterior}");
        }

        $producto->cantidad = $stockNuevo;
        $producto->save();

        return MovimientoInventario::create([
            'user_id' => $userId ?? Auth::id(),
            'producto_id' => $producto->id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'motivo' => $motivo,
            'referencia_tipo' => $referenciaTipo,
            'referencia_id' => $referenciaId,
        ]);
    }
}
