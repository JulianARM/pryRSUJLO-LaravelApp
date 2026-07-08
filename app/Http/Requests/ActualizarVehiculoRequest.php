<?php

namespace App\Http\Requests;

use App\Models\ModeloVehiculo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarVehiculoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'marca_id' => ['required', 'exists:marcas,id'],
            'modelo_vehiculo_id' => ['required', 'exists:modelos_vehiculo,id'],
            'tipo_vehiculo_id' => ['required', 'exists:tipos_vehiculo,id'],
            'color_vehiculo_id' => ['required', 'exists:colores_vehiculo,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', 'unique:vehiculos,code,'.$vehicleId],
            'placa' => ['required', 'string', 'max:10', 'regex:/^([A-Z0-9]{3}\s?[A-Z0-9]{3}|[A-Z0-9]{2}-[A-Z0-9]{4}|[A-Z0-9]{3}-[A-Z0-9]{3})$/', 'unique:vehiculos,placa,'.$vehicleId],
            'anio' => ['required', 'integer', 'between:1990,'.now()->addYear()->year],
            'capacidad_carga' => ['required', 'numeric', 'min:0'],
            'capacidad_combustible' => ['required', 'numeric', 'min:0'],
            'capacidad_compactacion' => ['required', 'numeric', 'min:0'],
            'capacidad_personas' => ['required', 'integer', 'min:1'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['required', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $exists = ModeloVehiculo::where('id', $this->modelo_vehiculo_id)
                ->where('marca_id', $this->marca_id)
                ->exists();

            if (! $exists) {
                $validator->errors()->add('modelo_vehiculo_id', 'El modelo seleccionado no pertenece a la marca indicada.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'placa' => strtoupper((string) $this->placa),
            'code' => strtoupper((string) $this->code),
            'activo' => $this->boolean('activo', true),
        ]);
    }
}
