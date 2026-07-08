<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarTurnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shiftId = $this->route('turno')?->id ?? $this->route('shift')?->id;

        return [
            'name' => ['required', 'string', 'max:80', 'unique:turnos,name,'.$shiftId],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
