<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Cu08Test extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_validacion_y_autoproteccion(): void
    {
        $u = $this->usuario();
        $this->actingAs($u);
        $this->post('/usuarios', ['nombre_usuario' => 'nuevo', 'rol' => 'vendedor', 'activo' => 'on', 'password' => 'seguro123', 'password_confirmation' => 'seguro123'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['nombre_usuario' => 'nuevo', 'activo' => true]);
        $this->put("/usuarios/{$u->id}", ['nombre_usuario' => 'admin', 'rol' => 'vendedor', 'activo' => 'on'])->assertSessionHas('error');
        $this->assertSame('admin', $u->fresh()->rol);
        $this->delete("/usuarios/{$u->id}")->assertSessionHas('error');
        $this->post('/usuarios', ['nombre_usuario' => 'malo', 'rol' => 'admin', 'password' => 'abcdefgh', 'password_confirmation' => 'abcdefgh'])->assertSessionHasErrors('password');
    }

    public function test_cambio_contrasena_y_desactivacion(): void
    {
        $u = $this->usuario();
        $otro = $this->usuario('vendedor', 'otro');
        $this->actingAs($u);
        $this->put('/perfil', ['actual' => 'admin123', 'password' => 'nueva123', 'password_confirmation' => 'nueva123'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('nueva123', $u->fresh()->password));
        $this->delete("/usuarios/{$otro->id}");
        $this->assertFalse($otro->fresh()->activo);
        $this->delete("/usuarios/{$otro->id}");
        $this->assertTrue($otro->fresh()->activo);
    }
}
