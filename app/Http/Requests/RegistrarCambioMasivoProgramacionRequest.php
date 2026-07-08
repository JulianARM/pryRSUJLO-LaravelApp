<?php

namespace App\Http\Requests;

use App\Services\CambioMasivoProgramacionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RegistrarCambioMasivoProgramacionRequest extends FormRequest
{
    public function authorize(): bool
    {

        return true;

    }

    public function rules(): array
    {

        return [

            'fecha_inicio' => ['required', 'date'],

            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],

            'zona_id' => ['required', 'exists:zonas,id'],

            'tipo_cambio' => ['required', Rule::in(array_keys(CambioMasivoProgramacionService::TYPES))],

            'motivo_cambio_id' => ['required', 'exists:motivos_cambio,id'],

            'detail' => ['required', 'string', 'min:5', 'max:1000'],

            'turno_id' => ['nullable', 'exists:turnos,id'],

            'conductor_id' => ['nullable', 'exists:personal,id'],

            'helper_ids' => ['nullable', 'array'],

            'helper_ids.*' => ['nullable', 'distinct', 'exists:personal,id'],

            'vehiculo_id' => ['nullable', 'exists:vehiculos,id'],

            'confirm_mass_change' => ['accepted'],

        ];

    }

    public function messages(): array
    {

        return [

            'confirm_mass_change.accepted' => 'Debe confirmar que la operación es irreversible.',

        ];

    }

    public function withValidator(Validator $validator): void
    {

        $validator->after(function (Validator $validator) {

            if ($validator->errors()->isNotEmpty()) {

                return;

            }

            $requiredField = match ($this->input('tipo_cambio')) {

                'shift' => 'turno_id',

                'driver' => 'conductor_id',

                'helper' => 'helper_ids',

                'vehicle' => 'vehiculo_id',

                default => null,

            };

            if ($requiredField === 'helper_ids' && empty($this->input('helper_ids', []))) {

                $validator->errors()->add('helper_ids', 'Debe seleccionar los ocupantes de reemplazo.');

            }

            if ($requiredField && $requiredField !== 'helper_ids' && ! $this->filled($requiredField)) {

                $validator->errors()->add($requiredField, 'Debe seleccionar el nuevo valor para el tipo de cambio elegido.');

            }

        });

    }

    protected function prepareForValidation(): void
    {

        $this->merge([

            'helper_ids' => array_values(array_filter((array) $this->input('helper_ids', []))),

            'confirm_mass_change' => $this->boolean('confirm_mass_change'),

        ]);

    }
}
