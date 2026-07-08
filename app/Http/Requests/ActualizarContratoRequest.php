<?php

namespace App\Http\Requests;

use App\Models\Contrato;
use App\Services\DisponibilidadContratoService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ActualizarContratoRequest extends FormRequest
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
        return [
            'personal_id' => ['required', 'exists:personal,id'],
            'tipo_contrato' => ['required', Rule::in(array_keys(Contrato::TYPES))],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'required_if:tipo_contrato,'.Contrato::TYPE_TEMPORARY, 'date', 'after_or_equal:fecha_inicio'],
            'salario' => ['required', 'numeric', 'min:0'],
            'meses_periodo_prueba' => ['nullable', 'integer', 'min:0', 'max:12'],
            'activo' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $message = app(DisponibilidadContratoService::class)->firstIssue(
                $this->contractData(),
                $this->route('contract')
            );

            if ($message) {
                $validator->errors()->add('personal_id', $message);
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo'),
        ]);
    }

    private function contractData(): array
    {
        return $this->only([
            'personal_id',
            'fecha_inicio',
            'activo',
        ]);
    }
}
