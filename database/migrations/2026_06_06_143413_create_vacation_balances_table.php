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
        Schema::create('saldos_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_id')->constrained('personal')->cascadeOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->unsignedSmallInteger('dias_totales')->default(30);
            $table->unsignedSmallInteger('dias_usados')->default(0);
            $table->unsignedSmallInteger('dias_disponibles')->default(30);
            $table->timestamps();

            $table->unique(['personal_id', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldos_vacaciones');
    }
};
