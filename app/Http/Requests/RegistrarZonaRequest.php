<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarZonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('zonas', 'name')],
            'departamento' => ['required', 'string', 'max:80'],
            'provincia' => ['required', 'string', 'max:80'],
            'distrito' => ['required', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'residuos_promedio_kg' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'activo' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe una zona registrada con este nombre.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo'),
        ]);
    }
}
