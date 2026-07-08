<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_id')->constrained('personal')->restrictOnDelete();
            $table->foreignId('turno_id')->constrained('turnos')->restrictOnDelete();
            $table->date('fecha_asistencia');
            $table->time('hora_asistencia');
            $table->dateTime('registrado_en');
            $table->string('type', 20);
            $table->string('status', 20);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['personal_id', 'fecha_asistencia', 'type']);
            $table->index(['fecha_asistencia', 'personal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
