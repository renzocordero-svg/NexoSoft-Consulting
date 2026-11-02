<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Venta::exists()) {
            $this->command?->warn('Ya hay ventas. Se omitieron los datos de demostración.');

            return;
        }
        $this->call(DatabaseSeeder::class);
        $users = User::orderBy('id')->get();
        $productos = Producto::orderBy('id')->take(3)->get();
        foreach ($productos as $i => $p) {
            $v = app(VentaService::class)->procesarVenta([['producto_id' => $p->id, 'cantidad' => $i + 1]], $users[$i % $users->count()]->id);
            $fecha = now()->subDays($i);
            $v->update(['fecha_hora' => $fecha]);
            $v->factura->update(['fecha_emision' => $fecha]);
        }
    }
}
