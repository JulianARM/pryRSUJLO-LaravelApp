<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarGrupoPersonalRequest;
use App\Http\Requests\RegistrarGrupoPersonalRequest;
use App\Models\GrupoPersonal;
use App\Models\Personal;
use App\Models\TipoPersonal;
use App\Models\Turno;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Services\DisponibilidadGrupoPersonalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GrupoPersonalController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $groups = GrupoPersonal::with(['shift', 'zone', 'vehicle', 'driver', 'helpers'])
            ->when(request('q'), fn ($query, $term) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhereHas('driver', fn ($driver) => $driver
                    ->where('dni', 'like', "%{$term}%")
                    ->orWhere('nombres', 'like', "%{$term}%")
                    ->orWhere('apellidos', 'like', "%{$term}%")))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('grupos-personal.index', $this->formData() + compact('groups'));
    }

    public function show(Request $request, GrupoPersonal $personnelGroup): JsonResponse|View
    {
        $personnelGroup->load(['shift', 'zone', 'vehicle', 'driver', 'helpers']);

        if (! $request->expectsJson()) {
            $personnelGroup->load([
                'schedules' => fn ($query) => $query
                    ->with(['shift', 'zone', 'vehicle', 'driver', 'helpers', 'changes'])
                    ->latest('fecha_programada'),
            ]);

            return view('grupos-personal.show', compact('personnelGroup'));
        }

        return response()->json([
            'id' => $personnelGroup->id,
            'name' => $personnelGroup->name,
            'turno_id' => $personnelGroup->turno_id,
            'zona_id' => $personnelGroup->zona_id,
            'vehiculo_id' => $personnelGroup->vehiculo_id,
            'conductor_id' => $personnelGroup->conductor_id,
            'helper_ids' => $personnelGroup->helpers->pluck('id')->values(),
            'helper_count' => $personnelGroup->helpers->count(),
            'vehicle_capacity' => (int) $personnelGroup->vehicle->capacidad_personas,
            'dias_semana' => collect($personnelGroup->dias_semana)->map(fn ($day) => (int) $day)->values(),
            'summary' => [
                'shift' => $personnelGroup->shift->name,
                'zone' => $personnelGroup->zone->name,
                'vehicle' => "{$personnelGroup->vehicle->name} - {$personnelGroup->vehicle->placa}",
                'driver' => "{$personnelGroup->driver->full_name} - {$personnelGroup->driver->dni}",
                'helpers' => $personnelGroup->helpers->map(fn ($helper) => "{$helper->full_name} - {$helper->dni}")->values(),
                'days' => $personnelGroup->days_label,
            ],
        ]);
    }

    public function store(RegistrarGrupoPersonalRequest $request)
    {
        $group = GrupoPersonal::create($request->safe()->except('helper_ids'));
        $group->helpers()->sync($request->validated('helper_ids'));

        return $this->successResponse($request, 'grupos-personal.index', 'Grupo de personal registrado correctamente.');
    }

    public function update(ActualizarGrupoPersonalRequest $request, GrupoPersonal $personnelGroup)
    {
        $personnelGroup->update($request->safe()->except('helper_ids'));
        $personnelGroup->helpers()->sync($request->validated('helper_ids'));

        return $this->successResponse($request, 'grupos-personal.index', 'Grupo de personal actualizado correctamente.');
    }

    public function destroy(Request $request, GrupoPersonal $personnelGroup)
    {
        if ($personnelGroup->schedules()->exists()) {
            return $this->errorResponse($request, 'grupos-personal.index', 'No se puede eliminar un grupo con programaciones generadas.');
        }

        $personnelGroup->delete();

        return $this->successResponse($request, 'grupos-personal.index', 'Grupo de personal eliminado correctamente.');
    }

    public function validateAvailability(Request $request, DisponibilidadGrupoPersonalService $availability): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'turno_id' => ['required', 'exists:turnos,id'],
            'vehiculo_id' => ['required', 'exists:vehiculos,id'],
            'conductor_id' => ['required', 'exists:personal,id'],
            'helper_ids' => ['present', 'array'],
            'helper_ids.*' => ['required', 'distinct', 'exists:personal,id'],
            'dias_semana' => ['required', 'array', 'min:1'],
            'dias_semana.*' => ['required', 'integer', Rule::in(array_keys(GrupoPersonal::DAYS))],
            'ignored_group_id' => ['nullable', 'exists:grupos_personal,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'available' => false,
                'message' => 'Complete turno, vehículo, días, conductor y ayudantes para validar.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $expectedHelpers = max(((int) (Vehiculo::find($data['vehiculo_id'])?->capacidad_personas ?? 1)) - 1, 0);

        if (count($data['helper_ids']) !== $expectedHelpers) {
            return response()->json([
                'available' => false,
                'message' => "El vehículo seleccionado requiere exactamente {$expectedHelpers} ayudante(s).",
                'errors' => [
                    'helper_ids' => ["El vehículo seleccionado requiere exactamente {$expectedHelpers} ayudante(s)."],
                ],
            ], 422);
        }

        $ignoredGroup = filled($data['ignored_group_id'] ?? null)
            ? GrupoPersonal::find($data['ignored_group_id'])
            : null;
        $report = $availability->report($data, $ignoredGroup);

        return response()->json([
            ...$report,
            'message' => $report['available']
                ? 'Personal disponible para los días y turno seleccionados.'
                : 'Hay personal o vehículo no disponible para la configuración seleccionada.',
        ], $report['available'] ? 200 : 422);
    }

    private function formData(): array
    {
        $vehiculos = Vehiculo::where('activo', true)->orderBy('name')->get();

        return [
            'days' => GrupoPersonal::DAYS,
            'turnos' => Turno::orderBy('start_time')->pluck('name', 'id'),
            'zonas' => Zona::where('activo', true)->orderBy('name')->pluck('name', 'id'),
            'vehiculos' => $vehiculos->mapWithKeys(fn ($vehicle) => [
                $vehicle->id => "{$vehicle->name} - {$vehicle->placa}",
            ]),
            'vehicleCapacities' => $vehiculos->mapWithKeys(fn ($vehicle) => [
                $vehicle->id => (int) $vehicle->capacidad_personas,
            ])->all(),
            'drivers' => Personal::with('staffType')
                ->where('activo', true)
                ->withActiveContrato()
                ->whereHas('staffType', fn ($query) => $query->where('name', TipoPersonal::DRIVER))
                ->orderBy('nombres')
                ->get()
                ->mapWithKeys(fn ($employee) => [$employee->id => "{$employee->full_name} - {$employee->dni}"]),
            'helpers' => Personal::with('staffType')
                ->where('activo', true)
                ->withActiveContrato()
                ->whereHas('staffType', fn ($query) => $query->where('name', '!=', TipoPersonal::DRIVER))
                ->orderBy('nombres')
                ->get()
                ->mapWithKeys(fn ($employee) => [$employee->id => "{$employee->full_name} - {$employee->dni}"]),
        ];
    }
}
