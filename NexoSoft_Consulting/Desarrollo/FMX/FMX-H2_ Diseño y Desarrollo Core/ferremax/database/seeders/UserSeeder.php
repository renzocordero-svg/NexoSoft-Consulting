<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios de prueba para el entorno de desarrollo.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['nombre_usuario' => 'admin'],
            ['password' => 'admin123', 'rol' => User::ROL_ADMIN, 'activo' => true]
        );

        User::firstOrCreate(
            ['nombre_usuario' => 'vendedor1'],
            ['password' => 'vendedor123', 'rol' => User::ROL_VENDEDOR, 'activo' => true]
        );

        // Para probar el flujo alternativo «Cuenta inactiva» del CUS-01
        User::firstOrCreate(
            ['nombre_usuario' => 'inactivo'],
            ['password' => 'inactivo123', 'rol' => User::ROL_VENDEDOR, 'activo' => false]
        );
    }
}
