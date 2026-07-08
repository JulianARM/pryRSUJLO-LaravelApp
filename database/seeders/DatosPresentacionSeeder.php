<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosPresentacionSeeder extends Seeder
{
    public function run(): void
    {
        $this->normalizarTurnos();
        $this->sembrarImagenesVehiculo();
        $this->sembrarAsistencias();
        $this->sembrarMotivosCambio();
    }

    private function normalizarTurnos(): void
    {
        DB::table('turnos')->where('name', 'Manana')->update(['name' => 'Mañana']);
    }

    private function sembrarImagenesVehiculo(): void
    {
        foreach (DB::table('vehiculos')->select('id', 'code')->get() as $vehiculo) {
            DB::table('imagenes_vehiculo')->updateOrInsert(
                ['vehiculo_id' => $vehiculo->id, 'es_principal' => true],
                [
                    'path' => 'vehiculos/demo/'.strtolower($vehiculo->code).'.jpg',
                    'nombre_original' => strtolower($vehiculo->code).'.jpg',
                    'size' => 125000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function sembrarAsistencias(): void
    {
        $rows = [
            ['12345678', 'Mañana', '2026-06-15', '06:08:00', 'in', 'present', 'Ingreso registrado en ruta Centro.'],
            ['12345678', 'Mañana', '2026-06-15', '14:02:00', 'out', 'present', 'Salida registrada sin incidencias.'],
            ['80000019', 'Mañana', '2026-06-15', '06:05:00', 'in', 'present', null],
            ['10000001', 'Mañana', '2026-06-15', '06:12:00', 'in', 'present', null],
            ['70000008', 'Tarde', '2026-06-19', '14:06:00', 'in', 'present', 'Unidad asignada correctamente.'],
            ['10000002', 'Tarde', '2026-06-19', '14:15:00', 'in', 'present', null],
        ];

        foreach ($rows as [$dni, $turno, $fecha, $hora, $tipo, $estado, $notas]) {
            $personalId = DB::table('personal')->where('dni', $dni)->value('id');
            $turnoId = DB::table('turnos')->where('name', $turno)->value('id');

            if (! $personalId || ! $turnoId) {
                continue;
            }

            DB::table('asistencias')->updateOrInsert(
                ['personal_id' => $personalId, 'fecha_asistencia' => $fecha, 'type' => $tipo],
                [
                    'turno_id' => $turnoId,
                    'hora_asistencia' => $hora,
                    'registrado_en' => $fecha.' '. $hora,
                    'status' => $estado,
                    'notes' => $notas,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function sembrarMotivosCambio(): void
    {
        DB::table('motivos_cambio')->upsert([
            ['name' => 'Imprevistos operativos', 'descripcion' => 'Cambio realizado por incidencias de operación o disponibilidad.', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mantenimiento preventivo', 'descripcion' => 'Cambio por revisión o mantenimiento de una unidad vehicular.', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Apoyo de cobertura', 'descripcion' => 'Cambio para reforzar la cobertura de una zona programada.', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['name'], ['descripcion', 'activo', 'updated_at']);
    }
}
