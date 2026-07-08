<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarAsistenciaRequest;
use App\Http\Requests\RegistrarAsistenciaRequest;
use App\Models\Asistencia;
use App\Models\Personal;
use App\Models\Turno;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AsistenciaController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $asistencias = Asistencia::with(['employee', 'shift'])
            ->when(request('date_from'), fn ($query, $date) => $query->whereDate('fecha_asistencia', '>=', $date))
            ->when(request('date_to'), fn ($query, $date) => $query->whereDate('fecha_asistencia', '<=', $date))
            ->when(request('employee'), fn ($query, $term) => $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery
                ->where('dni', 'like', "%{$term}%")
                ->orWhere('nombres', 'like', "%{$term}%")
                ->orWhere('apellidos', 'like', "%{$term}%")
                ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$term}%"])))
            ->latest('registrado_en')
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('asistencias.index', [
            'asistencias' => $asistencias,
            'personal' => Personal::where('activo', true)->orderBy('nombres')->limit(50)->get(),
            'turnos' => Turno::orderBy('start_time')->get(),
            'types' => Asistencia::TYPES,
            'statuses' => Asistencia::STATUSES,
        ]);
    }

    public function store(RegistrarAsistenciaRequest $request)
    {
        Asistencia::create($this->payload($request->validated()));

        return $this->successResponse($request, 'asistencias.index', 'Asistencia registrada correctamente.');
    }

    public function update(ActualizarAsistenciaRequest $request, Asistencia $attendance)
    {
        $attendance->update($this->payload($request->validated(), $attendance));

        return $this->successResponse($request, 'asistencias.index', 'Asistencia actualizada correctamente.');
    }

    public function destroy(Request $request, Asistencia $attendance)
    {
        $attendance->delete();

        return $this->successResponse($request, 'asistencias.index', 'Asistencia eliminada correctamente.');
    }

    public function suggestedType(Request $request): JsonResponse
    {
        $data = $request->validate([
            'personal_id' => ['required', 'exists:personal,id'],
            'fecha_asistencia' => ['required', 'date'],
            'attendance_id' => ['nullable', 'exists:asistencias,id'],
        ]);

        $type = $this->typeForPersonalDate(
            (int) $data['personal_id'],
            $data['fecha_asistencia'],
            isset($data['attendance_id']) ? (int) $data['attendance_id'] : null
        );

        return response()->json([
            'type' => $type,
            'label' => Asistencia::TYPES[$type],
        ]);
    }

    private function payload(array $data, ?Asistencia $attendance = null): array
    {
        $data['turno_id'] = $this->shiftForTime($data['hora_asistencia'])?->id;
        $data['type'] = $this->typeForPersonalDate(
            (int) $data['personal_id'],
            $data['fecha_asistencia'],
            $attendance?->id
        );
        $data['registrado_en'] = Carbon::parse($data['fecha_asistencia'].' '.$data['hora_asistencia']);

        return $data;
    }

    private function typeForPersonalDate(int $employeeId, string $date, ?int $ignoredAsistenciaId = null): string
    {
        $latestType = Asistencia::where('personal_id', $employeeId)
            ->whereDate('fecha_asistencia', $date)
            ->when($ignoredAsistenciaId, fn ($query) => $query->whereKeyNot($ignoredAsistenciaId))
            ->latest('registrado_en')
            ->value('type');

        return $latestType === Asistencia::TYPE_IN
            ? Asistencia::TYPE_OUT
            : Asistencia::TYPE_IN;
    }

    private function shiftForTime(string $time): ?Turno
    {
        return Turno::orderBy('start_time')->get()->first(function (Turno $shift) use ($time) {
            $start = $shift->start_time->format('H:i');
            $end = $shift->end_time->format('H:i');

            if ($start <= $end) {
                return $time >= $start && $time <= $end;
            }

            return $time >= $start || $time <= $end;
        }) ?? Turno::orderBy('start_time')->first();
    }
}
