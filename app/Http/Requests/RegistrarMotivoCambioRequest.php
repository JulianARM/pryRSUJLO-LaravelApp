<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarMotivoCambioRequest extends FormRequest
{
    public function authorize(): bool
    {

        return true;

    }

    public function rules(): array
    {

        return [

            'name' => ['required', 'string', 'max:120', 'unique:motivos_cambio,name'],

            'descripcion' => ['nullable', 'string', 'max:1000'],

            'activo' => ['boolean'],

        ];

    }

    protected function prepareForValidation(): void
    {

        $this->merge(['activo' => $this->boolean('activo')]);

    }
}
