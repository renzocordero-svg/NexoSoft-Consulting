<?php

namespace App\Services;

use App\Models\DetalleVenta;
use App\Models\Factura;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class VentaService
{
    public function procesarVenta(array $items, int $usuarioId): Venta
    {
        if (! $items) {
            throw new \RuntimeException('El carrito está vacío.');
        }
        $ids = array_column($items, 'producto_id');
        if (count(array_unique($ids)) !== count($ids)) {
            throw new \RuntimeException('Hay productos repetidos.');
        }

        return DB::transaction(function () use ($items, $ids, $usuarioId) {
            // Lock this row first in every sale, then products in stable order.
            $sequence = DB::table('secuencias')->where('nombre', 'factura')->lockForUpdate()->first();
            $productos = Producto::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $centavos = 0;
            foreach ($items as $item) {
                $p = $productos->get($item['producto_id']);
                $n = filter_var($item['cantidad'], FILTER_VALIDATE_INT);
                if (! $p || ! $p->activo || ! $n || $n < 1 || $n > 9999) {
                    throw new \RuntimeException('Producto o cantidad inválidos.');
                }
                if ($p->cantidad_stock < $n) {
                    throw new \RuntimeException("Stock insuficiente para {$p->nombre}. Disponible: {$p->cantidad_stock}.");
                }
                $centavos += (int) round((float) $p->precio_unitario * 100) * $n;
            }
            if ($centavos > 847457626) {
                throw new \RuntimeException('El total excede el importe admitido por venta.');
            }
            $igv = (int) round($centavos * 18 / 100);
            $venta = Venta::create(['usuario_id' => $usuarioId, 'fecha_hora' => now(), 'subtotal' => $centavos / 100, 'impuestos' => $igv / 100, 'total' => ($centavos + $igv) / 100]);
            foreach ($items as $item) {
                $p = $productos[$item['producto_id']];
                $n = (int) $item['cantidad'];
                DetalleVenta::create(['venta_id' => $venta->id, 'producto_id' => $p->id, 'cantidad' => $n, 'precio_unitario_momento' => $p->precio_unitario, 'costo_unitario_momento' => $p->precio_costo, 'subtotal_linea' => ((int) round((float) $p->precio_unitario * 100) * $n) / 100]);
                $affected = Producto::whereKey($p->id)->where('cantidad_stock', '>=', $n)->decrement('cantidad_stock', $n);
                if (! $affected) {
                    throw new \RuntimeException('El stock cambió durante la venta. Inténtalo nuevamente.');
                }
                MovimientoStock::create(['producto_id' => $p->id, 'tipo' => 'salida', 'cantidad' => $n, 'usuario_id' => $usuarioId, 'motivo' => 'venta', 'fecha_hora' => now()]);
            }
            $next = $sequence->ultimo + 1;
            DB::table('secuencias')->where('nombre', 'factura')->update(['ultimo' => $next]);
            Factura::create(['venta_id' => $venta->id, 'numero_factura' => sprintf('FACT-%06d', $next), 'fecha_emision' => now(), 'total' => $venta->total]);

            return $venta->load(['detalles.producto', 'factura', 'usuario']);
        }, 5);
    }
}
