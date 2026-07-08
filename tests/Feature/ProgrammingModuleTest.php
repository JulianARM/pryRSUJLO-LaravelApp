<?php

namespace Tests\Feature;

use App\Models\Contrato;
use App\Models\Feriado;
use App\Models\GrupoPersonal;
use App\Models\Personal;
use App\Models\Programacion;
use App\Models\SolicitudVacacion;
use App\Models\TipoPersonal;
use App\Models\User;
use App\Services\ProgramacionMasivaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProgrammingModuleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_programming_indexes_are_available(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();

        $this->actingAs($user)->get(route('feriados.index'))->assertOk()->assertSee('Listado de Feriados');
        $this->actingAs($user)->get(route('grupos-personal.index'))->assertOk()->assertSee('Lista de Grupos de Personal');
        $this->actingAs($user)->get(route('programaciones.index'))->assertOk()->assertSee('Lista de Programaciones');
        $this->actingAs($user)->get(route('programaciones.mass'))->assertOk()->assertSee('Programación Masiva');
        $this->actingAs($user)
            ->get(route('programaciones.mass', ['fecha_inicio' => '2026-08-10', 'fecha_fin' => '2026-08-16']))
            ->assertOk()
            ->assertSee('Grupos a programar');
    }

    public function test_active_holiday_is_skipped_when_route_schedule_has_other_programmable_dates(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();

        Feriado::updateOrCreate(
            ['date' => '2026-07-01'],
            ['descripcion' => 'Feriado de prueba', 'activo' => true]
        );
        Feriado::whereDate('date', '2026-07-02')->delete();
        Programacion::whereBetween('fecha_programada', ['2026-07-01', '2026-07-02'])->delete();

        $this->actingAs($user)
            ->postJson(route('programaciones.validate'), $this->payload($group, '2026-07-01', '2026-07-02', [3, 4]))
            ->assertOk()
            ->assertJsonFragment([
                'available' => true,
                'count' => 1,
            ]);

        $this->actingAs($user)
            ->postJson(route('programaciones.store'), $this->payload($group, '2026-07-01', '2026-07-02', [3, 4]))
            ->assertOk();

        $this->assertDatabaseMissing('programaciones', [
            'grupo_personal_id' => $group->id,
            'fecha_programada' => '2026-07-01',
        ]);
        $this->assertDatabaseHas('programaciones', [
            'grupo_personal_id' => $group->id,
            'fecha_programada' => '2026-07-02',
        ]);
    }

    public function test_mass_route_schedule_loads_all_active_groups_when_shift_filter_is_empty(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();

        $this->actingAs($user)
            ->get(route('programaciones.mass', ['fecha_inicio' => '2026-08-10', 'fecha_fin' => '2026-08-16']))
            ->assertOk()
            ->assertSee('Todos los turnos')
            ->assertSee('Todas las zonas')
            ->assertSee('Filtrar grupos cargados por zona')
            ->assertSeeText('diferentes turnos');

        $this->assertSame(
            GrupoPersonal::where('activo', true)->count(),
            app(ProgramacionMasivaService::class)->groups()->count()
        );
    }

    public function test_route_schedule_can_be_generated_after_availability_validation(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();

        Feriado::whereDate('date', '2026-07-02')->delete();
        Programacion::whereDate('fecha_programada', '2026-07-02')->delete();

        $this->actingAs($user)
            ->postJson(route('programaciones.validate'), $this->payload($group, '2026-07-02', '2026-07-02', [4]))
            ->assertOk()
            ->assertJsonFragment([
                'available' => true,
            ]);

        $this->actingAs($user)
            ->postJson(route('programaciones.store'), $this->payload($group, '2026-07-02', '2026-07-02', [4]))
            ->assertOk();

        $this->assertDatabaseHas('programaciones', [
            'grupo_personal_id' => $group->id,
            'fecha_programada' => '2026-07-02',
            'status' => Programacion::STATUS_SCHEDULED,
        ]);
    }

    public function test_route_schedule_rejects_same_group_same_date_and_shift(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();

        Feriado::whereDate('date', '2026-07-02')->delete();

        $schedule = Programacion::create([
            'grupo_personal_id' => $group->id,
            'turno_id' => $group->turno_id,
            'zona_id' => $group->zona_id,
            'vehiculo_id' => $group->vehiculo_id,
            'conductor_id' => $group->conductor_id,
            'fecha_programada' => '2026-07-02',
            'status' => Programacion::STATUS_SCHEDULED,
        ]);
        $schedule->helpers()->sync($group->helpers->pluck('id')->all());

        $this->actingAs($user)
            ->postJson(route('programaciones.validate'), $this->payload($group, '2026-07-02', '2026-07-02', [4]))
            ->assertStatus(422)
            ->assertJsonFragment([
                'available' => false,
            ]);
    }

    public function test_route_schedule_rejects_same_group_same_date_shift_and_zone(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();

        Feriado::whereDate('date', '2026-09-07')->delete();
        Programacion::whereDate('fecha_programada', '2026-09-07')->delete();

        $schedule = Programacion::create([
            'grupo_personal_id' => $group->id,
            'turno_id' => $group->turno_id,
            'zona_id' => $group->zona_id,
            'vehiculo_id' => $group->vehiculo_id,
            'conductor_id' => $group->conductor_id,
            'fecha_programada' => '2026-09-07',
            'status' => Programacion::STATUS_SCHEDULED,
        ]);
        $schedule->helpers()->sync($group->helpers->pluck('id')->all());

        $response = $this->actingAs($user)
            ->postJson(route('programaciones.validate'), $this->payload($group, '2026-09-07', '2026-09-07', [1]))
            ->assertStatus(422)
            ->assertJsonFragment([
                'available' => false,
            ]);

        $this->assertContains(
            '07/09/2026: el grupo seleccionado ya tiene una programación para este turno y zona.',
            $response->json('issues')
        );
    }

    public function test_personnel_group_availability_ignores_method_override_from_edit_form(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();
        $peopleIds = collect([$group->conductor_id])->merge($group->helpers->pluck('id'));

        SolicitudVacacion::whereIn('personal_id', $peopleIds)->delete();

        $this->actingAs($user)
            ->post(route('grupos-personal.validate'), [
                '_method' => 'PUT',
                'ignored_group_id' => $group->id,
                'turno_id' => $group->turno_id,
                'vehiculo_id' => $group->vehiculo_id,
                'conductor_id' => $group->conductor_id,
                'helper_ids' => $group->helpers->pluck('id')->all(),
                'dias_semana' => $group->dias_semana,
            ])
            ->assertOk()
            ->assertJsonFragment([
                'available' => true,
            ]);
    }

    public function test_personnel_group_requires_exactly_two_helpers(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('grupos-personal.validate'), [
                'turno_id' => $group->turno_id,
                'vehiculo_id' => $group->vehiculo_id,
                'conductor_id' => $group->conductor_id,
                'helper_ids' => [$group->helpers->first()->id],
                'dias_semana' => $group->dias_semana,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('helper_ids');
    }

    public function test_personnel_group_rejects_helper_without_active_contract(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();
        $helperType = TipoPersonal::where('name', '!=', TipoPersonal::DRIVER)->firstOrFail();
        $helperWithoutContrato = Personal::create([
            'tipo_personal_id' => $helperType->id,
            'dni' => '55667788',
            'nombres' => 'Federico',
            'apellidos' => 'Valverde',
            'fecha_nacimiento' => '1995-01-10',
            'telefono' => '999888777',
            'email' => 'federico.valverde@test.local',
            'password' => 'secret123',
            'direccion' => 'Av. Principal 123',
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('grupos-personal.validate'), [
                'ignored_group_id' => $group->id,
                'turno_id' => $group->turno_id,
                'vehiculo_id' => $group->vehiculo_id,
                'conductor_id' => $group->conductor_id,
                'helper_ids' => [$group->helpers->first()->id, $helperWithoutContrato->id],
                'dias_semana' => $group->dias_semana,
            ])
            ->assertStatus(422)
            ->assertJsonFragment([
                'available' => false,
                'id' => $helperWithoutContrato->id,
                'message' => 'No tiene contrato activo vigente.',
            ]);
    }

    public function test_personnel_group_form_lists_only_people_with_active_contract(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $helperType = TipoPersonal::where('name', '!=', TipoPersonal::DRIVER)->firstOrFail();
        $helperWithoutContrato = Personal::create([
            'tipo_personal_id' => $helperType->id,
            'dni' => '44556611',
            'nombres' => 'Personal',
            'apellidos' => 'Sin Contrato',
            'fecha_nacimiento' => '1995-01-10',
            'telefono' => '999888777',
            'email' => 'sin.contrato@test.local',
            'password' => 'secret123',
            'direccion' => 'Av. Principal 123',
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->get(route('grupos-personal.index'))
            ->assertOk()
            ->assertDontSee("{$helperWithoutContrato->full_name} - {$helperWithoutContrato->dni}");
    }

    public function test_personnel_group_rejects_helper_with_vacation_on_selected_day(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();
        $peopleIds = collect([$group->conductor_id])->merge($group->helpers->pluck('id'));
        $helper = $group->helpers->first();
        $vacationDate = $this->upcomingDateForDays($group->dias_semana);

        SolicitudVacacion::whereIn('personal_id', $peopleIds)->delete();
        SolicitudVacacion::create([
            'personal_id' => $helper->id,
            'fecha_solicitud' => today(),
            'fecha_inicio' => $vacationDate,
            'fecha_fin' => $vacationDate,
            'dias_solicitados' => 1,
            'dias_restantes' => 29,
            'status' => SolicitudVacacion::STATUS_APPROVED,
        ]);

        $this->actingAs($user)
            ->postJson(route('grupos-personal.validate'), [
                'ignored_group_id' => $group->id,
                'turno_id' => $group->turno_id,
                'vehiculo_id' => $group->vehiculo_id,
                'conductor_id' => $group->conductor_id,
                'helper_ids' => $group->helpers->pluck('id')->all(),
                'dias_semana' => $group->dias_semana,
            ])
            ->assertStatus(422)
            ->assertJsonFragment([
                'available' => false,
                'id' => $helper->id,
                'message' => 'Tiene vacaciones registradas para el '.$vacationDate->format('d/m/Y').'.',
            ]);
    }

    public function test_route_schedule_validation_returns_replacement_suggestions(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();
        $helperType = TipoPersonal::where('name', '!=', TipoPersonal::DRIVER)->firstOrFail();
        $busyHelper = $group->helpers->first();
        $availableHelper = Personal::create([
            'tipo_personal_id' => $helperType->id,
            'dni' => '66778899',
            'nombres' => 'Aaron',
            'apellidos' => 'Campos',
            'fecha_nacimiento' => '1994-03-12',
            'telefono' => '988777666',
            'email' => 'valeria.campos@test.local',
            'password' => 'secret123',
            'direccion' => 'Av. Los Jardines 456',
            'activo' => true,
        ]);

        Contrato::create([
            'personal_id' => $availableHelper->id,
            'tipo_contrato' => Contrato::TYPE_PERMANENT,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => null,
            'salario' => 1500,
            'meses_periodo_prueba' => 0,
            'cargo' => 'Ayudante',
            'activo' => true,
        ]);

        SolicitudVacacion::where('personal_id', $busyHelper->id)->delete();
        SolicitudVacacion::create([
            'personal_id' => $busyHelper->id,
            'fecha_solicitud' => '2026-07-02',
            'fecha_inicio' => '2026-07-02',
            'fecha_fin' => '2026-07-02',
            'dias_solicitados' => 1,
            'dias_restantes' => 29,
            'status' => SolicitudVacacion::STATUS_APPROVED,
        ]);

        $this->actingAs($user)
            ->postJson(route('programaciones.validate'), $this->payload($group, '2026-07-02', '2026-07-02', [4]))
            ->assertStatus(422)
            ->assertJsonFragment([
                'role' => 'helper',
                'id' => $availableHelper->id,
                'label' => "{$availableHelper->full_name} - {$availableHelper->dni}",
            ]);
    }

    public function test_mass_route_schedule_can_generate_selected_groups_without_server_shift_filter(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $group = GrupoPersonal::with('helpers')->firstOrFail();
        $groups = GrupoPersonal::with('helpers')
            ->where('activo', true)
            ->where('turno_id', $group->turno_id)
            ->get();
        $peopleIds = $groups
            ->flatMap(fn (GrupoPersonal $personnelGroup) => collect([$personnelGroup->conductor_id])->merge($personnelGroup->helpers->pluck('id')))
            ->unique();

        Programacion::whereBetween('fecha_programada', ['2026-08-10', '2026-08-16'])->delete();
        Feriado::whereBetween('date', ['2026-08-10', '2026-08-16'])->delete();
        SolicitudVacacion::whereIn('personal_id', $peopleIds)->delete();

        $payloadGroups = $groups->mapWithKeys(fn (GrupoPersonal $personnelGroup) => [
            $personnelGroup->id => [
                'enabled' => 1,
                'conductor_id' => $personnelGroup->conductor_id,
                'helper_ids' => $personnelGroup->helpers->pluck('id')->all(),
            ],
        ])->all();

        $this->actingAs($user)
            ->post(route('programaciones.mass.store'), [
                'fecha_inicio' => '2026-08-10',
                'fecha_fin' => '2026-08-16',
                'turno_id' => null,
                'groups' => $payloadGroups,
                'reason' => 'Programación masiva de prueba.',
            ])
            ->assertRedirect(route('programaciones.index'));

        $this->assertDatabaseHas('cambios_programacion', [
            'action' => 'mass_created',
            'descripcion' => 'Programación masiva de prueba.',
        ]);
    }

    public function test_route_schedule_update_requires_reason_and_marks_reprogrammed(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $schedule = Programacion::with(['helpers', 'personnelGroup'])->firstOrFail();
        $peopleIds = collect([$schedule->conductor_id])->merge($schedule->helpers->pluck('id'));

        SolicitudVacacion::whereIn('personal_id', $peopleIds)->delete();

        $this->actingAs($user)
            ->putJson(route('programaciones.update', $schedule), [
                'fecha_programada' => $schedule->fecha_programada->format('Y-m-d'),
                'turno_id' => $schedule->turno_id,
                'zona_id' => $schedule->zona_id,
                'vehiculo_id' => $schedule->vehiculo_id,
                'conductor_id' => $schedule->conductor_id,
                'helper_ids' => $schedule->helpers->pluck('id')->all(),
                'notes' => $schedule->notes,
                'change_reason' => 'Ajuste operativo de prueba.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('programaciones', [
            'id' => $schedule->id,
            'status' => Programacion::STATUS_REPROGRAMMED,
        ]);
        $this->assertDatabaseHas('cambios_programacion', [
            'programacion_id' => $schedule->id,
            'action' => 'reprogrammed',
            'descripcion' => 'Ajuste operativo de prueba.',
        ]);
    }

    private function payload(GrupoPersonal $group, string $startDate, string $endDate, array $days): array
    {
        return [
            'grupo_personal_id' => $group->id,
            'fecha_inicio' => $startDate,
            'fecha_fin' => $endDate,
            'turno_id' => $group->turno_id,
            'zona_id' => $group->zona_id,
            'vehiculo_id' => $group->vehiculo_id,
            'conductor_id' => $group->conductor_id,
            'helper_ids' => $group->helpers->pluck('id')->all(),
            'dias_semana' => $days,
            'notes' => null,
        ];
    }

    private function upcomingDateForDays(array $days): Carbon
    {
        $selectedDays = collect($days)->map(fn ($day) => (int) $day);

        for ($date = today(); $date->lte(today()->addDays(13)); $date->addDay()) {
            if ($selectedDays->contains($date->dayOfWeekIso)) {
                return $date->copy();
            }
        }

        return today();
    }
}
