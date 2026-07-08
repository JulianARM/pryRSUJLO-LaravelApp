<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\CambioProgramacion;
use App\Models\MotivoCambio;
use App\Models\Programacion;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Avance07FeatureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_daily_programming_is_available(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $schedule = Programacion::firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard', ['date' => $schedule->fecha_programada->format('Y-m-d')]))
            ->assertOk()
            ->assertSee('Dashboard General')
            ->assertSee('Total de programaciones')
            ->assertSee('Programaciones del')
            ->assertSee('Ver Detalles');
    }

    public function test_dashboard_validates_attendance_for_programmed_personnel(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $schedule = Programacion::with('helpers')->firstOrFail();
        $schedule->update(['fecha_programada' => today()->toDateString()]);

        $personIds = collect([$schedule->conductor_id])
            ->merge($schedule->helpers->pluck('id'))
            ->filter()
            ->values();

        Asistencia::whereIn('personal_id', $personIds)
            ->where('turno_id', $schedule->turno_id)
            ->whereDate('fecha_asistencia', today()->toDateString())
            ->delete();

        $this->actingAs($user)
            ->get(route('dashboard', ['date' => today()->toDateString(), 'turno_id' => $schedule->turno_id]))
            ->assertOk()
            ->assertSee('No registra entrada presente para el turno.');
    }

    public function test_change_reason_can_be_created(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();

        $this->actingAs($user)
            ->post(route('motivos-cambio.store'), [
                'name' => 'Mantenimiento correctivo',
                'descripcion' => 'Cambio por incidencia operativa.',
                'activo' => 1,
            ])
            ->assertRedirect(route('motivos-cambio.index'));

        $this->assertDatabaseHas('motivos_cambio', ['name' => 'Mantenimiento correctivo']);
    }

    public function test_mass_shift_change_registers_history_and_can_be_reverted(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $schedule = Programacion::with('helpers')->where('status', '!=', Programacion::STATUS_FINALIZED)->firstOrFail();
        $oldTurnoId = $schedule->turno_id;
        $newTurno = Turno::whereKeyNot($schedule->turno_id)->firstOrFail();
        $reason = MotivoCambio::create([
            'name' => 'Cambio operativo de prueba',
            'descripcion' => 'Motivo usado por pruebas.',
            'activo' => true,
        ]);

        Programacion::whereDate('fecha_programada', $schedule->fecha_programada)
            ->where('zona_id', $schedule->zona_id)
            ->whereKeyNot($schedule->id)
            ->delete();

        $this->actingAs($user)
            ->post(route('cambios-programacion.mass.store'), [
                'fecha_inicio' => $schedule->fecha_programada->format('Y-m-d'),
                'fecha_fin' => $schedule->fecha_programada->format('Y-m-d'),
                'zona_id' => $schedule->zona_id,
                'tipo_cambio' => 'shift',
                'turno_id' => $newTurno->id,
                'motivo_cambio_id' => $reason->id,
                'detail' => 'Ajuste de turno por prueba automatizada.',
                'confirm_mass_change' => 1,
            ])
            ->assertRedirect(route('cambios-programacion.index'));

        $change = CambioProgramacion::where('programacion_id', $schedule->id)
            ->where('motivo_cambio_id', $reason->id)
            ->where('tipo_cambio', 'shift')
            ->where('action', 'mass_change')
            ->firstOrFail();

        $this->assertSame($newTurno->id, $schedule->fresh()->turno_id);

        $this->actingAs($user)
            ->delete(route('cambios-programacion.destroy', $change))
            ->assertRedirect(route('cambios-programacion.index'));

        $this->assertSame($oldTurnoId, $schedule->fresh()->turno_id);
        $this->assertDatabaseMissing('cambios_programacion', ['id' => $change->id]);
    }
    public function test_paginated_module_second_pages_are_available(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();

        $routes = [
            'cambios-programacion.index',
            'programaciones.index',
            'motivos-cambio.index',
            'asistencias.index',
            'contratos.index',
            'vacaciones.index',
            'grupos-personal.index',
            'personal.index',
            'tipos-personal.index',
            'vehiculos.index',
            'tipos-vehiculo.index',
            'modelos-vehiculo.index',
            'marcas.index',
            'colores-vehiculo.index',
            'turnos.index',
            'zonas.index',
            'feriados.index',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName, ['page' => 2]))
                ->assertOk();
        }
    }
}
