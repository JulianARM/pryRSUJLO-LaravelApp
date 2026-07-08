<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarTipoVehiculoRequest;
use App\Http\Requests\RegistrarTipoVehiculoRequest;
use App\Models\TipoVehiculo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TipoVehiculoController extends Controller
{
    use RespondsToCrudRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $vehicleTypes = TipoVehiculo::query()
            ->when(request('q'), fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('tipos-vehiculo.index', compact('vehicleTypes'));
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
    public function store(RegistrarTipoVehiculoRequest $request)
    {
        TipoVehiculo::create($request->validated());

        return $this->successResponse($request, 'tipos-vehiculo.index', 'Tipo de vehículo registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoVehiculo $vehicleType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoVehiculo $vehicleType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ActualizarTipoVehiculoRequest $request, TipoVehiculo $vehicleType)
    {
        $vehicleType->update($request->validated());

        return $this->successResponse($request, 'tipos-vehiculo.index', 'Tipo de vehículo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, TipoVehiculo $vehicleType)
    {
        if ($vehicleType->vehiculos()->exists()) {
            return $this->errorResponse($request, 'tipos-vehiculo.index', 'No se puede eliminar un tipo asignado a vehículos.');
        }

        $vehicleType->delete();

        return $this->successResponse($request, 'tipos-vehiculo.index', 'Tipo de vehículo eliminado correctamente.');
    }
}
