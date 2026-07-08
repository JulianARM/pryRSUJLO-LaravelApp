<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarSolicitudVacacionRequest;
use App\Http\Requests\RegistrarSolicitudVacacionRequest;
use App\Models\Contrato;
use App\Models\Personal;
use App\Models\SaldoVacacion;
use App\Models\SolicitudVacacion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SolicitudVacacionController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $vacations = SolicitudVacacion::with('employee.vacationBalances')
            ->when(request('q'), fn ($query, $term) => $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery
                ->where('dni', 'like', "%{$term}%")
                ->orWhere('nombres', 'like', "%{$term}%")
                ->orWhere('apellidos', 'like', "%{$term}%")
                ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$term}%"])))
            ->latest('fecha_solicitud')
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('vacaciones.index', [
            'vacations' => $vacations,
            'personal' => $this->eligiblePersonals(),
        ]);
    }

    public function store(RegistrarSolicitudVacacionRequest $request)
    {
        SolicitudVacacion::create($this->payload($request->validated()));

        return $this->successResponse($request, 'vacaciones.index', 'Solicitud de vacaciones registrada correctamente.');
    }

    public function update(ActualizarSolicitudVacacionRequest $request, SolicitudVacacion $vacation)
    {
        $vacation->update($this->payload($request->validated(), $vacation));

        return $this->successResponse($request, 'vacaciones.index', 'Solicitud de vacaciones actualizada correctamente.');
    }

    public function approve(Request $request, SolicitudVacacion $vacation)
    {
        if ($vacation->status !== SolicitudVacacion::STATUS_PENDING) {
            return $this->errorResponse($request, 'vacaciones.index', 'Solo se pueden aprobar solicitudes pendientes.');
        }

        $message = DB::transaction(function () use ($vacation) {
            $balance = SaldoVacacion::where('personal_id', $vacation->personal_id)
                ->where('anio', $vacation->fecha_inicio->year)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = SaldoVacacion::create([
                    'personal_id' => $vacation->personal_id,
                    'anio' => $vacation->fecha_inicio->year,
                    'dias_totales' => SaldoVacacion::DEFAULT_ANNUAL_DAYS,
                    'dias_usados' => 0,
                    'dias_disponibles' => SaldoVacacion::DEFAULT_ANNUAL_DAYS,
                ]);
            }

            if (! $balance->canUse($vacation->dias_solicitados)) {
                return 'No se puede aprobar. El personal solo tiene '.$balance->dias_disponibles.' días disponibles en '.$balance->anio.'.';
            }

            $balance->discount($vacation->dias_solicitados);

            $vacation->update([
                'saldo_vacacion_id' => $balance->id,
                'dias_restantes' => $balance->dias_disponibles,
                'status' => SolicitudVacacion::STATUS_APPROVED,
            ]);

            return null;
        });

        if ($message) {
            return $this->errorResponse($request, 'vacaciones.index', $message);
        }

        return $this->successResponse($request, 'vacaciones.index', 'Solicitud aprobada correctamente.');
    }

    public function reject(Request $request, SolicitudVacacion $vacation)
    {
        if ($vacation->status !== SolicitudVacacion::STATUS_PENDING) {
            return $this->errorResponse($request, 'vacaciones.index', 'Solo se pueden rechazar solicitudes pendientes.');
        }

        $vacation->update(['status' => SolicitudVacacion::STATUS_REJECTED]);

        return $this->successResponse($request, 'vacaciones.index', 'Solicitud rechazada correctamente.');
    }

    public function destroy(Request $request, SolicitudVacacion $vacation)
    {
        DB::transaction(function () use ($vacation) {
            if ($vacation->status === SolicitudVacacion::STATUS_APPROVED && $vacation->vacationBalance) {
                $vacation->vacationBalance->restoreDays($vacation->dias_solicitados);
            }

            $vacation->delete();
        });

        return $this->successResponse($request, 'vacaciones.index', 'Solicitud eliminada correctamente.');
    }

    private function payload(array $data, ?SolicitudVacacion $vacation = null): array
    {
        $startDate = Carbon::parse($data['fecha_inicio']);
        $days = (int) $data['dias_solicitados'];

        return [
            'personal_id' => $data['personal_id'],
            'saldo_vacacion_id' => $vacation?->saldo_vacacion_id,
            'fecha_solicitud' => $vacation?->fecha_solicitud ?? today(),
            'fecha_inicio' => $startDate,
            'fecha_fin' => $startDate->copy()->addDays($days - 1),
            'dias_solicitados' => $days,
            'dias_restantes' => $vacation?->dias_restantes ?? 0,
            'status' => $vacation?->status ?? SolicitudVacacion::STATUS_PENDING,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function eligiblePersonals()
    {
        return Personal::where('activo', true)
            ->with(['vacationBalances' => fn ($query) => $query->where('anio', now()->year)])
            ->whereHas('contratos', fn ($query) => $query
                ->where('activo', true)
                ->whereIn('tipo_contrato', [Contrato::TYPE_PERMANENT, Contrato::TYPE_NAMED]))
            ->orderBy('nombres')
            ->limit(50)
            ->get();
    }
}
