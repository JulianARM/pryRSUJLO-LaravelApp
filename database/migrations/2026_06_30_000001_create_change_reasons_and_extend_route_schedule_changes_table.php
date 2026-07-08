<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivos_cambio', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('cambios_programacion', function (Blueprint $table) {
            $table->foreignId('usuario_id')->nullable()->after('programacion_id')->constrained('users')->nullOnDelete();
            $table->foreignId('motivo_cambio_id')->nullable()->after('usuario_id')->constrained('motivos_cambio')->nullOnDelete();
            $table->uuid('lote_uuid')->nullable()->after('motivo_cambio_id')->index();
            $table->string('tipo_cambio', 40)->nullable()->after('action')->index();
            $table->text('detail')->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('cambios_programacion', function (Blueprint $table) {
            $table->dropConstrainedForeignId('usuario_id');
            $table->dropConstrainedForeignId('motivo_cambio_id');
            $table->dropColumn(['lote_uuid', 'tipo_cambio', 'detail']);
        });

        Schema::dropIfExists('motivos_cambio');
    }
};
