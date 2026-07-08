<?php

namespace Tests\Feature;

use App\Models\SaldoVacacion;
use App\Models\SolicitudVacacion;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VacationApprovalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_approving_vacation_discounts_dias_disponibles(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $vacation = SolicitudVacacion::where('status', SolicitudVacacion::STATUS_PENDING)->firstOrFail();
        $balance = SaldoVacacion::where('personal_id', $vacation->personal_id)
            ->where('anio', $vacation->fecha_inicio->year)
            ->firstOrFail();

        $availableBefore = $balance->dias_disponibles;

        $this->actingAs($user)
            ->putJson(route('vacaciones.approve', $vacation))
            ->assertOk();

        $vacation->refresh();
        $balance->refresh();

        $this->assertSame(SolicitudVacacion::STATUS_APPROVED, $vacation->status);
        $this->assertSame($availableBefore - $vacation->dias_solicitados, $balance->dias_disponibles);
        $this->assertSame($balance->dias_disponibles, $vacation->dias_restantes);
    }

    public function test_deleting_approved_vacation_restores_days_and_removes_request(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $vacation = SolicitudVacacion::where('status', SolicitudVacacion::STATUS_PENDING)->firstOrFail();

        $this->actingAs($user)->putJson(route('vacaciones.approve', $vacation))->assertOk();

        $vacation->refresh();
        $balance = $vacation->vacationBalance;
        $availableAfterApproval = $balance->dias_disponibles;

        $this->actingAs($user)
            ->deleteJson(route('vacaciones.destroy', $vacation))
            ->assertOk();

        $balance->refresh();

        $this->assertDatabaseMissing('solicitudes_vacaciones', ['id' => $vacation->id]);
        $this->assertSame($availableAfterApproval + $vacation->dias_solicitados, $balance->dias_disponibles);
    }
}
