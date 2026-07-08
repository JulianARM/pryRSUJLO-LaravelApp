<?php

namespace App\Http\Requests;

use App\Models\GrupoPersonal;
use App\Models\Vehiculo;
use App\Services\DisponibilidadProgramacionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RegistrarProgramacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grupo_personal_id' => ['required', 'exists:grupos_personal,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'turno_id' => ['required', 'exists:turnos,id'],
            'zona_id' => ['required', 'exists:zonas,id'],
            'vehiculo_id' => ['required', 'exists:vehiculos,id'],
            'conductor_id' => ['required', 'exists:personal,id'],
            'helper_ids' => ['present', 'array'],
            'helper_ids.*' => ['required', 'distinct', 'exists:personal,id'],
            'dias_semana' => ['required', 'array', 'min:1'],
            'dias_semana.*' => ['required', 'integer', Rule::in(array_keys(GrupoPersonal::DAYS))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'helper_ids.array' => 'Debe seleccionar los ayudantes requeridos por la capacidad del vehículo.',
            'helper_ids.*.distinct' => 'Los ayudantes deben ser personas diferentes.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $expectedHelpers = $this->expectedHelperCount();

            if (count($this->input('helper_ids', [])) !== $expectedHelpers) {
                $validator->errors()->add('helper_ids', "La programación debe contar exactamente con {$expectedHelpers} ayudante(s).");

                return;
            }

            $issues = app(DisponibilidadProgramacionService::class)->issues($this->scheduleData());

            foreach ($issues as $issue) {
                $validator->errors()->add('availability', $issue);
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'helper_ids' => array_values(array_filter((array) $this->input('helper_ids', []))),
            'dias_semana' => array_values(array_filter((array) $this->input('dias_semana', []))),
        ]);
    }

    public function scheduleData(): array
    {
        return $this->only([
            'grupo_personal_id',
            'fecha_inicio',
            'fecha_fin',
            'turno_id',
            'zona_id',
            'vehiculo_id',
            'conductor_id',
            'helper_ids',
            'dias_semana',
            'notes',
        ]);
    }

    protected function expectedHelperCount(): int
    {
        $vehicle = Vehiculo::find($this->input('vehiculo_id'));

        return max(((int) ($vehicle?->capacidad_personas ?? 1)) - 1, 0);
    }
}
