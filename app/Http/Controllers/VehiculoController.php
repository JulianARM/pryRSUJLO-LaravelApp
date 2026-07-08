<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarVehiculoRequest;
use App\Http\Requests\RegistrarVehiculoRequest;
use App\Models\ColorVehiculo;
use App\Models\Marca;
use App\Models\ModeloVehiculo;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehiculoController extends Controller
{
    use RespondsToCrudRequests;

    public function index(): View
    {
        $vehiculos = Vehiculo::with(['brand', 'model', 'type', 'color', 'profileImage', 'images'])
            ->when(request('q'), fn ($query, $term) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('placa', 'like', "%{$term}%"))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('vehiculos.index', [
            'vehiculos' => $vehiculos,
            'marcas' => Marca::orderBy('name')->pluck('name', 'id'),
            'brandModels' => ModeloVehiculo::with('brand')->orderBy('name')->get(),
            'vehicleTypes' => TipoVehiculo::orderBy('name')->pluck('name', 'id'),
            'vehicleColors' => ColorVehiculo::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(RegistrarVehiculoRequest $request)
    {
        Vehiculo::create($request->validated());

        return $this->successResponse($request, 'vehiculos.index', 'Vehículo registrado correctamente.');
    }

    public function update(ActualizarVehiculoRequest $request, Vehiculo $vehicle)
    {
        $vehicle->update($request->validated());

        return $this->successResponse($request, 'vehiculos.index', 'Vehículo actualizado correctamente.');
    }

    public function destroy(Request $request, Vehiculo $vehicle)
    {
        if ($vehicle->personnelGroups()->exists()) {
            return $this->errorResponse($request, 'vehiculos.index', 'No se puede eliminar un vehículo asignado a grupos de personal.');
        }

        if ($vehicle->routeSchedules()->exists()) {
            return $this->errorResponse($request, 'vehiculos.index', 'No se puede eliminar un vehículo con programaciones registradas.');
        }

        foreach ($vehicle->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $vehicle->delete();

        return $this->successResponse($request, 'vehiculos.index', 'Vehículo eliminado correctamente.');
    }
}
