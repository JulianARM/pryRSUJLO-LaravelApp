<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarTurnoRequest;
use App\Http\Requests\RegistrarTurnoRequest;
use App\Models\Turno;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $turnos = Turno::query()
            ->when(request('q'), fn ($query, $term) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('descripcion', 'like', "%{$term}%"))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('turnos.index', compact('turnos'));
    }

    public function store(RegistrarTurnoRequest $request)
    {
        Turno::create($request->validated());

        return $this->successResponse($request, 'turnos.index', 'Turno registrado correctamente.');
    }

    public function update(ActualizarTurnoRequest $request, Turno $shift)
    {
        $shift->update($request->validated());

        return $this->successResponse($request, 'turnos.index', 'Turno actualizado correctamente.');
    }

    public function destroy(Request $request, Turno $shift)
    {
        if ($shift->asistencias()->exists()) {
            return $this->errorResponse($request, 'turnos.index', 'No se puede eliminar un turno con asistencias registradas.');
        }

        $shift->delete();

        return $this->successResponse($request, 'turnos.index', 'Turno eliminado correctamente.');
    }
}
