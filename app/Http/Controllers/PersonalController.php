<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarPersonalRequest;
use App\Http\Requests\RegistrarPersonalRequest;
use App\Models\Contrato;
use App\Models\Personal;
use App\Models\SaldoVacacion;
use App\Models\TipoPersonal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PersonalController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $anio = now()->year;

        $personal = Personal::with([
            'staffType',
            'vacationBalances' => fn ($query) => $query->where('anio', $anio),
        ])
            ->when(request('q'), fn ($query, $term) => $query
                ->where('dni', 'like', "%{$term}%")
                ->orWhere('nombres', 'like', "%{$term}%")
                ->orWhere('apellidos', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('personal.index', [
            'personal' => $personal,
            'staffTypes' => TipoPersonal::orderBy('name')->pluck('name', 'id'),
            'staffTypeRecords' => TipoPersonal::orderBy('name')->get(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));
        $vacationEligible = $request->boolean('vacation_eligible');
        $anio = now()->year;

        $personal = Personal::with([
            'staffType',
            'vacationBalances' => fn ($query) => $query->where('anio', $anio),
        ])
            ->where('activo', true)
            ->when($vacationEligible, fn ($query) => $query->whereHas('contratos', fn ($contractQuery) => $contractQuery
                ->where('activo', true)
                ->whereIn('tipo_contrato', [Contrato::TYPE_PERMANENT, Contrato::TYPE_NAMED])))
            ->when($term, fn ($query) => $query->where(function ($query) use ($term) {
                $query->where('dni', 'like', "%{$term}%")
                    ->orWhere('nombres', 'like', "%{$term}%")
                    ->orWhere('apellidos', 'like', "%{$term}%")
                    ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$term}%"]);
            }))
            ->orderBy('nombres')
            ->limit(20)
            ->get()
            ->map(function (Personal $employee) use ($vacationEligible, $anio) {
                $availableDays = $employee->vacationDaysAvailable($anio);
                $text = "{$employee->full_name} - DNI: {$employee->dni}";

                if ($vacationEligible) {
                    $text .= " ({$availableDays} días disponibles)";
                }

                return [
                    'id' => $employee->id,
                    'text' => $text,
                    'dias_disponibles' => $availableDays,
                    'maximum_days' => SaldoVacacion::DEFAULT_ANNUAL_DAYS,
                ];
            });

        return response()->json([
            'results' => $personal,
        ]);
    }

    public function store(RegistrarPersonalRequest $request)
    {
        $data = $request->validated();
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            $data['ruta_foto'] = $request->file('photo')->store('personal', 'public');
        }

        Personal::create($data);

        return $this->successResponse($request, 'personal.index', 'Personal registrado correctamente.');
    }

    public function update(ActualizarPersonalRequest $request, Personal $employee)
    {
        $data = $request->validated();
        unset($data['photo']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($request->hasFile('photo')) {
            if ($employee->ruta_foto) {
                Storage::disk('public')->delete($employee->ruta_foto);
            }

            $data['ruta_foto'] = $request->file('photo')->store('personal', 'public');
        }

        $employee->update($data);

        return $this->successResponse($request, 'personal.index', 'Personal actualizado correctamente.');
    }

    public function destroy(Request $request, Personal $employee)
    {
        if ($employee->contratos()->exists()) {
            return $this->errorResponse($request, 'personal.index', 'No se puede eliminar personal con contratos registrados.');
        }

        if ($employee->ruta_foto) {
            Storage::disk('public')->delete($employee->ruta_foto);
        }

        $employee->delete();

        return $this->successResponse($request, 'personal.index', 'Personal eliminado correctamente.');
    }
}
