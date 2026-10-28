<?php

namespace Tests\Feature;

use App\Services\ReporteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cu09Test extends TestCase
{
    use RefreshDatabase;

    public function test_reportes_sqlite_ganancia_historica_y_filtros(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $this->vender($u, $p, 2);
        $p->update(['precio_costo' => 99]);
        $data = app(ReporteService::class)->generar(['fecha_desde' => now()->startOfMonth()->format('Y-m-d'), 'fecha_hasta' => now()->endOfMonth()->format('Y-m-d')]);
        $this->assertSame(61.34, $data['resumen']['ingresos']);
        $this->assertSame(20.98, $data['resumen']['ganancia']);
        $this->assertCount(1, $data['masVendidos']);
        $this->actingAs($u)->get('/reportes')->assertOk()->assertSee('61.34')->assertSee('Martillo');
        $this->get('/reportes?fecha_desde=2026-12-02&fecha_hasta=2026-12-01')->assertSessionHasErrors('fecha_hasta');
    }

    public function test_reportes_sin_ventas(): void
    {
        $this->actingAs($this->usuario())->get('/reportes')->assertOk()->assertSee('0.00');
    }
}
