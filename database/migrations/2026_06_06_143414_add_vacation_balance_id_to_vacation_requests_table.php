<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_vacaciones', function (Blueprint $table) {
            $table->foreignId('saldo_vacacion_id')
                ->nullable()
                ->after('personal_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_vacaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('saldo_vacacion_id');
        });
    }
};
