<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AsistenciaAutomationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_attendance_type_is_assigned_automatically(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $employee = Personal::where('activo', true)->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('asistencias.store'), [
                'personal_id' => $employee->id,
                'fecha_asistencia' => '2026-06-09',
                'hora_asistencia' => '08:00',
                'status' => Asistencia::STATUS_PRESENT,
                'notes' => null,
            ])
            ->assertOk();

        $this->assertDatabaseHas('asistencias', [
            'personal_id' => $employee->id,
            'fecha_asistencia' => '2026-06-09',
            'type' => Asistencia::TYPE_IN,
        ]);

        $this->actingAs($user)
            ->postJson(route('asistencias.store'), [
                'personal_id' => $employee->id,
                'fecha_asistencia' => '2026-06-09',
                'hora_asistencia' => '17:00',
                'status' => Asistencia::STATUS_PRESENT,
                'notes' => null,
            ])
            ->assertOk();

        $this->assertDatabaseHas('asistencias', [
            'personal_id' => $employee->id,
            'fecha_asistencia' => '2026-06-09',
            'type' => Asistencia::TYPE_OUT,
        ]);
    }
}
