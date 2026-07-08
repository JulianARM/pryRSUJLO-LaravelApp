<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarColorVehiculoRequest;
use App\Http\Requests\RegistrarColorVehiculoRequest;
use App\Models\ColorVehiculo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ColorVehiculoController extends Controller
{
    use RespondsToCrudRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $colors = ColorVehiculo::query()
            ->when(request('q'), fn ($query, $term) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%"))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('colores-vehiculo.index', compact('colors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegistrarColorVehiculoRequest $request)
    {
        ColorVehiculo::create($request->validated());

        return $this->successResponse($request, 'colores-vehiculo.index', 'Color registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ColorVehiculo $vehicleColor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ColorVehiculo $vehicleColor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ActualizarColorVehiculoRequest $request, ColorVehiculo $vehicleColor)
    {
        $vehicleColor->update($request->validated());

        return $this->successResponse($request, 'colores-vehiculo.index', 'Color actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ColorVehiculo $vehicleColor)
    {
        if ($vehicleColor->vehiculos()->exists()) {
            return $this->errorResponse($request, 'colores-vehiculo.index', 'No se puede eliminar un color asignado a vehículos.');
        }

        $vehicleColor->delete();

        return $this->successResponse($request, 'colores-vehiculo.index', 'Color eliminado correctamente.');
    }
}

