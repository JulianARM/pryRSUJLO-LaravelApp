<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RegistrarCoordenadasZonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coordinates' => ['required', 'json'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $coordinates = $this->coordinates();

            if (count($coordinates) < 3) {
                $validator->errors()->add('coordinates', 'Debe registrar al menos 3 coordenadas para formar el perímetro de la zona.');

                return;
            }

            foreach ($coordinates as $coordinate) {
                if (! isset($coordinate['lat'], $coordinate['lng']) ||
                    ! is_numeric($coordinate['lat']) ||
                    ! is_numeric($coordinate['lng']) ||
                    $coordinate['lat'] < -90 ||
                    $coordinate['lat'] > 90 ||
                    $coordinate['lng'] < -180 ||
                    $coordinate['lng'] > 180) {
                    $validator->errors()->add('coordinates', 'Las coordenadas ingresadas no son válidas.');

                    return;
                }
            }
        });
    }

    public function coordinates(): array
    {
        return json_decode($this->input('coordinates', '[]'), true) ?: [];
    }
}
