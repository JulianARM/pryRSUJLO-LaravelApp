<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Models\Asistencia;
use App\Models\GrupoPersonal;
use App\Models\Personal;
use App\Models\Programacion;
use App\Models\TipoPersonal;
use App\Models\Turno;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Services\DisponibilidadProgramacionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use RespondsToCrudRequests;

    public function index(Request $request, DisponibilidadProgramacionService $availability): View
    {
        $date = $request->query('date', now()->toDateString());
        $selectedTurnoId = $request->query('turno_id');
        $vehiculos = Vehiculo::where('activo', true)->orderBy('name')->get();
        $schedules = Programacion::with(['personnelGroup', 'shift', 'zone', 'vehicle', 'driver.staffType', 'helpers.staffType'])
            ->whereDate('fecha_programada', $date)
            ->when($selectedTurnoId, fn ($query) => $query->where('turno_id', $selectedTurnoId))
            ->orderBy('fecha_programada')
            ->orderBy('turno_id')
            ->get();

        $scheduleDetails = $schedules->mapWithKeys(function (Programacion $schedule) use ($availability) {
            $team = $this->personnelStatusForSchedule($schedule, $availability);

            return [
                $schedule->id => [
                    'team' => $team,
                    'has_issues' => collect($team)->contains(fn (array $member) => ! $member['is_ok']),
                    'issues_count' => collect($team)->sum(fn (array $member) => count($member['issues'])),
                    'expected_helpers' => max(((int) ($schedule->vehicle?->capacidad_personas ?? 1)) - 1, 0),
                ],
            ];
        })->all();

        $missingSchedules = $schedules
            ->map(fn (Programacion $schedule) => [
                'schedule' => $schedule,
                'issues' => collect($scheduleDetails[$schedule->id]['team'] ?? [])
                    ->reject(fn (array $member) => $member['is_ok'])
                    ->map(fn (array $member) => [
                        'role' => $member['role'],
                        'name' => $member['person']->full_name,
                        'issues' => $member['issues'],
                    ])
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $item) => ! empty($item['issues']))
            ->values();

        $metrics = [
            'total' => $schedules->count(),
            'completed' => $schedules->where('status', Programacion::STATUS_FINALIZED)->count(),
            'incomplete' => $schedules->where('status', '!=', Programacion::STATUS_FINALIZED)->count(),
            'missing_staff' => $missingSchedules->sum(fn (array $item) => count($item['issues'])),
        ];

        return view('dashboard', [
            'date' => $date,
            'selectedTurnoId' => $selectedTurnoId,
            'schedules' => $schedules,
            'missingSchedules' => $missingSchedules,
            'scheduleDetails' => $scheduleDetails,
            'metrics' => $metrics,
            'days' => GrupoPersonal::DAYS,
            'turnos' => Turno::orderBy('start_time')->pluck('name', 'id'),
            'zonas' => Zona::where('activo', true)->orderBy('name')->pluck('name', 'id'),
            'vehiculos' => $vehiculos->mapWithKeys(fn (Vehiculo $vehicle) => [
                $vehicle->id => "{$vehicle->name} - {$vehicle->placa}",
            ]),
            'vehicleCapacities' => $vehiculos->mapWithKeys(fn (Vehiculo $vehicle) => [
                $vehicle->id => (int) $vehicle->capacidad_personas,
            ])->all(),
            'drivers' => $this->personalByType(true),
            'helpers' => $this->personalByType(false),
        ]);
    }

    public function updatePersonnel(Request $request, Programacion $routeSchedule, DisponibilidadProgramacionService $availability)
    {
        $data = $request->validate([
            'conductor_id' => ['required', 'exists:personal,id'],
            'helper_ids' => ['present', 'array'],
            'helper_ids.*' => ['required', 'distinct', 'exists:personal,id'],
            'change_reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'change_reason.required' => 'Debe especificar el motivo del reemplazo.',
            'change_reason.min' => 'El motivo debe tener al menos 5 caracteres.',
            'helper_ids.*.distinct' => 'Los ayudantes deben ser personas diferentes.',
        ]);

        $expectedHelpers = max(((int) $routeSchedule->vehicle->capacidad_personas) - 1, 0);

        if (count($data['helper_ids']) !== $expectedHelpers) {
            return $this->errorResponse($request, 'dashboard', "La programación debe contar exactamente con {$expectedHelpers} ayudante(s).");
        }

        $payload = [
            'grupo_personal_id' => $routeSchedule->grupo_personal_id,
            'fecha_inicio' => $routeSchedule->fecha_programada->format('Y-m-d'),
            'fecha_fin' => $routeSchedule->fecha_programada->format('Y-m-d'),
            'turno_id' => $routeSchedule->turno_id,
            'zona_id' => $routeSchedule->zona_id,
            'vehiculo_id' => $routeSchedule->vehiculo_id,
            'conductor_id' => $data['conductor_id'],
            'helper_ids' => $data['helper_ids'],
            'dias_semana' => [$routeSchedule->fecha_programada->dayOfWeekIso],
        ];
        $issues = $availability->issues($payload, $routeSchedule);

        if (! empty($issues)) {
            return $this->errorResponse($request, 'dashboard', implode(' ', $issues));
        }

        DB::transaction(function () use ($routeSchedule, $data, $request) {
            $oldValues = $routeSchedule->load(['driver', 'helpers'])->toArray();
            $routeSchedule->update([
                'conductor_id' => $data['conductor_id'],
                'status' => Programacion::STATUS_REPROGRAMMED,
            ]);
            $routeSchedule->helpers()->sync($data['helper_ids']);
            $routeSchedule->changes()->create([
                'usuario_id' => $request->user()?->id,
                'action' => 'personnel_replacement',
                'tipo_cambio' => 'personnel',
                'descripcion' => $data['change_reason'],
                'detail' => 'Reemplazo de personal desde dashboard diario.',
                'valores_anteriores' => $oldValues,
                'valores_nuevos' => $routeSchedule->fresh(['driver', 'helpers'])->toArray(),
            ]);
        });

        return $this->successResponse($request, 'dashboard', 'Personal de la programación actualizado correctamente.');
    }

    private function personnelStatusForSchedule(Programacion $schedule, DisponibilidadProgramacionService $availability): array
    {
        $dates = collect([$schedule->fecha_programada->copy()]);
        $members = [
            $this->personnelMemberStatus('Conductor', $schedule->driver, $schedule, $dates, $availability),
        ];

        foreach ($schedule->helpers as $index => $helper) {
            $members[] = $this->personnelMemberStatus('Ayudante '.($index + 1), $helper, $schedule, $dates, $availability);
        }

        return $members;
    }

    private function personnelMemberStatus(string $role, Personal $person, Programacion $schedule, Collection $dates, DisponibilidadProgramacionService $availability): array
    {
        $issues = array_values(array_unique(array_merge(
            $availability->personIssues($person->id, $schedule->turno_id, $dates, $schedule),
            $this->attendanceIssues($person, $schedule)
        )));

        return [
            'role' => $role,
            'person' => $person,
            'issues' => $issues,
            'is_ok' => empty($issues),
        ];
    }

    private function attendanceIssues(Personal $person, Programacion $schedule): array
    {
        if ($schedule->fecha_programada->isFuture()) {
            return [];
        }

        $hasAttendance = Asistencia::where('personal_id', $person->id)
            ->where('turno_id', $schedule->turno_id)
            ->whereDate('fecha_asistencia', $schedule->fecha_programada->format('Y-m-d'))
            ->where('type', Asistencia::TYPE_IN)
            ->where('status', Asistencia::STATUS_PRESENT)
            ->exists();

        return $hasAttendance ? [] : ['No registra entrada presente para el turno.'];
    }

    private function personalByType(bool $driver): Collection
    {
        return Personal::where('activo', true)
            ->withActiveContrato()
            ->whereHas('staffType', function ($query) use ($driver) {
                $driver
                    ? $query->where('name', TipoPersonal::DRIVER)
                    : $query->where('name', '!=', TipoPersonal::DRIVER);
            })
            ->orderBy('nombres')
            ->get()
            ->mapWithKeys(fn (Personal $employee) => [$employee->id => "{$employee->full_name} - {$employee->dni}"]);
    }
}
