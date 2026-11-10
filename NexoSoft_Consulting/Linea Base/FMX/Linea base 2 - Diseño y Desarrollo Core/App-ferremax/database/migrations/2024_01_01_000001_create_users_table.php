<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_usuario', 60)->unique();
            $table->string('password');
            $table->enum('rol', ['admin', 'vendedor'])->default('vendedor');
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps(); // created_at sirve como fecha_creacion
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
