<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarTipoVehiculoRequest extends FormRequest
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
        $typeId = $this->route('vehicle_type')?->id;

        return [
            'name' => ['required', 'string', 'max:100', 'unique:tipos_vehiculo,name,'.$typeId],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
