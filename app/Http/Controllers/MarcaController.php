<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToCrudRequests;
use App\Http\Requests\ActualizarMarcaRequest;
use App\Http\Requests\RegistrarMarcaRequest;
use App\Models\Marca;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarcaController extends Controller
{
    use RespondsToCrudRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $marcas = Marca::query()
            ->when(request('q'), fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->latest()
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('marcas.index', compact('marcas'));
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
    public function store(RegistrarMarcaRequest $request)
    {
        $data = $request->validated();
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            $data['ruta_logo'] = $request->file('logo')->store('marcas', 'public');
        }

        Marca::create($data);

        return $this->successResponse($request, 'marcas.index', 'Marca registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Marca $brand)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ActualizarMarcaRequest $request, Marca $brand)
    {
        $data = $request->validated();
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            if ($brand->ruta_logo) {
                Storage::disk('public')->delete($brand->ruta_logo);
            }

            $data['ruta_logo'] = $request->file('logo')->store('marcas', 'public');
        }

        $brand->update($data);

        return $this->successResponse($request, 'marcas.index', 'Marca actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Marca $brand)
    {
        if ($brand->modelos()->exists()) {
            return $this->errorResponse($request, 'marcas.index', 'No se puede eliminar una marca con modelos asociados.');
        }

        if ($brand->vehiculos()->exists()) {
            return $this->errorResponse($request, 'marcas.index', 'No se puede eliminar una marca asignada a vehículos.');
        }

        if ($brand->ruta_logo) {
            Storage::disk('public')->delete($brand->ruta_logo);
        }

        $brand->delete();

        return $this->successResponse($request, 'marcas.index', 'Marca eliminada correctamente.');
    }
}
