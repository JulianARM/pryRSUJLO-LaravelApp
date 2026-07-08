<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarColorVehiculoRequest extends FormRequest
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
        $colorId = $this->route('vehicle_color')?->id;

        return [
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/', 'unique:colores_vehiculo,code,'.$colorId],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
