<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos_personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->restrictOnDelete();
            $table->foreignId('zona_id')->constrained('zonas')->restrictOnDelete();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->restrictOnDelete();
            $table->foreignId('conductor_id')->constrained('personal')->restrictOnDelete();
            $table->string('name', 120)->unique();
            $table->json('dias_semana');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('grupo_personal_ayudantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_personal_id')->constrained('grupos_personal')->cascadeOnDelete();
            $table->foreignId('personal_id')->constrained('personal')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['grupo_personal_id', 'personal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_personal_ayudantes');
        Schema::dropIfExists('grupos_personal');
    }
};
