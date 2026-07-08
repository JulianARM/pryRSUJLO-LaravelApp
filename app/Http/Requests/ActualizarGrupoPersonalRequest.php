<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ActualizarGrupoPersonalRequest extends RegistrarGrupoPersonalRequest
{
    public function rules(): array
    {
        $group = $this->route('personnel_group');

        return array_replace(parent::rules(), [
            'name' => ['required', 'string', 'max:120', Rule::unique('grupos_personal', 'name')->ignore($group)],
        ]);
    }
}
