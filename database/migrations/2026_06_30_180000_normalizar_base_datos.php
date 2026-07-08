<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->tables() as $old => $new) {
            $this->renameTableIfNeeded($old, $new);
        }

        foreach ($this->columns() as $table => $columns) {
            foreach ($columns as $column) {
                $this->renameColumnIfNeeded($table, $column[0], $column[1], $column[2]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // La normalizacion del esquema es un cambio estructural definitivo del proyecto.
    }

    private function tables(): array
    {
        return [
            'brands' => 'marcas',
            'brand_models' => 'modelos_vehiculo',
            'vehicle_colors' => 'colores_vehiculo',
            'vehicle_types' => 'tipos_vehiculo',
            'vehicles' => 'vehiculos',
            'vehicle_images' => 'imagenes_vehiculo',
            'staff_types' => 'tipos_personal',
            'employees' => 'personal',
            'contracts' => 'contratos',
            'shifts' => 'turnos',
            'attendances' => 'asistencias',
            'vacation_balances' => 'saldos_vacaciones',
            'vacation_requests' => 'solicitudes_vacaciones',
            'zones' => 'zonas',
            'zone_coordinates' => 'coordenadas_zona',
            'holidays' => 'feriados',
            'personnel_groups' => 'grupos_personal',
            'personnel_group_helpers' => 'grupo_personal_ayudantes',
            'route_schedules' => 'programaciones',
            'route_schedule_helpers' => 'programacion_ayudantes',
            'route_schedule_changes' => 'cambios_programacion',
            'change_reasons' => 'motivos_cambio',
        ];
    }

    private function columns(): array
    {
        return [
            'marcas' => [
                ['description', 'descripcion', 'varchar(255) NULL'],
                ['logo_path', 'ruta_logo', 'varchar(255) NULL'],
            ],
            'modelos_vehiculo' => [
                ['brand_id', 'marca_id', 'bigint unsigned NOT NULL'],
                ['description', 'descripcion', 'varchar(255) NULL'],
            ],
            'colores_vehiculo' => [['description', 'descripcion', 'varchar(255) NULL']],
            'tipos_vehiculo' => [['description', 'descripcion', 'varchar(255) NULL']],
            'vehiculos' => [
                ['brand_id', 'marca_id', 'bigint unsigned NOT NULL'],
                ['brand_model_id', 'modelo_vehiculo_id', 'bigint unsigned NOT NULL'],
                ['vehicle_type_id', 'tipo_vehiculo_id', 'bigint unsigned NOT NULL'],
                ['vehicle_color_id', 'color_vehiculo_id', 'bigint unsigned NOT NULL'],
                ['plate', 'placa', 'varchar(10) NOT NULL'],
                ['year', 'anio', 'smallint unsigned NOT NULL'],
                ['load_capacity', 'capacidad_carga', 'decimal(10,2) NOT NULL'],
                ['fuel_capacity', 'capacidad_combustible', 'decimal(10,2) NOT NULL'],
                ['compaction_capacity', 'capacidad_compactacion', 'decimal(10,2) NOT NULL'],
                ['person_capacity', 'capacidad_personas', 'smallint unsigned NOT NULL'],
                ['description', 'descripcion', 'varchar(255) NULL'],
                ['is_active', 'activo', 'tinyint(1) NOT NULL'],
            ],
            'imagenes_vehiculo' => [
                ['vehicle_id', 'vehiculo_id', 'bigint unsigned NOT NULL'],
                ['original_name', 'nombre_original', 'varchar(255) NULL'],
                ['is_profile', 'es_principal', 'tinyint(1) NOT NULL'],
            ],
            'tipos_personal' => [
                ['description', 'descripcion', 'varchar(255) NULL'],
                ['is_system', 'es_sistema', 'tinyint(1) NOT NULL'],
            ],
            'personal' => [
                ['staff_type_id', 'tipo_personal_id', 'bigint unsigned NOT NULL'],
                ['first_names', 'nombres', 'varchar(100) NOT NULL'],
                ['last_names', 'apellidos', 'varchar(100) NOT NULL'],
                ['birth_date', 'fecha_nacimiento', 'date NOT NULL'],
                ['phone', 'telefono', 'varchar(20) NULL'],
                ['license', 'licencia', 'varchar(30) NULL'],
                ['address', 'direccion', 'varchar(255) NOT NULL'],
                ['photo_path', 'ruta_foto', 'varchar(255) NULL'],
                ['is_active', 'activo', 'tinyint(1) NOT NULL'],
            ],
            'contratos' => [
                ['employee_id', 'personal_id', 'bigint unsigned NOT NULL'],
                ['contract_type', 'tipo_contrato', 'varchar(20) NOT NULL'],
                ['start_date', 'fecha_inicio', 'date NOT NULL'],
                ['end_date', 'fecha_fin', 'date NULL'],
                ['salary', 'salario', 'decimal(10,2) NOT NULL'],
                ['trial_period_months', 'meses_periodo_prueba', 'tinyint unsigned NULL'],
                ['position', 'cargo', 'varchar(80) NOT NULL'],
                ['is_active', 'activo', 'tinyint(1) NOT NULL'],
            ],
            'asistencias' => [
                ['employee_id', 'personal_id', 'bigint unsigned NOT NULL'],
                ['shift_id', 'turno_id', 'bigint unsigned NOT NULL'],
                ['attendance_date', 'fecha_asistencia', 'date NOT NULL'],
                ['attendance_time', 'hora_asistencia', 'time NOT NULL'],
                ['registered_at', 'registrado_en', 'datetime NOT NULL'],
            ],
            'saldos_vacaciones' => [
                ['employee_id', 'personal_id', 'bigint unsigned NOT NULL'],
                ['year', 'anio', 'smallint unsigned NOT NULL'],
                ['total_days', 'dias_totales', 'smallint unsigned NOT NULL'],
                ['used_days', 'dias_usados', 'smallint unsigned NOT NULL'],
                ['available_days', 'dias_disponibles', 'smallint unsigned NOT NULL'],
            ],
            'solicitudes_vacaciones' => [
                ['employee_id', 'personal_id', 'bigint unsigned NOT NULL'],
                ['vacation_balance_id', 'saldo_vacacion_id', 'bigint unsigned NULL'],
                ['requested_at', 'fecha_solicitud', 'date NOT NULL'],
                ['start_date', 'fecha_inicio', 'date NOT NULL'],
                ['end_date', 'fecha_fin', 'date NOT NULL'],
                ['days_requested', 'dias_solicitados', 'smallint unsigned NOT NULL'],
                ['remaining_days', 'dias_restantes', 'smallint unsigned NOT NULL'],
            ],
            'zonas' => [
                ['department', 'departamento', 'varchar(80) NOT NULL'],
                ['province', 'provincia', 'varchar(80) NOT NULL'],
                ['district', 'distrito', 'varchar(80) NOT NULL'],
                ['description', 'descripcion', 'text NULL'],
                ['average_waste_kg', 'residuos_promedio_kg', 'decimal(10,2) NOT NULL'],
                ['is_active', 'activo', 'tinyint(1) NOT NULL'],
            ],
            'coordenadas_zona' => [
                ['zone_id', 'zona_id', 'bigint unsigned NOT NULL'],
                ['latitude', 'latitud', 'decimal(10,7) NOT NULL'],
                ['longitude', 'longitud', 'decimal(10,7) NOT NULL'],
                ['sort_order', 'orden', 'smallint unsigned NOT NULL'],
            ],
            'feriados' => [
                ['description', 'descripcion', 'varchar(160) NOT NULL'],
                ['is_active', 'activo', 'tinyint(1) NOT NULL'],
            ],
            'grupos_personal' => [
                ['shift_id', 'turno_id', 'bigint unsigned NOT NULL'],
                ['zone_id', 'zona_id', 'bigint unsigned NOT NULL'],
                ['vehicle_id', 'vehiculo_id', 'bigint unsigned NOT NULL'],
                ['driver_id', 'conductor_id', 'bigint unsigned NOT NULL'],
                ['days_of_week', 'dias_semana', 'longtext NOT NULL'],
                ['is_active', 'activo', 'tinyint(1) NOT NULL'],
            ],
            'grupo_personal_ayudantes' => [
                ['personnel_group_id', 'grupo_personal_id', 'bigint unsigned NOT NULL'],
                ['employee_id', 'personal_id', 'bigint unsigned NOT NULL'],
            ],
            'programaciones' => [
                ['personnel_group_id', 'grupo_personal_id', 'bigint unsigned NULL'],
                ['shift_id', 'turno_id', 'bigint unsigned NOT NULL'],
                ['zone_id', 'zona_id', 'bigint unsigned NOT NULL'],
                ['vehicle_id', 'vehiculo_id', 'bigint unsigned NOT NULL'],
                ['driver_id', 'conductor_id', 'bigint unsigned NOT NULL'],
                ['schedule_date', 'fecha_programada', 'date NOT NULL'],
            ],
            'programacion_ayudantes' => [
                ['route_schedule_id', 'programacion_id', 'bigint unsigned NOT NULL'],
                ['employee_id', 'personal_id', 'bigint unsigned NOT NULL'],
            ],
            'motivos_cambio' => [
                ['description', 'descripcion', 'text NULL'],
                ['is_active', 'activo', 'tinyint(1) NOT NULL'],
            ],
            'cambios_programacion' => [
                ['route_schedule_id', 'programacion_id', 'bigint unsigned NOT NULL'],
                ['user_id', 'usuario_id', 'bigint unsigned NULL'],
                ['change_reason_id', 'motivo_cambio_id', 'bigint unsigned NULL'],
                ['batch_uuid', 'lote_uuid', 'char(36) NULL'],
                ['change_type', 'tipo_cambio', 'varchar(40) NULL'],
                ['description', 'descripcion', 'varchar(255) NOT NULL'],
                ['old_values', 'valores_anteriores', 'longtext NULL'],
                ['new_values', 'valores_nuevos', 'longtext NULL'],
            ],
        ];
    }

    private function renameTableIfNeeded(string $old, string $new): void
    {
        if (Schema::hasTable($old) && ! Schema::hasTable($new)) {
            DB::statement("RENAME TABLE `{$old}` TO `{$new}`");
        }
    }

    private function renameColumnIfNeeded(string $table, string $old, string $new, string $definition): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, $old) && ! Schema::hasColumn($table, $new)) {
            DB::statement("ALTER TABLE `{$table}` CHANGE `{$old}` `{$new}` {$definition}");
        }
    }
};
