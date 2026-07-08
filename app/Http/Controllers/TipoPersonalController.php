<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarTipoPersonalRequest;
use App\Http\Requests\RegistrarTipoPersonalRequest;
use App\Models\TipoPersonal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TipoPersonalController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $staffTypes = TipoPersonal::query()
            ->when(request('q'), fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('tipos-personal.index', compact('staffTypes'));
    }

    public function store(RegistrarTipoPersonalRequest $request)
    {
        TipoPersonal::create(array_merge($request->validated(), [
            'es_sistema' => false,
        ]));

        return $this->successResponse($request, 'tipos-personal.index', 'Tipo de personal registrado correctamente.');
    }

    public function update(ActualizarTipoPersonalRequest $request, TipoPersonal $staffType)
    {
        $staffType->update($request->validated());

        return $this->successResponse($request, 'tipos-personal.index', 'Tipo de personal actualizado correctamente.');
    }

    public function destroy(Request $request, TipoPersonal $staffType)
    {
        if ($staffType->es_sistema) {
            return $this->errorResponse($request, 'tipos-personal.index', 'Este tipo de personal es predefinido y no puede eliminarse.');
        }

        if ($staffType->personal()->exists()) {
            return $this->errorResponse($request, 'tipos-personal.index', 'No se puede eliminar un tipo con personal asociado.');
        }

        $staffType->delete();

        return $this->successResponse($request, 'tipos-personal.index', 'Tipo de personal eliminado correctamente.');
    }
}