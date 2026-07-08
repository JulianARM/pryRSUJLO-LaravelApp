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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marca_id')->constrained('marcas')->restrictOnDelete();
            $table->foreignId('modelo_vehiculo_id')->constrained('modelos_vehiculo')->restrictOnDelete();
            $table->foreignId('tipo_vehiculo_id')->constrained('tipos_vehiculo')->restrictOnDelete();
            $table->foreignId('color_vehiculo_id')->constrained('colores_vehiculo')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('code', 30)->unique();
            $table->string('placa', 10)->unique();
            $table->unsignedSmallInteger('anio');
            $table->decimal('capacidad_carga', 10, 2);
            $table->decimal('capacidad_combustible', 10, 2);
            $table->decimal('capacidad_compactacion', 10, 2);
            $table->unsignedSmallInteger('capacidad_personas');
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
