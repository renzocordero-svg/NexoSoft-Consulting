<?php

namespace Tests\Feature;

use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cu03Test extends TestCase
{
    use RefreshDatabase;

    public function test_alta_valida_checkbox_y_movimiento_inicial(): void
    {
        $this->actingAs($this->usuario())->post('/productos', ['nombre' => 'Taladro', 'proveedor' => 'Bosch', 'categoria' => 'Herramientas', 'precio_unitario' => '50.00', 'precio_costo' => '30.00', 'cantidad_stock' => 5, 'stock_minimo' => 2, 'activo' => 'on'])->assertSessionHasNoErrors()->assertRedirect('/productos');
        $this->assertDatabaseHas('productos', ['nombre' => 'Taladro', 'activo' => true, 'cantidad_stock' => 5]);
        $this->assertDatabaseHas('movimiento_stocks', ['tipo' => 'entrada', 'cantidad' => 5, 'motivo' => 'compra']);
    }

    public function test_ajuste_no_permite_stock_negativo_y_audita_entrada(): void
    {
        $p = $this->producto();
        $this->actingAs($this->usuario());
        $this->post("/productos/{$p->id}/stock", ['tipo' => 'salida', 'cantidad' => 11, 'motivo' => 'ajuste'])->assertSessionHas('error');
        $this->assertSame(10, $p->fresh()->cantidad_stock);
        $this->assertDatabaseCount('movimiento_stocks', 0);
        $this->post("/productos/{$p->id}/stock", ['tipo' => 'entrada', 'cantidad' => 4, 'motivo' => 'compra'])->assertSessionHas('success');
        $this->assertSame(14, $p->fresh()->cantidad_stock);
        $this->assertDatabaseCount('movimiento_stocks', 1);
    }

    public function test_edicion_no_sobrescribe_stock_y_rechaza_duplicado(): void
    {
        $p = $this->producto();
        $this->actingAs($this->usuario());
        $this->put("/productos/{$p->id}", array_merge($p->toArray(), ['cantidad_stock' => 900, 'activo' => true]))->assertSessionHasNoErrors();
        $this->assertSame(10, $p->fresh()->cantidad_stock);
        $this->post('/productos', $p->toArray())->assertSessionHasErrors('nombre');
        $this->get('/productos?buscar=Stanley')->assertSee('Martillo');
        $this->get('/productos/imprimir')->assertOk();
    }

    public function test_desactivar_producto_con_movimientos_preserva_historial(): void
    {
        $p = $this->producto();
        $u = $this->usuario();
        app(InventarioService::class)->mover($p->id, 'entrada', 1, 'compra', $u->id);
        $this->actingAs($u)->delete("/productos/{$p->id}")->assertRedirect('/productos');
        $this->assertFalse($p->fresh()->activo);
        $this->assertDatabaseCount('movimiento_stocks', 1);
    }
}
