<?php

namespace App\Services;

use App\Models\MovimientoStock;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    public function mover(int $id, string $tipo, int $cantidad, string $motivo, int $usuario): void
    {
        if (! in_array($tipo, ['entrada', 'salida']) || $cantidad < 1 || ! in_array($motivo, ['compra', 'ajuste'])) {
            throw new \RuntimeException('Movimiento inválido.');
        }
        DB::transaction(function () use ($id, $tipo, $cantidad, $motivo, $usuario) {
            $p = Producto::whereKey($id)->lockForUpdate()->firstOrFail();
            if (! $p->activo) {
                throw new \RuntimeException('El producto está inactivo.');
            }
            $nuevo = $p->cantidad_stock + ($tipo === 'entrada' ? $cantidad : -$cantidad);
            if ($nuevo < 0 || $nuevo > 2147483647) {
                throw new \RuntimeException('La cantidad supera el stock disponible o el límite admitido.');
            }
            $p->update(['cantidad_stock' => $nuevo]);
            MovimientoStock::create(['producto_id' => $id, 'tipo' => $tipo, 'cantidad' => $cantidad, 'motivo' => $motivo, 'usuario_id' => $usuario, 'fecha_hora' => now()]);
        }, 5);
    }
}
