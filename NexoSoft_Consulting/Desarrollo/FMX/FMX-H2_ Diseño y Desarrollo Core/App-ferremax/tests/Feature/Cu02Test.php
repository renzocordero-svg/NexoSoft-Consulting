<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cu02Test extends TestCase
{
    use RefreshDatabase;

    public function test_cierre_de_sesion(): void
    {
        $this->actingAs($this->usuario())->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/')->assertRedirect('/login');
    }

    public function test_logout_no_acepta_get(): void
    {
        $this->get('/logout')->assertStatus(405);
    }

    // SC-17 (CU2 3.2.2): la sesión vence tras 30 minutos sin actividad y no sobrevive al cierre del navegador.
    public function test_sesion_expira_tras_30_minutos_de_inactividad(): void
    {
        $this->assertSame(30, config('session.lifetime'));
        $this->assertTrue(config('session.expire_on_close'));
        $this->usuario();
        $this->post('/login', ['nombre_usuario' => 'admin', 'password' => 'admin123'])->assertRedirect('/');
        $this->withCookie(config('session.cookie'), session()->getId());

        $this->travel(29)->minutes();
        $this->nuevaPeticion()->get('/')->assertOk();

        $this->travel(31)->minutes();
        $this->nuevaPeticion()->get('/')->assertRedirect('/login');
        $this->assertGuest();
    }

    // Simula la siguiente petición del navegador: solo viaja la cookie de sesión,
    // sin el usuario ni los datos que el test deja en memoria entre peticiones.
    private function nuevaPeticion(): static
    {
        $this->app['auth']->forgetGuards();
        $this->app['session']->driver()->flush();

        return $this;
    }
}
