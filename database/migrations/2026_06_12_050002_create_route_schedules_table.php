<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_personal_id')->nullable()->constrained('grupos_personal')->nullOnDelete();
            $table->foreignId('turno_id')->constrained('turnos')->restrictOnDelete();
            $table->foreignId('zona_id')->constrained('zonas')->restrictOnDelete();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->restrictOnDelete();
            $table->foreignId('conductor_id')->constrained('personal')->restrictOnDelete();
            $table->date('fecha_programada');
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['fecha_programada', 'turno_id']);
        });

        Schema::create('programacion_ayudantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programacion_id')->constrained('programaciones')->cascadeOnDelete();
            $table->foreignId('personal_id')->constrained('personal')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['programacion_id', 'personal_id']);
        });

        Schema::create('cambios_programacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programacion_id')->constrained('programaciones')->cascadeOnDelete();
            $table->string('action', 40);
            $table->string('descripcion', 255);
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios_programacion');
        Schema::dropIfExists('programacion_ayudantes');
        Schema::dropIfExists('programaciones');
    }
};
