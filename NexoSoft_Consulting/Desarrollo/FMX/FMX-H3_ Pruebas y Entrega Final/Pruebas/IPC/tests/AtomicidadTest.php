<?php

namespace Tests\IPC;

use App\Models\Factura;
use App\Models\MovimientoStock;
use App\Services\InventarioService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class AtomicidadTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require FMX_APP_PATH.'/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_at01_venta_valida_confirma_todos_los_efectos(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $v = $this->vender($u, $p, 2);
        $this->assertSame(8, $p->fresh()->cantidad_stock);
        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseCount('detalle_ventas', 1);
        $this->assertDatabaseCount('facturas', 1);
        $this->assertDatabaseCount('movimiento_stocks', 1);
        $this->assertSame('61.34', $v->factura->total);
        $this->assertDatabaseHas('movimiento_stocks', ['producto_id' => $p->id, 'usuario_id' => $u->id, 'cantidad' => 2, 'tipo' => 'salida']);
        $this->assertDatabaseHas('secuencias', ['nombre' => 'factura', 'ultimo' => 1]);
    }

    public function test_at02_stock_insuficiente_deja_la_base_sin_cambios(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $antes = $this->estado();
        try {
            $this->vender($u, $p, 11);
            $this->fail('Debía rechazarse el pedido sin stock.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }
        $this->assertSame($antes, $this->estado());
    }

    public function test_at03_fallo_de_factura_revierte_y_permite_reintento(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $antes = $this->estado();
        $dispatcher = Factura::getEventDispatcher();
        Factura::setEventDispatcher(clone $dispatcher);
        Factura::creating(fn () => throw new RuntimeException('IPC fallo de factura'));
        try {
            $this->vender($u, $p, 2);
            $this->fail('Debía fallar la emisión.');
        } catch (RuntimeException $e) {
            $this->assertSame('IPC fallo de factura', $e->getMessage());
        } finally {
            Factura::setEventDispatcher($dispatcher);
        }
        $this->assertSame($antes, $this->estado());
        $this->assertSame('FACT-000001', $this->vender($u, $p, 2)->factura->numero_factura);
    }

    public function test_at04_fallo_en_segundo_producto_revierte_tambien_el_primero(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $otro = $this->producto(['nombre' => 'Segundo producto']);
        $antes = $this->estado();
        $dispatcher = MovimientoStock::getEventDispatcher();
        MovimientoStock::setEventDispatcher(clone $dispatcher);
        MovimientoStock::creating(function ($m) use ($otro) {
            if ($m->producto_id === $otro->id) {
                throw new RuntimeException('IPC fallo segundo movimiento');
            }
        });
        try {
            app(\App\Services\VentaService::class)->procesarVenta([
                ['producto_id' => $p->id, 'cantidad' => 2],
                ['producto_id' => $otro->id, 'cantidad' => 3],
            ], $u->id);
            $this->fail('Debía fallar el segundo movimiento.');
        } catch (RuntimeException $e) {
            $this->assertSame('IPC fallo segundo movimiento', $e->getMessage());
        } finally {
            MovimientoStock::setEventDispatcher($dispatcher);
        }
        $this->assertSame($antes, $this->estado());
        $this->assertSame(10, $p->fresh()->cantidad_stock);
        $this->assertSame(10, $otro->fresh()->cantidad_stock);
    }

    public function test_at05_fallo_de_auditoria_revierte_entrada_de_inventario(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $antes = $this->estado();
        $dispatcher = MovimientoStock::getEventDispatcher();
        MovimientoStock::setEventDispatcher(clone $dispatcher);
        MovimientoStock::creating(fn () => throw new RuntimeException('IPC fallo auditoria'));
        try {
            app(InventarioService::class)->mover($p->id, 'entrada', 4, 'compra', $u->id);
            $this->fail('Debía fallar la auditoría.');
        } catch (RuntimeException $e) {
            $this->assertSame('IPC fallo auditoria', $e->getMessage());
        } finally {
            MovimientoStock::setEventDispatcher($dispatcher);
        }
        $this->assertSame($antes, $this->estado());
    }

    public function test_at06_rechazo_posterior_no_altera_venta_anterior_ni_secuencia(): void
    {
        $u = $this->usuario();
        $p = $this->producto(['cantidad_stock' => 1]);
        $this->vender($u, $p);
        $antes = $this->estado();
        try {
            $this->vender($u, $p);
            $this->fail('Debía rechazarse la segunda venta.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }
        $this->assertSame($antes, $this->estado());
        $this->assertSame(0, $p->fresh()->cantidad_stock);
        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseHas('secuencias', ['nombre' => 'factura', 'ultimo' => 1]);
    }

    private function estado(): array
    {
        $estado = [];
        foreach (['productos', 'ventas', 'detalle_ventas', 'facturas', 'movimiento_stocks'] as $tabla) {
            $estado[$tabla] = DB::table($tabla)->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
        }
        $estado['secuencias'] = DB::table('secuencias')->orderBy('nombre')->get()->map(fn ($r) => (array) $r)->all();

        return $estado;
    }
}
