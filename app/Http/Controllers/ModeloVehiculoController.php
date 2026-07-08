<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarModeloVehiculoRequest;
use App\Http\Requests\RegistrarModeloVehiculoRequest;
use App\Models\Marca;
use App\Models\ModeloVehiculo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ModeloVehiculoController extends Controller
{
    use RespondsToCrudRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $brandModels = ModeloVehiculo::with('brand')
            ->when(request('q'), fn ($query, $term) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%"))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();
        $marcas = Marca::orderBy('name')->pluck('name', 'id');

        return view('modelos-vehiculo.index', compact('brandModels', 'marcas'));
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
    public function store(RegistrarModeloVehiculoRequest $request)
    {
        ModeloVehiculo::create($request->validated());

        return $this->successResponse($request, 'modelos-vehiculo.index', 'Modelo registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ModeloVehiculo $brandModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ModeloVehiculo $brandModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ActualizarModeloVehiculoRequest $request, ModeloVehiculo $brandModel)
    {
        $brandModel->update($request->validated());

        return $this->successResponse($request, 'modelos-vehiculo.index', 'Modelo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ModeloVehiculo $brandModel)
    {
        if ($brandModel->vehiculos()->exists()) {
            return $this->errorResponse($request, 'modelos-vehiculo.index', 'No se puede eliminar un modelo asignado a vehículos.');
        }

        $brandModel->delete();

        return $this->successResponse($request, 'modelos-vehiculo.index', 'Modelo eliminado correctamente.');
    }
}
