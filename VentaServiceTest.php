<?php

namespace Tests\Unit;

use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VentaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_procesar_venta_recrea_la_secuencia_cuando_falta(): void
    {
        $usuario = $this->usuario();
        $producto = $this->producto();

        DB::table('secuencias')->where('nombre', 'factura')->delete();

        $venta = app(VentaService::class)->procesarVenta([
            ['producto_id' => $producto->id, 'cantidad' => 1],
        ], $usuario->id);

        $this->assertInstanceOf(Venta::class, $venta);
        $this->assertDatabaseHas('secuencias', ['nombre' => 'factura']);
        $this->assertSame('FACT-000001', $venta->factura->numero_factura);
    }

    public function test_procesar_venta_reconstruye_el_ultimo_numero_de_factura_si_la_secuencia_esta_vacia(): void
    {
        $usuario = $this->usuario();
        $producto = $this->producto();

        DB::table('secuencias')->where('nombre', 'factura')->delete();
        $ventaExistente = Venta::create([
            'usuario_id' => $usuario->id,
            'fecha_hora' => now(),
            'subtotal' => 25.99,
            'impuestos' => 4.68,
            'total' => 30.67,
        ]);
        DB::table('facturas')->insert([
            'venta_id' => $ventaExistente->id,
            'numero_factura' => 'FACT-000005',
            'fecha_emision' => now(),
            'total' => 30.67,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $venta = app(VentaService::class)->procesarVenta([
            ['producto_id' => $producto->id, 'cantidad' => 1],
        ], $usuario->id);

        $this->assertSame('FACT-000006', $venta->factura->numero_factura);
        $this->assertDatabaseHas('facturas', ['numero_factura' => 'FACT-000006']);
    }
}
