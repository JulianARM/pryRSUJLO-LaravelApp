<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\RegistrarImagenesVehiculoRequest;
use App\Models\ImagenVehiculo;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImagenVehiculoController extends Controller
{
    use RespondsToCrudRequests;

    public function store(RegistrarImagenesVehiculoRequest $request, Vehiculo $vehicle)
    {
        $hasProfile = $vehicle->images()->where('es_principal', true)->exists();

        foreach ($request->file('images', []) as $image) {
            $path = $image->store('vehiculos', 'public');

            $vehicle->images()->create([
                'path' => $path,
                'nombre_original' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
                'es_principal' => ! $hasProfile,
            ]);

            $hasProfile = true;
        }

        return $this->successResponse($request, 'vehiculos.index', 'Imágenes agregadas correctamente.');
    }

    public function profile(Request $request, Vehiculo $vehicle, ImagenVehiculo $image)
    {
        abort_unless($image->vehiculo_id === $vehicle->id, 404);

        DB::transaction(function () use ($vehicle, $image) {
            $vehicle->images()->update(['es_principal' => false]);
            $image->update(['es_principal' => true]);
        });

        return $this->successResponse($request, 'vehiculos.index', 'Imagen principal actualizada correctamente.');
    }

    public function destroy(Request $request, Vehiculo $vehicle, ImagenVehiculo $image)
    {
        abort_unless($image->vehiculo_id === $vehicle->id, 404);

        Storage::disk('public')->delete($image->path);
        $wasProfile = $image->es_principal;
        $image->delete();

        if ($wasProfile && $vehicle->images()->exists()) {
            $vehicle->images()->oldest()->first()?->update(['es_principal' => true]);
        }

        return $this->successResponse($request, 'vehiculos.index', 'Imagen eliminada correctamente.');
    }
}

