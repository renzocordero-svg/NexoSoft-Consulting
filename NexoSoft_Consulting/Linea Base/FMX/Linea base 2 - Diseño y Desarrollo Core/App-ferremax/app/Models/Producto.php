<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'proveedor',
        'cantidad_stock',
        'precio_unitario',
        'precio_costo',
        'categoria',
        'ubicacion',
        'stock_minimo',
        'activo',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'precio_costo' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class);
    }

    public function tieneStockSuficiente(int $cantidad): bool
    {
        return $this->cantidad_stock >= $cantidad;
    }

    /**
     * Descuenta stock atómicamente. Lanza excepción si el resultado sería negativo.
     * Siempre llamar dentro de una DB::transaction().
     */
    public function descontarStock(int $cantidad): void
    {
        if (! $this->tieneStockSuficiente($cantidad)) {
            throw new \RuntimeException(
                "Stock insuficiente para \"{$this->nombre}\". "
                ."Disponible: {$this->cantidad_stock}, solicitado: {$cantidad}."
            );
        }

        // decrement() genera: UPDATE productos SET cantidad_stock = cantidad_stock - N WHERE id = ?
        // Evita condiciones de carrera frente a actualizaciones concurrentes.
        $this->decrement('cantidad_stock', $cantidad);
    }
}
