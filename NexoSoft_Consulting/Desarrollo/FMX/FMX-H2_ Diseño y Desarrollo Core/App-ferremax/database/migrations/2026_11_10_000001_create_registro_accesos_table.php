<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SC-09: auditoría de accesos (CU01 Iniciar sesión, CU02 Cerrar sesión)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_accesos', function (Blueprint $table) {
            $table->id();
            // En un intento fallido el usuario puede no existir, por eso es nullable.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre_usuario_intentado', 60);
            $table->enum('evento', ['login_exitoso', 'login_fallido', 'logout', 'expiracion']);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('fecha_hora')->useCurrent();

            $table->index('user_id');
            $table->index('fecha_hora');
            $table->index('evento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_accesos');
    }
};
