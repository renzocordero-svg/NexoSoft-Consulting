<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            // Costo capturado al momento de la venta (inmutable), igual criterio que precio_unitario_momento.
            $table->decimal('costo_unitario_momento', 10, 2)->default(0)->after('precio_unitario_momento');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropColumn('costo_unitario_momento');
        });
    }
};
