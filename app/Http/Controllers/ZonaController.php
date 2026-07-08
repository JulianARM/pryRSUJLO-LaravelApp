<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarZonaRequest;
use App\Http\Requests\RegistrarCoordenadasZonaRequest;
use App\Http\Requests\RegistrarZonaRequest;
use App\Models\Zona;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZonaController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $zonas = Zona::withCount('coordinates')
            ->when(request('q'), fn ($query, $term) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('departamento', 'like', "%{$term}%")
                ->orWhere('provincia', 'like', "%{$term}%")
                ->orWhere('distrito', 'like', "%{$term}%"))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        $mapZonas = Zona::with('coordinates')
            ->latest()
            ->get();

        return view('zonas.index', compact('zonas', 'mapZonas'));
    }

    public function store(RegistrarZonaRequest $request)
    {
        Zona::create($request->validated());

        return $this->successResponse($request, 'zonas.index', 'Zona registrada correctamente.');
    }

    public function show(Zona $zone): View
    {
        $zone->load('coordinates');
        $zonas = Zona::with('coordinates')
            ->whereKeyNot($zone->id)
            ->where('activo', true)
            ->get();

        return view('zonas.show', compact('zone', 'zonas'));
    }

    public function update(ActualizarZonaRequest $request, Zona $zone)
    {
        $zone->update($request->validated());

        return $this->successResponse($request, 'zonas.index', 'Zona actualizada correctamente.');
    }

    public function destroy(Request $request, Zona $zone)
    {
        $zone->delete();

        return $this->successResponse($request, 'zonas.index', 'Zona eliminada correctamente.');
    }

    public function storeCoordinates(RegistrarCoordenadasZonaRequest $request, Zona $zone)
    {
        DB::transaction(function () use ($request, $zone) {
            $zone->coordinates()->delete();

            foreach ($request->coordinates() as $index => $coordinate) {
                $zone->coordinates()->create([
                    'latitud' => $coordinate['lat'],
                    'longitud' => $coordinate['lng'],
                    'orden' => $index + 1,
                ]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Perímetro de la zona actualizado correctamente.',
            ]);
        }

        return redirect()
            ->route('zonas.show', $zone)
            ->with('success', 'Perímetro de la zona actualizado correctamente.');
    }
}
