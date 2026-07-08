<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarContratoRequest;
use App\Http\Requests\RegistrarContratoRequest;
use App\Models\Contrato;
use App\Models\Personal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $contratos = Contrato::with(['employee.staffType'])
            ->when(request('q'), fn ($query, $term) => $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery
                ->where('dni', 'like', "%{$term}%")
                ->orWhere('nombres', 'like', "%{$term}%")
                ->orWhere('apellidos', 'like', "%{$term}%")))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('contratos.index', [
            'contratos' => $contratos,
            'personal' => Personal::with('staffType')->where('activo', true)->orderBy('nombres')->limit(50)->get(),
            'contractTypes' => Contrato::TYPES,
        ]);
    }

    public function searchPersonals(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));

        $personal = Personal::with('staffType')
            ->where('activo', true)
            ->when($term, fn ($query) => $query->where(function ($query) use ($term) {
                $query->where('dni', 'like', "%{$term}%")
                    ->orWhere('nombres', 'like', "%{$term}%")
                    ->orWhere('apellidos', 'like', "%{$term}%")
                    ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$term}%"]);
            }))
            ->orderBy('nombres')
            ->limit(20)
            ->get()
            ->map(fn (Personal $employee) => [
                'id' => $employee->id,
                'text' => "{$employee->full_name} - {$employee->dni} ({$employee->staffType->name})",
            ]);

        return response()->json([
            'results' => $personal,
        ]);
    }

    public function store(RegistrarContratoRequest $request)
    {
        Contrato::create($this->payload($request->validated()));

        return $this->successResponse($request, 'contratos.index', 'Contrato registrado correctamente.');
    }

    public function update(ActualizarContratoRequest $request, Contrato $contract)
    {
        $contract->update($this->payload($request->validated()));

        return $this->successResponse($request, 'contratos.index', 'Contrato actualizado correctamente.');
    }

    public function destroy(Request $request, Contrato $contract)
    {
        $contract->update(['activo' => false]);

        return $this->successResponse($request, 'contratos.index', 'Contrato desactivado correctamente.');
    }

    private function payload(array $data): array
    {
        $employee = Personal::with('staffType')->findOrFail($data['personal_id']);
        $data['cargo'] = $employee->staffType->name;

        if ($data['tipo_contrato'] !== Contrato::TYPE_TEMPORARY) {
            $data['fecha_fin'] = null;
        }

        return $data;
    }
}
