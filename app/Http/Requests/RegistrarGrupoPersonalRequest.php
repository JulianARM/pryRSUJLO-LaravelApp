<?php

namespace App\Http\Requests;

use App\Models\GrupoPersonal;
use App\Models\Personal;
use App\Models\TipoPersonal;
use App\Models\Vehiculo;
use App\Services\DisponibilidadGrupoPersonalService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RegistrarGrupoPersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', 'unique:grupos_personal,name'],
            'turno_id' => ['required', 'exists:turnos,id'],
            'zona_id' => ['required', 'exists:zonas,id'],
            'vehiculo_id' => ['required', 'exists:vehiculos,id'],
            'conductor_id' => ['required', 'exists:personal,id'],
            'helper_ids' => ['present', 'array'],
            'helper_ids.*' => ['required', 'distinct', 'exists:personal,id'],
            'dias_semana' => ['required', 'array', 'min:1'],
            'dias_semana.*' => ['required', 'integer', Rule::in(array_keys(GrupoPersonal::DAYS))],
            'activo' => ['required', 'boolean'],
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
        $validator->after(fn (Validator $validator) => $this->validatePeople($validator));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'helper_ids' => array_values(array_filter((array) $this->input('helper_ids', []))),
            'dias_semana' => array_values(array_filter((array) $this->input('dias_semana', []))),
            'activo' => $this->boolean('activo'),
        ]);
    }

    protected function validatePeople(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $expectedHelpers = $this->expectedHelperCount();

        if (count($this->helper_ids) !== $expectedHelpers) {
            $validator->errors()->add('helper_ids', "El vehículo seleccionado requiere exactamente {$expectedHelpers} ayudante(s).");

            return;
        }

        if (in_array((int) $this->conductor_id, array_map('intval', $this->helper_ids), true)) {
            $validator->errors()->add('helper_ids', 'El conductor no puede figurar también como ayudante.');
        }

        $driver = Personal::with('staffType')->find($this->conductor_id);

        if (! $driver?->activo || $driver->staffType?->name !== TipoPersonal::DRIVER) {
            $validator->errors()->add('conductor_id', 'Debe seleccionar un conductor activo.');
        }

        $helpers = Personal::with('staffType')->whereIn('id', $this->helper_ids)->get();

        foreach ($helpers as $helper) {
            if (! $helper->activo || $helper->staffType?->name === TipoPersonal::DRIVER) {
                $validator->errors()->add('helper_ids', 'Los ayudantes deben ser personal activo de apoyo.');

                return;
            }
        }

        $issues = app(DisponibilidadGrupoPersonalService::class)->issues($this->groupData(), $this->route('personnel_group'));

        foreach ($issues as $issue) {
            $validator->errors()->add('availability', $issue);
        }
    }

    public function groupData(): array
    {
        return $this->only([
            'turno_id',
            'zona_id',
            'vehiculo_id',
            'conductor_id',
            'helper_ids',
            'dias_semana',
        ]);
    }

    protected function expectedHelperCount(): int
    {
        $vehicle = Vehiculo::find($this->input('vehiculo_id'));

        return max(((int) ($vehicle?->capacidad_personas ?? 1)) - 1, 0);
    }
}
