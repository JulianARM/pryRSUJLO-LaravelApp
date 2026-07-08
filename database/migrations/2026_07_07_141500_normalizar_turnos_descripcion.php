<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('turnos') && Schema::hasColumn('turnos', 'description') && ! Schema::hasColumn('turnos', 'descripcion')) {
            DB::statement('ALTER TABLE turnos CHANGE description descripcion varchar(255) NULL');
        }
    }

    public function down(): void
    {
        // Cambio de compatibilidad para bases existentes del proyecto.
    }
};
