<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\RegistrarCambioMasivoProgramacionRequest;
use App\Models\CambioProgramacion;
use App\Models\MotivoCambio;
use App\Models\Personal;
use App\Models\TipoPersonal;
use App\Models\Turno;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Services\CambioMasivoProgramacionService;
use App\Services\ReversionCambioProgramacionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CambioProgramacionController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $changes = CambioProgramacion::with(['schedule.zone', 'schedule.shift', 'schedule.vehicle', 'schedule.driver', 'reason', 'user'])
            ->when(request('fecha_inicio'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when(request('fecha_fin'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when(request('tipo_cambio'), fn ($query, $type) => $query->where('tipo_cambio', $type))
            ->latest('created_at')
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('cambios-programacion.index', $this->formData() + compact('changes'));
    }

    public function show(CambioProgramacion $scheduleChange): View
    {
        $scheduleChange->load(['schedule.zone', 'schedule.shift', 'schedule.vehicle', 'schedule.driver', 'schedule.helpers', 'reason', 'user']);

        return view('cambios-programacion.show', [
            'change' => $scheduleChange,
            'changeTypes' => CambioMasivoProgramacionService::TYPES,
        ]);
    }

    public function storeMass(RegistrarCambioMasivoProgramacionRequest $request, CambioMasivoProgramacionService $service)
    {
        try {
            $count = $service->apply($request->validated(), $request->user());
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => collect($exception->errors())->flatten()->implode(' '),
                    'errors' => $exception->errors(),
                ], 422);
            }

            throw $exception;
        }

        return $this->successResponse($request, 'cambios-programacion.index', "Cambio masivo aplicado correctamente ({$count} programación(es) afectada(s)).");
    }

    public function destroy(Request $request, CambioProgramacion $scheduleChange, ReversionCambioProgramacionService $reversion)
    {
        try {
            $reversion->revertirYEliminar($scheduleChange);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => collect($exception->errors())->flatten()->implode(' '),
                    'errors' => $exception->errors(),
                ], 422);
            }

            return $this->errorResponse($request, 'cambios-programacion.index', collect($exception->errors())->flatten()->implode(' '));
        }

        return $this->successResponse($request, 'cambios-programacion.index', 'Cambio eliminado y programación afectada revertida correctamente.');
    }

    private function formData(): array
    {
        return [
            'changeTypes' => CambioMasivoProgramacionService::TYPES,
            'reasons' => MotivoCambio::where('activo', true)->orderBy('name')->pluck('name', 'id'),
            'zonas' => Zona::where('activo', true)->orderBy('name')->pluck('name', 'id'),
            'turnos' => Turno::orderBy('start_time')->pluck('name', 'id'),
            'vehiculos' => Vehiculo::where('activo', true)->orderBy('name')->get()->mapWithKeys(fn ($vehicle) => [
                $vehicle->id => "{$vehicle->name} - {$vehicle->placa}",
            ]),
            'drivers' => Personal::where('activo', true)->withActiveContrato()
                ->whereHas('staffType', fn ($query) => $query->where('name', TipoPersonal::DRIVER))
                ->orderBy('nombres')
                ->get()
                ->mapWithKeys(fn ($employee) => [$employee->id => "{$employee->full_name} - {$employee->dni}"]),
            'helpers' => Personal::where('activo', true)->withActiveContrato()
                ->whereHas('staffType', fn ($query) => $query->where('name', '!=', TipoPersonal::DRIVER))
                ->orderBy('nombres')
                ->get()
                ->mapWithKeys(fn ($employee) => [$employee->id => "{$employee->full_name} - {$employee->dni}"]),
        ];
    }
}
