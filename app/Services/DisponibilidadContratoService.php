<?php

namespace App\Services;

use App\Models\Contrato;
use Illuminate\Support\Carbon;

class DisponibilidadContratoService
{
    public const REHIRE_WAIT_MONTHS = 2;

    public function firstIssue(array $data, ?Contrato $ignoredContrato = null): ?string
    {
        if (! ($data['activo'] ?? false) || blank($data['personal_id'] ?? null) || blank($data['fecha_inicio'] ?? null)) {
            return null;
        }

        $employeeId = (int) $data['personal_id'];
        $startDate = Carbon::parse($data['fecha_inicio'])->startOfDay();
        $baseQuery = Contrato::where('personal_id', $employeeId);

        if ($ignoredContrato) {
            $baseQuery->whereKeyNot($ignoredContrato->id);
        }

        $hasActiveOverlap = (clone $baseQuery)
            ->where('activo', true)
            ->where(function ($query) use ($startDate) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $startDate);
            })
            ->exists();

        if ($hasActiveOverlap) {
            return 'El personal seleccionado ya tiene un contrato activo vigente o que se cruza con la fecha de inicio indicada.';
        }

        $latestEndedContrato = (clone $baseQuery)
            ->whereNotNull('fecha_fin')
            ->whereDate('fecha_fin', '<', $startDate)
            ->latest('fecha_fin')
            ->first();

        if (! $latestEndedContrato) {
            return null;
        }

        $availableFrom = $latestEndedContrato->fecha_fin->copy()->addMonthsNoOverflow(self::REHIRE_WAIT_MONTHS)->startOfDay();

        if ($startDate->lt($availableFrom)) {
            return 'Este personal podra ser recontratado desde '.$availableFrom->format('d/m/Y').'; deben transcurrir '.self::REHIRE_WAIT_MONTHS.' meses desde el fin del contrato anterior.';
        }

        return null;
    }
}
