<?php

namespace Tests\Hito3;

use App\Models\Factura;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class IntegracionVentaTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require FMX_APP_PATH.'/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    // CP-11 y CP-20: comprobar importes y auditoría de una venta de dos productos.
    public function test_cp11_venta_con_varios_productos_registra_importes_y_movimientos(): void
    {
        $usuario = $this->usuario('vendedor', 'vendedor_integracion');
        $martillo = $this->producto(['cantidad_stock' => 10]);
        $clavos = $this->producto(['nombre' => 'Clavos', 'cantidad_stock' => 20, 'precio_unitario' => '3.50', 'precio_costo' => '2.00']);

        $this->actingAs($usuario)->post('/ventas', ['items' => [
            ['producto_id' => $martillo->id, 'cantidad' => 2],
            ['producto_id' => $clavos->id, 'cantidad' => 3],
        ]])->assertSessionHasNoErrors()->assertSessionHas('success');

        $venta = Venta::sole();
        // 2 x 25.99 + 3 x 3.50 = 62.48; IGV 11.25; total 73.73.
        $this->assertSame('62.48', $venta->subtotal);
        $this->assertSame('11.25', $venta->impuestos);
        $this->assertSame('73.73', $venta->total);
        $this->assertDatabaseCount('detalle_ventas', 2);
        $this->assertDatabaseCount('facturas', 1);
        $this->assertDatabaseHas('facturas', ['venta_id' => $venta->id, 'numero_factura' => 'FACT-000001', 'total' => '73.73']);
        $this->assertSame(8, $martillo->fresh()->cantidad_stock);
        $this->assertSame(17, $clavos->fresh()->cantidad_stock);
        $this->assertDatabaseCount('movimiento_stocks', 2);
        foreach ([[$martillo, 2], [$clavos, 3]] as [$producto, $cantidad]) {
            $this->assertDatabaseHas('movimiento_stocks', ['producto_id' => $producto->id, 'usuario_id' => $usuario->id, 'tipo' => 'salida', 'motivo' => 'venta', 'cantidad' => $cantidad]);
        }
    }

    public function test_cp12_stock_insuficiente_conserva_todos_los_productos_y_el_carrito(): void
    {
        $usuario = $this->usuario();
        $primero = $this->producto();
        $segundo = $this->producto(['nombre' => 'Sin existencias', 'cantidad_stock' => 0]);
        $items = [['producto_id' => $primero->id, 'cantidad' => 2], ['producto_id' => $segundo->id, 'cantidad' => 1]];

        $this->actingAs($usuario)->from('/ventas/create')->post('/ventas', ['items' => $items])
            ->assertRedirect('/ventas/create')->assertSessionHas('error')
            ->assertSessionHasInput('items', $items);

        $this->assertSame(10, $primero->fresh()->cantidad_stock);
        $this->assertSame(0, $segundo->fresh()->cantidad_stock);
        $this->assertSinVentaRegistrada();
    }

    // CP-13: provocar un fallo después de descontar stock y actualizar el correlativo.
    public function test_cp13_fallo_al_emitir_factura_revierte_venta_stock_auditoria_y_secuencia(): void
    {
        $usuario = $this->usuario();
        $producto = $this->producto();
        $dispatcher = Factura::getEventDispatcher();
        Factura::setEventDispatcher(clone $dispatcher);
        Factura::creating(function () {
            throw new RuntimeException('Fallo de factura simulado por la prueba CP-13');
        });

        try {
            app(VentaService::class)->procesarVenta([['producto_id' => $producto->id, 'cantidad' => 2]], $usuario->id);
            $this->fail('La emisión de factura debía fallar.');
        } catch (RuntimeException $error) {
            $this->assertSame('Fallo de factura simulado por la prueba CP-13', $error->getMessage());
        } finally {
            Factura::setEventDispatcher($dispatcher);
        }

        $this->assertSame(10, $producto->fresh()->cantidad_stock);
        $this->assertSinVentaRegistrada();
        $this->vender($usuario, $producto);
        $this->assertDatabaseHas('facturas', ['numero_factura' => 'FACT-000001']);
    }

    public function test_cp15_facturas_consecutivas_y_unicas_para_ventas_sucesivas(): void
    {
        $usuario = $this->usuario();
        $producto = $this->producto();
        for ($i = 0; $i < 3; $i++) {
            $this->vender($usuario, $producto);
        }

        $this->assertSame(['FACT-000001', 'FACT-000002', 'FACT-000003'], Factura::orderBy('id')->pluck('numero_factura')->all());
        $this->assertDatabaseCount('ventas', 3);
        $this->assertSame(7, $producto->fresh()->cantidad_stock);
        $this->assertDatabaseHas('secuencias', ['nombre' => 'factura', 'ultimo' => 3]);
    }

    public function test_cp17_precio_y_costo_historicos_se_conservan_tras_cambiar_catalogo(): void
    {
        $usuario = $this->usuario();
        $producto = $this->producto();
        $venta = $this->vender($usuario, $producto, 2);
        $producto->update(['precio_unitario' => '99.00', 'precio_costo' => '80.00']);

        $detalle = $venta->detalles()->sole();
        $this->assertSame('25.99', $detalle->precio_unitario_momento);
        $this->assertSame('15.50', $detalle->costo_unitario_momento);
        $this->assertSame('51.98', $detalle->subtotal_linea);
        $this->assertSame('61.34', $venta->fresh()->total);
        $this->assertSame('61.34', $venta->factura->fresh()->total);
    }

    public function test_carrito_vacio_no_genera_registros_ni_consume_correlativo(): void
    {
        $this->actingAs($this->usuario())->post('/ventas', ['items' => []])->assertSessionHasErrors('items');
        $this->assertSinVentaRegistrada();
    }

    public function test_venta_de_la_ultima_unidad_y_rechazo_posterior_sin_stock_negativo(): void
    {
        $usuario = $this->usuario();
        $producto = $this->producto(['cantidad_stock' => 1]);
        $this->vender($usuario, $producto);
        $this->actingAs($usuario)->post('/ventas', ['items' => [['producto_id' => $producto->id, 'cantidad' => 1]]])->assertSessionHas('error');

        $this->assertSame(0, $producto->fresh()->cantidad_stock);
        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseCount('facturas', 1);
        $this->assertDatabaseCount('movimiento_stocks', 1);
        $this->assertDatabaseHas('secuencias', ['nombre' => 'factura', 'ultimo' => 1]);
        // Es un límite secuencial. No sustituye CP-14 con procesos concurrentes en MySQL.
    }

    private function assertSinVentaRegistrada(): void
    {
        foreach (['ventas', 'detalle_ventas', 'facturas', 'movimiento_stocks'] as $tabla) {
            $this->assertDatabaseCount($tabla, 0);
        }
        $this->assertSame(0, (int) DB::table('secuencias')->where('nombre', 'factura')->value('ultimo'));
    }
}
