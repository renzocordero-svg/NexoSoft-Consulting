<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ve_productos_bajo_stock_minimo(): void
    {
        $admin = $this->usuario();
        $this->producto(['nombre' => 'Tornillo 1/4', 'cantidad_stock' => 2, 'stock_minimo' => 5]);
        $this->producto(['nombre' => 'Martillo', 'cantidad_stock' => 50, 'stock_minimo' => 5]);
        $this->producto(['nombre' => 'Clavo inactivo', 'cantidad_stock' => 0, 'stock_minimo' => 5, 'activo' => false]);

        $respuesta = $this->actingAs($admin)->get('/')->assertOk()
            ->assertSee('Productos bajo stock mínimo')
            ->assertSee('Tornillo 1/4')
            ->assertDontSee('Clavo inactivo');

        $this->assertSame(['Tornillo 1/4'], $respuesta->viewData('bajoStock')->pluck('nombre')->all());
    }

    public function test_admin_sin_productos_bajo_minimo_ve_mensaje(): void
    {
        $admin = $this->usuario();
        $this->producto(['cantidad_stock' => 50, 'stock_minimo' => 5]);

        $this->actingAs($admin)->get('/')->assertOk()
            ->assertSee('Todos los productos tienen stock suficiente.');
    }

    public function test_vendedor_no_ve_el_aviso_de_stock(): void
    {
        $vendedor = $this->usuario('vendedor', 'vendedor1');
        $this->producto(['nombre' => 'Tornillo 1/4', 'cantidad_stock' => 2, 'stock_minimo' => 5]);

        $respuesta = $this->actingAs($vendedor)->get('/')->assertOk()
            ->assertDontSee('Productos bajo stock mínimo')
            ->assertDontSee('Tornillo 1/4');

        $this->assertTrue($respuesta->viewData('bajoStock')->isEmpty());
    }
}
