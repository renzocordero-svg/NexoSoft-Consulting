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
}
