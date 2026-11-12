<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cu06Test extends TestCase
{
    use RefreshDatabase;

    public function test_historial_filtra_por_vendedor_sin_permitir_suplantacion(): void
    {
        $u = $this->usuario('vendedor', 'uno');
        $otro = $this->usuario('vendedor', 'dos');
        $admin = $this->usuario();
        $p = $this->producto();
        $this->vender($u, $p);
        $this->vender($otro, $p);
        $this->actingAs($u)->get('/ventas?usuario_id='.$otro->id)->assertOk()->assertSee('FACT-000001')->assertDontSee('FACT-000002');
        $this->actingAs($admin)->get('/ventas?usuario_id='.$otro->id)->assertSee('FACT-000002')->assertDontSee('FACT-000001');
        $this->get('/ventas?fecha_desde=2026-12-02&fecha_hasta=2026-12-01')->assertSessionHasErrors('fecha_hasta');
    }

    public function test_historial_busca_por_numero_de_factura(): void
    {
        $admin = $this->usuario();
        $p = $this->producto();
        $this->vender($admin, $p);
        $this->vender($admin, $p);
        $this->vender($admin, $p);
        $this->actingAs($admin)->get('/ventas?numero_factura=FACT-000002')->assertOk()
            ->assertSee('FACT-000002')->assertDontSee('FACT-000001')->assertDontSee('FACT-000003');
        // Búsqueda parcial: basta con el final del número.
        $this->get('/ventas?numero_factura=003')->assertOk()->assertSee('FACT-000003')->assertDontSee('FACT-000002');
        $this->get('/ventas?numero_factura=FACT-999999')->assertOk()->assertDontSee('FACT-000001');
        $this->get('/ventas?numero_factura='.str_repeat('9', 21))->assertSessionHasErrors('numero_factura');
    }

    public function test_sin_numero_de_factura_el_historial_y_los_reportes_no_cambian(): void
    {
        $admin = $this->usuario();
        $p = $this->producto();
        $this->vender($admin, $p);
        $this->vender($admin, $p, 2);
        $this->actingAs($admin)->get('/ventas?numero_factura=')->assertOk()->assertSee('FACT-000001')->assertSee('FACT-000002');
        $sinFiltro = $this->get('/reportes')->assertOk()->viewData('resumen');
        $conVacio = $this->get('/reportes?numero_factura=')->assertOk()->viewData('resumen');
        $this->assertSame($sinFiltro, $conVacio);
        $this->assertSame(2, $sinFiltro['numero_ventas']);
    }

    public function test_vendedor_no_ve_facturas_ajenas_aunque_busque_su_numero(): void
    {
        $u = $this->usuario('vendedor', 'uno');
        $otro = $this->usuario('vendedor', 'dos');
        $p = $this->producto();
        $this->vender($u, $p);
        $this->vender($otro, $p);
        $this->actingAs($u)->get('/ventas?numero_factura=FACT-000002')->assertOk()->assertDontSee('FACT-000002');
    }
}
