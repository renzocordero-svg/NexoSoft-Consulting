<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Cu01Test extends TestCase
{
    use RefreshDatabase;

    public function test_login_y_bloqueo_por_tres_intentos(): void
    {
        $this->usuario();
        $this->get('/login')->assertOk()->assertSee('FERRE');
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', ['nombre_usuario' => 'admin', 'password' => 'error'])->assertSessionHasErrors('nombre_usuario');
        }
        $this->post('/login', ['nombre_usuario' => 'admin', 'password' => 'admin123'])->assertSessionHasErrors('nombre_usuario');
        $this->travel(301)->seconds();
        $this->post('/login', ['nombre_usuario' => 'admin', 'password' => 'admin123'])->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_dashboard_y_permisos(): void
    {
        $u = $this->usuario('vendedor', 'vendedor1');
        $this->actingAs($u)->get('/')->assertOk()->assertSee('Bienvenido, vendedor1')->assertDontSee('GestiÃ³n de accesos');
        foreach (['productos', 'usuarios', 'reportes', 'movimientos'] as $url) {
            if (Route::has($url.'.index')) {
                $this->get('/'.$url)->assertForbidden();
            }
        }
    }

    public function test_cuenta_inactiva_no_puede_acceder(): void
    {
        $u = $this->usuario();
        $u->update(['activo' => false]);
        $this->post('/login', ['nombre_usuario' => 'admin', 'password' => 'admin123'])->assertSessionHasErrors();
        $this->actingAs($u)->get('/')->assertRedirect('/login');
    }

    // SC-17: sin "Mantener sesión iniciada"; aunque se envíe remember no se crea la cookie de recordatorio.
    public function test_login_no_ofrece_mantener_sesion_iniciada(): void
    {
        $u = $this->usuario();
        $this->get('/login')->assertOk()->assertDontSee('Mantener sesión iniciada')->assertDontSee('name="remember"', false);
        $this->post('/login', ['nombre_usuario' => 'admin', 'password' => 'admin123', 'remember' => 'on'])
            ->assertRedirect('/')
            ->assertCookieMissing(Auth::guard()->getRecallerName());
        $this->assertNull($u->fresh()->remember_token);
    }

    // SC-17: con la contraseña correcta la cuenta inactiva recibe su mensaje y no consume intentos.
    public function test_cuenta_inactiva_con_contrasena_correcta_ve_su_mensaje_sin_consumir_intentos(): void
    {
        $u = $this->usuario();
        $u->update(['activo' => false]);
        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['nombre_usuario' => 'admin', 'password' => 'admin123'])
                ->assertSessionHasErrors(['nombre_usuario' => 'Tu cuenta está inactiva. Comunícate con el administrador.']);
        }
        $this->assertGuest();
        $this->assertSame(0, RateLimiter::attempts('admin|127.0.0.1'));
        $u->update(['activo' => true]);
        $this->post('/login', ['nombre_usuario' => 'admin', 'password' => 'admin123'])->assertRedirect('/');
        $this->assertAuthenticatedAs($u);
    }

    // SC-17: con la contraseña incorrecta no se revela que la cuenta existe y está inactiva.
    public function test_cuenta_inactiva_con_contrasena_incorrecta_recibe_mensaje_generico(): void
    {
        $u = $this->usuario();
        $u->update(['activo' => false]);
        $this->post('/login', ['nombre_usuario' => 'admin', 'password' => 'error'])
            ->assertSessionHasErrors(['nombre_usuario' => 'Credenciales incorrectas. Te quedan 2 intento(s).']);
        $this->assertStringNotContainsString('inactiva', session('errors')->first('nombre_usuario'));
        $this->assertSame(1, RateLimiter::attempts('admin|127.0.0.1'));
        $this->post('/login', ['nombre_usuario' => 'noexiste', 'password' => 'error'])
            ->assertSessionHasErrors(['nombre_usuario' => 'Credenciales incorrectas. Te quedan 2 intento(s).']);
        $this->assertGuest();
    }

    public function test_todas_las_vistas_disponibles_renderizan(): void
    {
        $u = $this->usuario();
        $p = $this->producto();
        $this->actingAs($u);
        $routes = ['dashboard' => [], 'productos.index' => [], 'productos.create' => [], 'productos.edit' => ['producto' => $p->id], 'usuarios.index' => [], 'usuarios.create' => [], 'usuarios.edit' => ['usuario' => $u->id], 'ventas.create' => [], 'ventas.index' => [], 'movimientos.index' => [], 'perfil.edit' => [], 'reportes.index' => []];
        foreach ($routes as $name => $args) {
            if (Route::has($name)) {
                $this->get(route($name, $args))->assertOk();
            }
        }
    }
}
