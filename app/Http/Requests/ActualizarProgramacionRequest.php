<?php

namespace App\Http\Requests;

use App\Models\GrupoPersonal;
use App\Models\Vehiculo;
use App\Services\DisponibilidadProgramacionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ActualizarProgramacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_programada' => ['required', 'date'],
            'turno_id' => ['required', 'exists:turnos,id'],
            'zona_id' => ['required', 'exists:zonas,id'],
            'vehiculo_id' => ['required', 'exists:vehiculos,id'],
            'conductor_id' => ['required', 'exists:personal,id'],
            'helper_ids' => ['present', 'array'],
            'helper_ids.*' => ['required', 'distinct', 'exists:personal,id'],
            'dias_semana' => ['nullable', 'array'],
            'dias_semana.*' => ['integer', Rule::in(array_keys(GrupoPersonal::DAYS))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'change_reason' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'helper_ids.array' => 'Debe seleccionar los ayudantes requeridos por la capacidad del vehículo.',
            'helper_ids.*.distinct' => 'Los ayudantes deben ser personas diferentes.',
            'change_reason.required' => 'Debe especificar el motivo de la reprogramación.',
            'change_reason.min' => 'El motivo de la reprogramación debe tener al menos 5 caracteres.',
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

            $schedule = $this->route('route_schedule');
            $issues = app(DisponibilidadProgramacionService::class)->issues($this->scheduleData(), $schedule);

            foreach ($issues as $issue) {
                $validator->errors()->add('availability', $issue);
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $scheduleDate = $this->input('fecha_programada');

        $this->merge([
            'helper_ids' => array_values(array_filter((array) $this->input('helper_ids', []))),
            'dias_semana' => $scheduleDate ? [(int) Carbon::parse($scheduleDate)->dayOfWeekIso] : [],
        ]);
    }

    public function scheduleData(): array
    {
        return [
            'fecha_inicio' => $this->input('fecha_programada'),
            'fecha_fin' => $this->input('fecha_programada'),
            'turno_id' => $this->input('turno_id'),
            'zona_id' => $this->input('zona_id'),
            'vehiculo_id' => $this->input('vehiculo_id'),
            'conductor_id' => $this->input('conductor_id'),
            'helper_ids' => $this->input('helper_ids', []),
            'dias_semana' => $this->input('dias_semana', []),
            'notes' => $this->input('notes'),
        ];
    }

    protected function expectedHelperCount(): int
    {
        $vehicle = Vehiculo::find($this->input('vehiculo_id'));

        return max(((int) ($vehicle?->capacidad_personas ?? 1)) - 1, 0);
    }
}
