<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Pruebas del CUS-01 Iniciar sesión (FMX_CU1).
 */
class IniciarSesionTest extends TestCase
{
    use RefreshDatabase;

    private function vendedor(array $atributos = []): User
    {
        return User::factory()->create($atributos + [
            'nombre_usuario' => 'vendedor1',
            'password'       => 'vendedor123',
        ]);
    }

    private function intentarLogin(string $usuario, string $password)
    {
        return $this->from('/login')->post('/login', [
            'nombre_usuario' => $usuario,
            'password'       => $password,
        ]);
    }

    // ── Flujo básico ───────────────────────────────────────────────────────

    public function test_muestra_la_ventana_de_inicio_de_sesion(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Nombre de usuario')
            ->assertSee('Contraseña')
            ->assertSee('Ingresar');
    }

    public function test_usuario_activo_inicia_sesion_y_ve_la_bienvenida(): void
    {
        $usuario = $this->vendedor();

        $this->intentarLogin('vendedor1', 'vendedor123')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Bienvenido, vendedor1');
    }

    public function test_la_contrasena_se_guarda_hasheada(): void
    {
        $usuario = $this->vendedor();

        $this->assertNotSame('vendedor123', $usuario->getRawOriginal('password'));
    }

    public function test_un_login_exitoso_reinicia_el_contador_de_intentos(): void
    {
        $this->vendedor();

        $this->intentarLogin('vendedor1', 'mala');
        $this->intentarLogin('vendedor1', 'mala');
        $this->intentarLogin('vendedor1', 'vendedor123');

        $this->assertSame(0, RateLimiter::attempts('vendedor1|127.0.0.1'));
    }

    public function test_el_menu_muestra_solo_las_opciones_del_rol(): void
    {
        Route::middleware('web')->get('/solo-admin', fn () => 'ok')->name('solo-admin');
        app('router')->getRoutes()->refreshNameLookups();

        config(['menu' => [
            ['titulo' => 'Inicio', 'ruta' => 'dashboard', 'icono' => 'bi-house', 'roles' => ['admin', 'vendedor']],
            ['titulo' => 'Opción de admin', 'ruta' => 'solo-admin', 'icono' => 'bi-gear', 'roles' => ['admin']],
            ['titulo' => 'Opción sin ruta', 'ruta' => 'no-existe', 'icono' => 'bi-x', 'roles' => ['admin', 'vendedor']],
        ]]);

        $this->actingAs($this->vendedor())->get(route('dashboard'))
            ->assertSee('Inicio')
            ->assertDontSee('Opción de admin')
            ->assertDontSee('Opción sin ruta');

        $this->actingAs(User::factory()->admin()->create())->get(route('dashboard'))
            ->assertSee('Inicio')
            ->assertSee('Opción de admin')
            ->assertDontSee('Opción sin ruta');
    }

    // ── Precondición 4.2: sin sesión abierta ───────────────────────────────

    public function test_usuario_con_sesion_abierta_es_enviado_al_inicio(): void
    {
        $this->actingAs($this->vendedor())
            ->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_sin_sesion_no_se_accede_al_inicio(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    // ── FA 3.2.1 Credenciales incorrectas ──────────────────────────────────

    public function test_contrasena_incorrecta_muestra_mensaje_generico_y_conserva_el_usuario(): void
    {
        $this->vendedor();

        $this->intentarLogin('vendedor1', 'incorrecta')
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login' => LoginController::MSG_CREDENCIALES])
            ->assertSessionHasInput('nombre_usuario', 'vendedor1')
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    public function test_usuario_inexistente_muestra_el_mismo_mensaje(): void
    {
        $this->intentarLogin('noexiste', 'loquesea')
            ->assertSessionHasErrors(['login' => LoginController::MSG_CREDENCIALES]);

        $this->assertGuest();
    }

    // ── FA 3.2.2 Demasiados intentos fallidos ──────────────────────────────

    public function test_tras_tres_fallos_se_bloquea_incluso_con_la_contrasena_correcta(): void
    {
        $this->vendedor();

        for ($i = 0; $i < LoginController::MAX_INTENTOS; $i++) {
            $this->intentarLogin('vendedor1', 'incorrecta');
        }

        $this->intentarLogin('vendedor1', 'vendedor123')
            ->assertSessionHasErrors(['login' => LoginController::MSG_BLOQUEO]);

        $this->assertGuest();
    }

    // ── FA 3.2.3 Cuenta inactiva ───────────────────────────────────────────

    public function test_cuenta_inactiva_no_inicia_sesion(): void
    {
        $this->vendedor(['activo' => false]);

        $this->intentarLogin('vendedor1', 'vendedor123')
            ->assertSessionHasErrors(['login' => LoginController::MSG_INACTIVA]);

        $this->assertGuest();
    }

    // ── FA 3.2.4 Campos obligatorios vacíos ────────────────────────────────

    public function test_campos_vacios_muestran_validacion_por_campo(): void
    {
        $this->intentarLogin('', '')
            ->assertSessionHasErrors([
                'nombre_usuario' => 'Ingrese su nombre de usuario.',
                'password'       => 'Ingrese su contraseña.',
            ]);

        $this->assertGuest();
    }
}
