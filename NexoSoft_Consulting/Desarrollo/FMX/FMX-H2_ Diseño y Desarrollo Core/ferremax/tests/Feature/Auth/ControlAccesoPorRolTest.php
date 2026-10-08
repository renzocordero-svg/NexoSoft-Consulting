<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Pruebas del middleware role:<roles> (paquete Seguridad).
 */
class ControlAccesoPorRolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:admin'])->get('/prueba/admin', fn () => 'admin ok');
        Route::middleware(['web', 'auth', 'role:admin,vendedor'])->get('/prueba/ambos', fn () => 'ambos ok');
    }

    public function test_admin_entra_a_ruta_de_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/prueba/admin')
            ->assertOk();
    }

    public function test_vendedor_no_entra_a_ruta_de_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/prueba/admin')
            ->assertForbidden();
    }

    public function test_ruta_con_varios_roles_admite_a_ambos(): void
    {
        $this->actingAs(User::factory()->create())->get('/prueba/ambos')->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get('/prueba/ambos')->assertOk();
    }

    public function test_sin_sesion_redirige_al_login(): void
    {
        $this->get('/prueba/admin')->assertRedirect(route('login'));
    }
}
