<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coordenadas_zona', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->unsignedSmallInteger('orden');
            $table->timestamps();

            $table->unique(['zona_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coordenadas_zona');
    }
};
