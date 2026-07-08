<?php

namespace App\Http\Requests;

use App\Models\TipoPersonal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarPersonalRequest extends FormRequest
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
        $employeeId = $this->route('employee')?->id;

        return [
            'tipo_personal_id' => ['required', 'exists:tipos_personal,id'],
            'dni' => ['required', 'digits:8', 'unique:personal,dni,'.$employeeId],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->format('Y-m-d')],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:120', 'unique:personal,email,'.$employeeId],
            'licencia' => [
                Rule::requiredIf(fn () => $this->selectedTypeIsDriver()),
                'nullable',
                'string',
                'max:30',
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'direccion' => ['required', 'string', 'min:10', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'activo' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo', true),
        ]);
    }

    private function selectedTypeIsDriver(): bool
    {
        return TipoPersonal::where('id', $this->tipo_personal_id)
            ->where('name', TipoPersonal::DRIVER)
            ->exists();
    }
}
