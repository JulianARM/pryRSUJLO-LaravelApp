<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarProgramacionRequest;
use App\Http\Requests\RegistrarProgramacionRequest;
use App\Models\GrupoPersonal;
use App\Models\Personal;
use App\Models\Programacion;
use App\Models\TipoPersonal;
use App\Models\Turno;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Services\DisponibilidadProgramacionService;
use App\Services\ProgramacionMasivaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProgramacionController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $schedules = Programacion::with(['personnelGroup', 'shift', 'zone', 'vehicle', 'driver', 'helpers', 'changes'])
            ->when(request('q'), fn ($query, $term) => $query
                ->whereHas('zone', fn ($zone) => $zone->where('name', 'like', "%{$term}%"))
                ->orWhereHas('vehicle', fn ($vehicle) => $vehicle->where('name', 'like', "%{$term}%")->orWhere('placa', 'like', "%{$term}%"))
                ->orWhereHas('driver', fn ($driver) => $driver
                    ->where('dni', 'like', "%{$term}%")
                    ->orWhere('nombres', 'like', "%{$term}%")
                    ->orWhere('apellidos', 'like', "%{$term}%")))
            ->latest('fecha_programada')
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('programaciones.index', $this->formData() + compact('schedules'));
    }

    public function mass(Request $request, ProgramacionMasivaService $massService): View
    {
        return $this->massView($request, $massService);
    }

    public function validateMass(Request $request, ProgramacionMasivaService $massService): View
    {
        $data = $this->validatedMassData($request);
        $groups = $massService->groups();
        $rows = $massService->rows($data, $groups);
        $massResults = $massService->validateRows($data, $groups, $rows);

        return view('programaciones.mass', $this->massFormData($request, $massService, $groups, $rows, $massResults, true));
    }

    public function storeMass(Request $request, ProgramacionMasivaService $massService)
    {
        $data = $this->validatedMassData($request, true);
        $groups = $massService->groups();
        $rows = $massService->rows($data, $groups);
        $massResults = $massService->validateRows($data, $groups, $rows);

        if ($massService->hasBlockingIssues($massResults)) {
            return redirect()
                ->route('programaciones.mass', $request->only(['fecha_inicio', 'fecha_fin', 'turno_id', 'mass_zone_filter']))
                ->withErrors(['availability' => 'Corrige las inconsistencias antes de generar la programación masiva.'])
                ->withInput();
        }

        $created = $massService->create($data, $groups, $rows, $data['reason']);

        if ($created === 0) {
            return redirect()
                ->route('programaciones.mass', $request->only(['fecha_inicio', 'fecha_fin', 'turno_id', 'mass_zone_filter']))
                ->with('error', 'No se generaron programaciones. Revisa el rango de fechas y los grupos seleccionados.');
        }

        return redirect()
            ->route('programaciones.index')
            ->with('success', "Programación masiva generada correctamente ({$created} registro(s)).");
    }

    public function validateAvailability(Request $request, DisponibilidadProgramacionService $availability): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->scheduleRules(), $this->scheduleMessages());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa los datos ingresados.',
                'errors' => $validator->errors(),
                'suggestions' => [],
            ], 422);
        }

        $data = $validator->validated();
        $expectedHelpers = max(((int) (Vehiculo::find($data['vehiculo_id'])?->capacidad_personas ?? 1)) - 1, 0);

        if (count($data['helper_ids']) !== $expectedHelpers) {
            return response()->json([
                'available' => false,
                'message' => "La programación debe contar exactamente con {$expectedHelpers} ayudante(s).",
                'errors' => [
                    'helper_ids' => ["La programación debe contar exactamente con {$expectedHelpers} ayudante(s)."],
                ],
                'warnings' => [],
                'suggestions' => [],
            ], 422);
        }

        $issues = $availability->issues($data);
        $dates = $availability->dates($data)->map->format('d/m/Y')->values();
        $warnings = $availability->holidayWarnings($data);
        $suggestions = empty($issues) ? [] : $availability->suggestions($data);
        $successMessage = empty($warnings)
            ? 'Disponibilidad validada correctamente. Ya puedes guardar la programación.'
            : 'Disponibilidad validada correctamente. Los feriados activos del rango serán omitidos al guardar.';

        return response()->json([
            'available' => empty($issues),
            'message' => empty($issues)
                ? $successMessage
                : 'Se encontraron inconsistencias. Corrige los datos antes de guardar.',
            'issues' => $issues,
            'warnings' => $warnings,
            'suggestions' => $suggestions,
            'dates' => $dates,
            'count' => $dates->count(),
        ], empty($issues) ? 200 : 422);
    }

    public function store(RegistrarProgramacionRequest $request, DisponibilidadProgramacionService $availability)
    {
        $created = DB::transaction(function () use ($request, $availability) {
            $dates = $availability->dates($request->scheduleData());
            $created = 0;

            foreach ($dates as $date) {
                $schedule = Programacion::create($this->payload($request->scheduleData(), $date->format('Y-m-d')));
                $schedule->helpers()->sync($request->validated('helper_ids'));
                $schedule->changes()->create([
                    'action' => 'created',
                    'descripcion' => 'Programación generada desde grupo de personal.',
                    'valores_nuevos' => $schedule->load(['helpers'])->toArray(),
                ]);
                $created++;
            }

            return $created;
        });

        return $this->successResponse($request, 'programaciones.index', "Programación generada correctamente ({$created} registro(s)).");
    }

    public function update(ActualizarProgramacionRequest $request, Programacion $routeSchedule)
    {
        DB::transaction(function () use ($request, $routeSchedule) {
            $oldValues = $routeSchedule->load('helpers')->toArray();
            $payload = $this->payload(
                $request->scheduleData() + ['grupo_personal_id' => $routeSchedule->grupo_personal_id],
                $request->validated('fecha_programada')
            );
            $payload['status'] = Programacion::STATUS_REPROGRAMMED;

            $routeSchedule->update($payload);
            $routeSchedule->helpers()->sync($request->validated('helper_ids'));

            $routeSchedule->changes()->create([
                'action' => 'reprogrammed',
                'descripcion' => $request->validated('change_reason'),
                'valores_anteriores' => $oldValues,
                'valores_nuevos' => $routeSchedule->fresh(['helpers'])->toArray(),
            ]);
        });

        return $this->successResponse($request, 'programaciones.index', 'Programación reprogramada correctamente.');
    }

    public function finalize(Request $request, Programacion $routeSchedule)
    {
        if ($routeSchedule->status === Programacion::STATUS_FINALIZED) {
            return $this->errorResponse($request, 'programaciones.index', 'La programación ya se encuentra finalizada.');
        }

        $oldValues = $routeSchedule->toArray();
        $routeSchedule->update(['status' => Programacion::STATUS_FINALIZED]);
        $routeSchedule->changes()->create([
            'action' => 'finalized',
            'descripcion' => 'Programación finalizada.',
            'valores_anteriores' => $oldValues,
            'valores_nuevos' => $routeSchedule->fresh()->toArray(),
        ]);

        return $this->successResponse($request, 'programaciones.index', 'Programación finalizada correctamente.');
    }

    public function destroy(Request $request, Programacion $routeSchedule)
    {
        $routeSchedule->delete();

        return $this->successResponse($request, 'programaciones.index', 'Programación eliminada correctamente.');
    }

    private function payload(array $data, string $date): array
    {
        return [
            'grupo_personal_id' => $data['grupo_personal_id'] ?? null,
            'turno_id' => $data['turno_id'],
            'zona_id' => $data['zona_id'],
            'vehiculo_id' => $data['vehiculo_id'],
            'conductor_id' => $data['conductor_id'],
            'fecha_programada' => $date,
            'status' => $data['status'] ?? Programacion::STATUS_SCHEDULED,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function scheduleRules(): array
    {
        return [
            'grupo_personal_id' => ['required', 'exists:grupos_personal,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'turno_id' => ['required', 'exists:turnos,id'],
            'zona_id' => ['required', 'exists:zonas,id'],
            'vehiculo_id' => ['required', 'exists:vehiculos,id'],
            'conductor_id' => ['required', 'exists:personal,id'],
            'helper_ids' => ['present', 'array'],
            'helper_ids.*' => ['required', 'distinct', 'exists:personal,id'],
            'dias_semana' => ['required', 'array', 'min:1'],
            'dias_semana.*' => ['required', 'integer', Rule::in(array_keys(GrupoPersonal::DAYS))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function scheduleMessages(): array
    {
        return [
            'helper_ids.array' => 'Debe seleccionar los ayudantes requeridos por la capacidad del vehículo.',
            'helper_ids.*.distinct' => 'Los ayudantes deben ser personas diferentes.',
        ];
    }

    private function massView(Request $request, ProgramacionMasivaService $massService): View
    {
        $data = [
            'fecha_inicio' => $request->query('fecha_inicio'),
            'fecha_fin' => $request->query('fecha_fin'),
            'turno_id' => $request->query('turno_id'),
            'mass_zone_filter' => $request->query('mass_zone_filter'),
            'notes' => null,
        ];
        $groups = ($data['fecha_inicio'] && $data['fecha_fin'])
            ? $massService->groups()
            : collect();
        $rows = $massService->rows($data, $groups);

        return view('programaciones.mass', $this->massFormData($request, $massService, $groups, $rows, [], false));
    }

    private function validatedMassData(Request $request, bool $requireReason = false): array
    {
        return Validator::make($request->all(), [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'turno_id' => ['nullable', 'exists:turnos,id'],
            'mass_zone_filter' => ['nullable', 'exists:zonas,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'reason' => [$requireReason ? 'required' : 'nullable', 'string', 'max:255'],
            'groups' => ['nullable', 'array'],
            'groups.*.enabled' => ['nullable'],
            'groups.*.conductor_id' => ['nullable', 'exists:personal,id'],
            'groups.*.helper_ids' => ['nullable', 'array'],
            'groups.*.helper_ids.*' => ['nullable', 'exists:personal,id'],
        ], [
            'reason.required' => 'Debe especificar el motivo para generar la programación masiva.',
        ])->validate();
    }

    private function massFormData(
        Request $request,
        ProgramacionMasivaService $massService,
        Collection $groups,
        array $rows,
        array $massResults,
        bool $validated
    ): array {
        $startDate = old('fecha_inicio', $request->input('fecha_inicio', $request->query('fecha_inicio')));
        $endDate = old('fecha_fin', $request->input('fecha_fin', $request->query('fecha_fin')));

        return $this->formData() + [
            'massGroups' => $groups,
            'massRows' => $rows,
            'massResults' => $massResults,
            'massValidated' => $validated,
            'massHasIssues' => $massService->hasBlockingIssues($massResults),
            'massFeriados' => $massService->feriados($startDate, $endDate),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedTurnoId' => old('turno_id', $request->input('turno_id', $request->query('turno_id'))),
            'selectedMassZonaId' => old('mass_zone_filter', $request->input('mass_zone_filter', $request->query('mass_zone_filter'))),
        ];
    }

    private function formData(): array
    {
        $vehiculos = Vehiculo::where('activo', true)->orderBy('name')->get();

        return [
            'days' => GrupoPersonal::DAYS,
            'statuses' => Programacion::STATUSES,
            'groups' => GrupoPersonal::where('activo', true)->orderBy('name')->pluck('name', 'id'),
            'turnos' => Turno::orderBy('start_time')->pluck('name', 'id'),
            'zonas' => Zona::where('activo', true)->orderBy('name')->pluck('name', 'id'),
            'vehiculos' => $vehiculos->mapWithKeys(fn ($vehicle) => [
                $vehicle->id => "{$vehicle->name} - {$vehicle->placa}",
            ]),
            'vehicleCapacities' => $vehiculos->mapWithKeys(fn ($vehicle) => [
                $vehicle->id => (int) $vehicle->capacidad_personas,
            ])->all(),
            'drivers' => Personal::where('activo', true)
                ->withActiveContrato()
                ->whereHas('staffType', fn ($query) => $query->where('name', TipoPersonal::DRIVER))
                ->orderBy('nombres')
                ->get()
                ->mapWithKeys(fn ($employee) => [$employee->id => "{$employee->full_name} - {$employee->dni}"]),
            'helpers' => Personal::where('activo', true)
                ->withActiveContrato()
                ->whereHas('staffType', fn ($query) => $query->where('name', '!=', TipoPersonal::DRIVER))
                ->orderBy('nombres')
                ->get()
                ->mapWithKeys(fn ($employee) => [$employee->id => "{$employee->full_name} - {$employee->dni}"]),
        ];
    }
}
