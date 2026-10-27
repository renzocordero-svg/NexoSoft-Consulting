<?php

namespace Tests;

use App\Models\Producto;
use App\Models\User;
use App\Services\VentaService;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    protected function usuario(string $rol = 'admin', string $nombre = 'admin'): User
    {
        return User::create(['nombre_usuario' => $nombre, 'password' => Hash::make('admin123'), 'rol' => $rol, 'activo' => true]);
    }

    protected function producto(array $datos = []): Producto
    {
        return Producto::create(array_merge(['nombre' => 'Martillo', 'proveedor' => 'Stanley', 'cantidad_stock' => 10, 'stock_minimo' => 3, 'precio_unitario' => '25.99', 'precio_costo' => '15.50', 'categoria' => 'Herramientas', 'activo' => true], $datos));
    }

    protected function vender(User $u, Producto $p, int $n = 1)
    {
        return app(VentaService::class)->procesarVenta([['producto_id' => $p->id, 'cantidad' => $n]], $u->id);
    }
}
