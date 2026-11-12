<?php

namespace Tests\Hito3;

use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Services\InventarioService;
use App\Services\ReporteService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class CierreIntegracionTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require FMX_APP_PATH.'/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_cp20_cp26_compra_venta_ajuste_y_reporte_concilian_sus_saldos(): void
    {
        $admin = $this->usuario();
        $vendedor = $this->usuario('vendedor', 'cierre_vendedor');
        $producto = $this->producto(['cantidad_stock' => 0, 'precio_unitario' => '10.00', 'precio_costo' => '6.00']);
        $inventario = app(InventarioService::class);
        $inventario->mover($producto->id, 'entrada', 10, 'compra', $admin->id);
        $venta = $this->vender($vendedor, $producto, 3);
        $inventario->mover($producto->id, 'salida', 2, 'ajuste', $admin->id);

        $movimientos = MovimientoStock::where('producto_id', $producto->id)->get();
        $saldo = $movimientos->sum(fn ($m) => $m->tipo === 'entrada' ? $m->cantidad : -$m->cantidad);
        $this->assertSame(5, $producto->fresh()->cantidad_stock);
        $this->assertSame(5, $saldo);
        $this->assertCount(3, $movimientos);
        $this->assertDatabaseHas('movimiento_stocks', ['producto_id' => $producto->id, 'usuario_id' => $vendedor->id, 'tipo' => 'salida', 'motivo' => 'venta', 'cantidad' => 3]);
        $this->assertSame('35.40', $venta->factura->total);
        $reporte = app(ReporteService::class)->generar($this->periodo());
        $this->assertSame(['ingresos' => 35.40, 'ganancia' => 12.0, 'numero_ventas' => 1, 'ticket_promedio' => 35.40], $reporte['resumen']);
        $this->assertEquals(3, $reporte['masVendidos']->sole()->cantidad_vendida);
        $this->actingAs($admin)->get('/movimientos?producto_id='.$producto->id)->assertOk()
            ->assertViewHas('movimientos', fn ($datos) => $datos->total() === 3);
    }

    public function test_cp27_reporte_concilia_facturas_y_costos_historicos_tras_cambiar_catalogo(): void
    {
        $admin = $this->usuario();
        $uno = $this->usuario('vendedor', 'uno');
        $dos = $this->usuario('vendedor', 'dos');
        $producto = $this->producto(['precio_unitario' => '10.00', 'precio_costo' => '6.00']);
        $this->vender($uno, $producto, 2);
        $this->vender($dos, $producto, 3);
        $producto->update(['precio_unitario' => '100.00', 'precio_costo' => '90.00']);
        $reporte = app(ReporteService::class)->generar($this->periodo());
        $this->assertSame(['ingresos' => 59.0, 'ganancia' => 20.0, 'numero_ventas' => 2, 'ticket_promedio' => 29.5], $reporte['resumen']);
        $this->assertEquals(DB::table('facturas')->sum('total'), $reporte['resumen']['ingresos']);
        $this->assertEquals(59.0, array_sum($reporte['ventasPorDia']['data']));
        $query = http_build_query(array_merge($this->periodo(), ['usuario_id' => $uno->id]));
        $this->actingAs($admin)->get('/reportes?'.$query)->assertOk()
            ->assertViewHas('resumen', fn ($r) => $r === ['ingresos' => 23.6, 'ganancia' => 8.0, 'numero_ventas' => 1, 'ticket_promedio' => 23.6]);
    }

    public function test_cp28_rankings_limitan_a_cinco_y_excluyen_productos_sin_ventas(): void
    {
        $admin = $this->usuario();
        $sinVenta = $this->producto(['nombre' => 'Sin ventas']);
        $ids = [];
        for ($i = 1; $i <= 7; $i++) {
            $p = $this->producto(['nombre' => 'Producto '.$i]);
            $ids[] = $p->id;
            $this->vender($admin, $p, $i);
        }
        $reporte = app(ReporteService::class)->generar($this->periodo());
        $this->assertSame(array_reverse(array_slice($ids, 2)), $reporte['masVendidos']->pluck('id')->all());
        $this->assertSame(array_slice($ids, 0, 5), $reporte['menosVendidos']->pluck('id')->all());
        $this->assertNotContains($sinVenta->id, $reporte['menosVendidos']->pluck('id')->all());
        $this->assertEquals([1, 2, 3, 4, 5], $reporte['menosVendidos']->pluck('cantidad_vendida')->all());
    }

    public function test_fallo_al_auditar_movimiento_revierte_el_cambio_de_stock(): void
    {
        $admin = $this->usuario();
        $producto = $this->producto();
        $dispatcher = MovimientoStock::getEventDispatcher();
        MovimientoStock::setEventDispatcher(clone $dispatcher);
        MovimientoStock::creating(function () {
            throw new RuntimeException('Fallo simulado al registrar auditoria');
        });
        try {
            app(InventarioService::class)->mover($producto->id, 'entrada', 5, 'compra', $admin->id);
            $this->fail('El registro de auditoría debía fallar.');
        } catch (RuntimeException $error) {
            $this->assertSame('Fallo simulado al registrar auditoria', $error->getMessage());
        } finally {
            MovimientoStock::setEventDispatcher($dispatcher);
        }
        $this->assertSame(10, $producto->fresh()->cantidad_stock);
        $this->assertDatabaseCount('movimiento_stocks', 0);
        app(InventarioService::class)->mover($producto->id, 'entrada', 5, 'compra', $admin->id);
        $this->assertSame(15, $producto->fresh()->cantidad_stock);
        $this->assertDatabaseCount('movimiento_stocks', 1);
    }

    public function test_reporte_vacio_no_divide_por_cero_y_vendedor_no_accede_a_reportes(): void
    {
        $this->producto();
        $reporte = app(ReporteService::class)->generar($this->periodo());
        $this->assertEquals(['ingresos' => 0, 'ganancia' => 0, 'numero_ventas' => 0, 'ticket_promedio' => 0], $reporte['resumen']);
        $this->assertCount(0, $reporte['masVendidos']);
        $this->assertCount(0, $reporte['menosVendidos']);
        $this->assertEquals(0, array_sum($reporte['ventasPorDia']['data']));
        $this->actingAs($this->usuario('vendedor', 'restringido'))->get('/reportes')->assertForbidden();
        $this->assertDatabaseCount('ventas', 0);
    }

    private function periodo(): array
    {
        return ['fecha_desde' => now()->startOfMonth()->format('Y-m-d'), 'fecha_hasta' => now()->endOfMonth()->format('Y-m-d')];
    }
}
