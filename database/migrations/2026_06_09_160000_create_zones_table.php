<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('departamento', 80);
            $table->string('provincia', 80);
            $table->string('distrito', 80);
            $table->text('descripcion')->nullable();
            $table->decimal('residuos_promedio_kg', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonas');
    }
};
