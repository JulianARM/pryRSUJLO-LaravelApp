<?php

namespace App\Http\Requests;

use App\Models\Asistencia;
use App\Models\Personal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RegistrarAsistenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personal_id' => ['required', 'exists:personal,id'],
            'turno_id' => ['nullable', 'exists:turnos,id'],
            'fecha_asistencia' => ['required', 'date'],
            'hora_asistencia' => ['required', 'date_format:H:i'],
            'type' => ['nullable', Rule::in(array_keys(Asistencia::TYPES))],
            'status' => ['required', Rule::in(array_keys(Asistencia::STATUSES))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $employee = Personal::find($this->personal_id);

            if (! $employee?->activo) {
                $validator->errors()->add('personal_id', 'El personal seleccionado no está activo.');

                return;
            }

            $suggestedType = Asistencia::where('personal_id', $this->personal_id)
                ->whereDate('fecha_asistencia', $this->fecha_asistencia)
                ->latest('registrado_en')
                ->value('type') === Asistencia::TYPE_IN
                    ? Asistencia::TYPE_OUT
                    : Asistencia::TYPE_IN;

            $duplicate = Asistencia::where('personal_id', $this->personal_id)
                ->whereDate('fecha_asistencia', $this->fecha_asistencia)
                ->where('type', $suggestedType)
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('type', 'Ya existen los registros de entrada y salida para el personal en la fecha indicada.');
            }
        });
    }
}
