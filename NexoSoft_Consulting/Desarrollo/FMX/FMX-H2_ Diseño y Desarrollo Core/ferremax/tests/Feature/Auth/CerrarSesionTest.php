<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del CUS-02 Cerrar sesión (FMX_CU2).
 */
class CerrarSesionTest extends TestCase
{
    use RefreshDatabase;

    // ── Flujo básico ───────────────────────────────────────────────────────

    public function test_salir_pide_confirmacion(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertSee('modal-cerrar-sesion')
            ->assertSee('¿Desea cerrar su sesión?')
            ->assertSee('Cancelar');
    }

    public function test_cerrar_sesion_redirige_al_login_con_mensaje(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', LoginController::MSG_LOGOUT);

        $this->assertGuest();

        $this->get(route('login'))->assertSee(LoginController::MSG_LOGOUT);
    }

    public function test_iniciar_sesion_crea_la_cookie_de_sesion_iniciada(): void
    {
        User::factory()->create(['nombre_usuario' => 'vendedor1', 'password' => 'vendedor123']);

        $this->post('/login', ['nombre_usuario' => 'vendedor1', 'password' => 'vendedor123'])
            ->assertCookie(LoginController::COOKIE_SESION);
    }

    public function test_cerrar_sesion_borra_la_cookie_de_sesion_iniciada(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertCookieExpired(LoginController::COOKIE_SESION);
    }

    // ── Precondición 4.1 / post condición 5.2 / FA 3.2.3 ───────────────────

    public function test_sin_sesion_no_se_puede_cerrar_sesion(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_tras_cerrar_sesion_las_rutas_protegidas_redirigen_al_login(): void
    {
        $this->actingAs(User::factory()->create())->post(route('logout'));

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    // ── FA 3.2.2 Expiración de la sesión por inactividad ───────────────────

    public function test_la_sesion_dura_30_minutos(): void
    {
        $this->assertSame(30, (int) config('session.lifetime'));
    }

    public function test_sesion_expirada_avisa_en_el_login(): void
    {
        // Navegador con la cookie de sesión iniciada, pero la sesión ya no existe
        $this->withCookie(LoginController::COOKIE_SESION, '1')
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('expirada', LoginController::MSG_EXPIRADA)
            ->assertCookieExpired(LoginController::COOKIE_SESION);

        $this->get(route('login'))->assertSee(LoginController::MSG_EXPIRADA);
    }

    public function test_visitante_nuevo_no_ve_aviso_de_expiracion(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionMissing('expirada');
    }
}
