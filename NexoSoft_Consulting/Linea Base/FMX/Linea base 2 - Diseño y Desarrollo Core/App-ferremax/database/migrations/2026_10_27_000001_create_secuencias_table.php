<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secuencias', function (Blueprint $table) {
            $table->string('nombre')->primary();
            $table->unsignedBigInteger('ultimo')->default(0);
        });
        $max = 0;
        foreach (DB::table('facturas')->pluck('numero_factura') as $numero) {
            if (preg_match('/^FACT-(\d+)$/', $numero, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }
        DB::table('secuencias')->insert(['nombre' => 'factura', 'ultimo' => $max]);
    }

    public function down(): void
    {
        Schema::dropIfExists('secuencias');
    }
};
