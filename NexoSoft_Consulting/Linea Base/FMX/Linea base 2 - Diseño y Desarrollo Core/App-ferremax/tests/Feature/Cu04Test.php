<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cu04Test extends TestCase
{
    use RefreshDatabase;

    public function test_venta_atomica_con_igv_factura_y_costo_historico(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $this->actingAs($u)->post('/ventas', ['items' => [['producto_id' => $p->id, 'cantidad' => 2]]])->assertRedirect('/ventas/1');
        $this->assertDatabaseHas('ventas', ['subtotal' => 51.98, 'impuestos' => 9.36, 'total' => 61.34]);
        $this->assertDatabaseHas('facturas', ['numero_factura' => 'FACT-000001', 'total' => 61.34]);
        $this->assertDatabaseHas('detalle_ventas', ['costo_unitario_momento' => 15.50]);
        $this->assertSame(8, $p->fresh()->cantidad_stock);
        $this->assertDatabaseHas('movimiento_stocks', ['tipo' => 'salida', 'cantidad' => 2]);
    }

    public function test_stock_insuficiente_no_deja_venta_parcial(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $otro = $this->producto(['nombre' => 'Otro']);
        $this->actingAs($u)->post('/ventas', ['items' => [['producto_id' => $otro->id, 'cantidad' => 1], ['producto_id' => $p->id, 'cantidad' => 11]]])->assertSessionHas('error');
        foreach (['ventas', 'detalle_ventas', 'facturas', 'movimiento_stocks'] as $tabla) {
            $this->assertDatabaseCount($tabla, 0);
        }
        $this->assertSame(10, $otro->fresh()->cantidad_stock);
    }

    public function test_rechaza_carrito_vacio_duplicado_y_producto_inactivo(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $this->actingAs($u);
        $this->post('/ventas', ['items' => []])->assertSessionHasErrors('items');
        $this->post('/ventas', ['items' => [['producto_id' => $p->id, 'cantidad' => 6], ['producto_id' => $p->id, 'cantidad' => 6]]])->assertSessionHasErrors();
        $p->update(['activo' => false]);
        $this->post('/ventas', ['items' => [['producto_id' => $p->id, 'cantidad' => 1]]])->assertSessionHas('error');
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_correlativos_para_productos_distintos(): void
    {
        $u = $this->usuario();
        $this->vender($u, $this->producto());
        $this->vender($u, $this->producto(['nombre' => 'Otro']));
        $this->assertDatabaseHas('facturas', ['numero_factura' => 'FACT-000002']);
    }
}
