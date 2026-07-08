<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarMotivoCambioRequest;
use App\Http\Requests\RegistrarMotivoCambioRequest;
use App\Models\MotivoCambio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MotivoCambioController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {

        $reasons = MotivoCambio::query()

            ->when(request('q'), fn ($query, $term) => $query

                ->where('name', 'like', "%{$term}%")

                ->orWhere('descripcion', 'like', "%{$term}%"))

            ->latest()

            ->paginate(request('per_page', 10))

            ->withQueryString();

        return view('motivos-cambio.index', compact('reasons'));

    }

    public function store(RegistrarMotivoCambioRequest $request)
    {

        MotivoCambio::create($request->validated());

        return $this->successResponse($request, 'motivos-cambio.index', 'Motivo registrado correctamente.');

    }

    public function update(ActualizarMotivoCambioRequest $request, MotivoCambio $changeReason)
    {

        $changeReason->update($request->validated());

        return $this->successResponse($request, 'motivos-cambio.index', 'Motivo actualizado correctamente.');

    }

    public function destroy(Request $request, MotivoCambio $changeReason)
    {

        if ($changeReason->cambios()->exists()) {

            return $this->errorResponse($request, 'motivos-cambio.index', 'No se puede eliminar un motivo usado en cambios registrados.');

        }

        $changeReason->delete();

        return $this->successResponse($request, 'motivos-cambio.index', 'Motivo eliminado correctamente.');

    }
}
