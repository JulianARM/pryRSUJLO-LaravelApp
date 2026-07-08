<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarModeloVehiculoRequest extends FormRequest
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
        $modelId = $this->route('brand_model')?->id;

        return [
            'marca_id' => ['required', 'exists:marcas,id'],
            'name' => ['required', 'string', 'max:100', 'unique:modelos_vehiculo,name,'.$modelId.',id,marca_id,'.$this->marca_id],
            'code' => ['required', 'string', 'max:30', 'unique:modelos_vehiculo,code,'.$modelId],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
