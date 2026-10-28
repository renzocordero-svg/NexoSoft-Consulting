<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [
                'nombre' => 'Martillo de Carpintero 16oz',
                'proveedor' => 'Stanley Tools',
                'cantidad_stock' => 50,
                'precio_unitario' => 25.99,
                'precio_costo' => 15.50,
                'categoria' => 'Herramientas',
                'ubicacion' => 'Estante A1',
                'stock_minimo' => 5,
                'activo' => true,
            ],
            [
                'nombre' => 'Taladro Eléctrico 750W',
                'proveedor' => 'Black & Decker',
                'cantidad_stock' => 20,
                'precio_unitario' => 89.99,
                'precio_costo' => 58.00,
                'categoria' => 'Herramientas Eléctricas',
                'ubicacion' => 'Estante B2',
                'stock_minimo' => 3,
                'activo' => true,
            ],
            [
                'nombre' => 'Clavos Galvanizados 2" (caja 500u)',
                'proveedor' => 'Ferretería Industrial S.A.',
                'cantidad_stock' => 200,
                'precio_unitario' => 8.50,
                'precio_costo' => 5.20,
                'categoria' => 'Fijaciones',
                'ubicacion' => 'Estante C3',
                'stock_minimo' => 20,
                'activo' => true,
            ],
            [
                'nombre' => 'Pintura Látex Blanca 20L',
                'proveedor' => 'Sherwin-Williams',
                'cantidad_stock' => 30,
                'precio_unitario' => 45.00,
                'precio_costo' => 28.00,
                'categoria' => 'Pinturas',
                'ubicacion' => 'Estante D4',
                'stock_minimo' => 5,
                'activo' => true,
            ],
            [
                'nombre' => 'Llave Inglesa Ajustable 12"',
                'proveedor' => 'Bahco',
                'cantidad_stock' => 40,
                'precio_unitario' => 18.75,
                'precio_costo' => 11.00,
                'categoria' => 'Herramientas',
                'ubicacion' => 'Estante A2',
                'stock_minimo' => 8,
                'activo' => true,
            ],
        ];

        foreach ($productos as $datos) {
            Producto::firstOrCreate(['nombre' => $datos['nombre']], $datos);
        }
    }
}
