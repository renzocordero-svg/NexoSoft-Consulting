<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['nombre_usuario' => 'admin'],
            ['password' => Hash::make('admin123'), 'rol' => 'admin', 'activo' => true]
        );

        User::firstOrCreate(
            ['nombre_usuario' => 'vendedor1'],
            ['password' => Hash::make('vendedor123'), 'rol' => 'vendedor', 'activo' => true]
        );
    }
}
