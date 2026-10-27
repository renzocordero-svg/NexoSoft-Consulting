<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
