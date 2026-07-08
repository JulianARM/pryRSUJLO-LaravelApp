<?php

namespace App\Http\Requests;

use App\Models\Contrato;
use App\Models\Personal;
use App\Models\SolicitudVacacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class ActualizarSolicitudVacacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personal_id' => ['required', 'exists:personal,id'],
            'dias_solicitados' => ['required', 'integer', 'min:1', 'max:30'],
            'fecha_inicio' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $vacation = $this->route('vacacione') ?? $this->route('vacation') ?? $this->route('vacation_request');

            if ($vacation && $vacation->status !== SolicitudVacacion::STATUS_PENDING) {
                $validator->errors()->add('personal_id', 'Solo las solicitudes pendientes pueden editarse.');

                return;
            }

            $employee = Personal::find($this->personal_id);

            if (! $employee?->activo) {
                $validator->errors()->add('personal_id', 'El personal seleccionado no está activo.');

                return;
            }

            $hasValidContrato = Contrato::where('personal_id', $this->personal_id)
                ->where('activo', true)
                ->whereIn('tipo_contrato', [Contrato::TYPE_PERMANENT, Contrato::TYPE_NAMED])
                ->whereDate('fecha_inicio', '<=', $this->fecha_inicio)
                ->where(function ($query) {
                    $query->whereNull('fecha_fin')
                        ->orWhereDate('fecha_fin', '>=', $this->fecha_inicio);
                })
                ->exists();

            if (! $hasValidContrato) {
                $validator->errors()->add('personal_id', 'Solo personal con contrato permanente o nombrado activo puede solicitar vacaciones.');

                return;
            }

            $availableDays = $employee->vacationDaysAvailable(Carbon::parse($this->fecha_inicio)->year);

            if ((int) $this->dias_solicitados > $availableDays) {
                $validator->errors()->add('dias_solicitados', "El personal seleccionado solo tiene {$availableDays} días disponibles para vacaciones.");

                return;
            }

            if ($this->hasDateOverlap($vacation)) {
                $validator->errors()->add('fecha_inicio', 'El período solicitado coincide con una solicitud pendiente o aprobada.');
            }
        });
    }

    private function hasDateOverlap(?SolicitudVacacion $vacation): bool
    {
        $endDate = Carbon::parse($this->fecha_inicio)->addDays((int) $this->dias_solicitados - 1)->format('Y-m-d');

        return SolicitudVacacion::where('personal_id', $this->personal_id)
            ->whereIn('status', [SolicitudVacacion::STATUS_PENDING, SolicitudVacacion::STATUS_APPROVED])
            ->when($vacation, fn ($query) => $query->whereKeyNot($vacation->id))
            ->whereDate('fecha_inicio', '<=', $endDate)
            ->whereDate('fecha_fin', '>=', $this->fecha_inicio)
            ->exists();
    }
}
