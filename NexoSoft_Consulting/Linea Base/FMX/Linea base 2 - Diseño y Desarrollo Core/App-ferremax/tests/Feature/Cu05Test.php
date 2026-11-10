<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cu05Test extends TestCase
{
    use RefreshDatabase;

    public function test_factura_restringida_al_dueno_o_administrador(): void
    {
        $u = $this->usuario('vendedor', 'uno');
        $otro = $this->usuario('vendedor', 'dos');
        $admin = $this->usuario();
        $v = $this->vender($u, $this->producto());
        $this->actingAs($u)->get("/ventas/{$v->id}")->assertOk()->assertSee('FACT-000001')->assertSee('window.print', false);
        $this->actingAs($otro)->get("/ventas/{$v->id}")->assertForbidden();
        $this->actingAs($admin)->get("/ventas/{$v->id}")->assertOk();
        $this->get('/ventas/9999')->assertNotFound();
    }
}
