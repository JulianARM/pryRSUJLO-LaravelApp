<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarFeriadoRequest;
use App\Http\Requests\RegistrarFeriadoRequest;
use App\Models\Feriado;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FeriadoController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $anio = (int) request('anio', now()->year);
        $baseQuery = Feriado::query();

        $feriados = Feriado::query()
            ->when(request('q'), fn ($query, $term) => $query
                ->where('descripcion', 'like', "%{$term}%"))
            ->when(request('date_start'), fn ($query, $date) => $query->whereDate('date', '>=', $date))
            ->when(request('date_end'), fn ($query, $date) => $query->whereDate('date', '<=', $date))
            ->when(request()->filled('status'), fn ($query) => $query->where('activo', request('status')))
            ->latest('date')
            ->paginate(request('per_page', 10))
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('activo', true)->count(),
            'upcoming' => (clone $baseQuery)
                ->where('activo', true)
                ->whereDate('date', '>=', now()->toDateString())
                ->count(),
            'anio' => $anio,
        ];

        return view('feriados.index', compact('feriados', 'stats'));
    }

    public function store(RegistrarFeriadoRequest $request)
    {
        Feriado::create($request->validated());

        return $this->successResponse($request, 'feriados.index', 'Feriado registrado correctamente.');
    }

    public function update(ActualizarFeriadoRequest $request, Feriado $holiday)
    {
        $holiday->update($request->validated());

        return $this->successResponse($request, 'feriados.index', 'Feriado actualizado correctamente.');
    }

    public function destroy(Request $request, Feriado $holiday)
    {
        $holiday->delete();

        return $this->successResponse($request, 'feriados.index', 'Feriado eliminado correctamente.');
    }

    public function loadPeru(Request $request)
    {
        $anio = (int) $request->input('anio', now()->year);
        $feriados = [
            "{$anio}-01-01" => 'Año Nuevo',
            "{$anio}-05-01" => 'Día del Trabajo',
            "{$anio}-06-07" => 'Día de la Bandera',
            "{$anio}-06-29" => 'San Pedro y San Pablo',
            "{$anio}-07-23" => 'Día de la Fuerza Aérea del Perú',
            "{$anio}-07-28" => 'Fiestas Patrias',
            "{$anio}-07-29" => 'Fiestas Patrias',
            "{$anio}-08-06" => 'Batalla de Junín',
            "{$anio}-08-30" => 'Santa Rosa de Lima',
            "{$anio}-10-08" => 'Combate de Angamos',
            "{$anio}-11-01" => 'Día de Todos los Santos',
            "{$anio}-12-08" => 'Inmaculada Concepción',
            "{$anio}-12-09" => 'Batalla de Ayacucho',
            "{$anio}-12-25" => 'Navidad',
        ];

        foreach ($feriados as $date => $descripcion) {
            Feriado::updateOrCreate(
                ['date' => Carbon::parse($date)->toDateString()],
                ['descripcion' => $descripcion, 'activo' => true]
            );
        }

        return $this->successResponse($request, 'feriados.index', 'Feriados de Perú cargados correctamente.');
    }
}
