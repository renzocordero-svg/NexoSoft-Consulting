<?php

namespace Tests\Feature;

use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cu07Test extends TestCase
{
    use RefreshDatabase;

    public function test_movimientos_filtrados_y_exportacion_completa(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        for ($i = 0; $i < 22; $i++) {
            app(InventarioService::class)->mover($p->id, 'entrada', 1, 'compra', $u->id);
        }
        $this->actingAs($u)->get('/movimientos?imprimir=1')->assertOk()->assertSee('22 movimiento(s)');
        $this->get('/movimientos?tipo=salida')->assertOk()->assertSee('No se encontraron movimientos');
        $this->get('/movimientos?fecha_desde=2026-12-02&fecha_hasta=2026-12-01')->assertSessionHasErrors('fecha_hasta');
    }
}
