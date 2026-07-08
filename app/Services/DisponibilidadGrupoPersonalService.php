<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\GrupoPersonal;
use App\Models\Personal;
use App\Models\SolicitudVacacion;
use App\Models\Vehiculo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DisponibilidadGrupoPersonalService
{
    public function report(array $data, ?GrupoPersonal $ignoredGroup = null): array
    {
        $days = collect($data['dias_semana'] ?? [])->map(fn ($day) => (int) $day)->values();
        $shiftId = (int) ($data['turno_id'] ?? 0);
        $vehicleId = (int) ($data['vehiculo_id'] ?? 0);
        $driverId = (int) ($data['conductor_id'] ?? 0);
        $helperIds = collect($data['helper_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $peopleIds = collect([$driverId])->merge($helperIds)->filter()->unique()->values();
        $issues = [];
        $generalIssues = [];
        $people = [];

        if ($shiftId && $days->isNotEmpty() && $vehicleId) {
            $vehicleConflicts = $this->baseConflictQuery($shiftId, $days, $ignoredGroup)
                ->where('vehiculo_id', $vehicleId)
                ->with('vehicle')
                ->get();

            if ($vehicleConflicts->isNotEmpty()) {
                $vehicle = Vehiculo::find($vehicleId);
                $message = "{$vehicle?->name} ya está asignado en {$this->groupsLabel($vehicleConflicts)} para los días seleccionados.";
                $issues[] = $message;
                $generalIssues[] = $message;
            }
        }

        foreach ($peopleIds as $personId) {
            $employee = Personal::with('staffType')->find($personId);

            if (! $employee) {
                continue;
            }

            $conflicts = $shiftId
                ? $this->personConflicts($personId, $shiftId, $ignoredGroup)
                : collect();
            $messages = $this->personMessages($employee, $conflicts, $days);
            $available = empty($messages);
            $message = $available
                ? 'Disponible para los días seleccionados.'
                : implode(' ', $messages);

            $people[] = [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'dni' => $employee->dni,
                'type' => $employee->staffType?->name,
                'role' => $personId === $driverId ? 'driver' : 'helper',
                'available' => $available,
                'message' => $message,
            ];

            if (! $available) {
                $issues[] = "{$employee->full_name}: {$message}";
            }
        }

        if ($driverId && $helperIds->contains($driverId)) {
            $message = 'El conductor no puede figurar también como ayudante.';
            $issues[] = $message;
            $generalIssues[] = $message;
        }

        return [
            'available' => empty($issues),
            'issues' => array_values(array_unique($issues)),
            'general_issues' => array_values(array_unique($generalIssues)),
            'people' => $people,
        ];
    }

    public function issues(array $data, ?GrupoPersonal $ignoredGroup = null): array
    {
        return $this->report($data, $ignoredGroup)['issues'];
    }

    private function personConflicts(int $employeeId, int $shiftId, ?GrupoPersonal $ignoredGroup): Collection
    {
        return GrupoPersonal::with(['shift', 'helpers'])
            ->where('activo', true)
            ->where('turno_id', $shiftId)
            ->when($ignoredGroup, fn ($query) => $query->whereKeyNot($ignoredGroup->id))
            ->where(function ($query) use ($employeeId) {
                $query->where('conductor_id', $employeeId)
                    ->orWhereHas('helpers', fn ($helpers) => $helpers->whereKey($employeeId));
            })
            ->get();
    }

    private function baseConflictQuery(int $shiftId, Collection $days, ?GrupoPersonal $ignoredGroup)
    {
        return GrupoPersonal::with(['shift', 'helpers'])
            ->where('activo', true)
            ->where('turno_id', $shiftId)
            ->where(function ($query) use ($days) {
                foreach ($days as $day) {
                    $query->orWhereJsonContains('dias_semana', $day);
                }
            })
            ->when($ignoredGroup, fn ($query) => $query->whereKeyNot($ignoredGroup->id));
    }

    private function personMessages(Personal $employee, Collection $conflicts, Collection $days): array
    {
        $messages = [];

        if (! $employee->activo) {
            $messages[] = 'El personal no está activo.';
        }

        if (! $this->hasActiveContrato($employee->id)) {
            $messages[] = 'No tiene contrato activo vigente.';
        }

        if ($vacationDate = $this->vacationDateForSelectedDays($employee, $days)) {
            $messages[] = 'Tiene vacaciones registradas para el '.$vacationDate->format('d/m/Y').'.';
        }

        if ($conflicts->isNotEmpty()) {
            $messages[] = 'No disponible por cruce con '.$this->groupsLabel($conflicts).'.';
        }

        return $messages;
    }

    private function hasActiveContrato(int $employeeId): bool
    {
        $today = now()->toDateString();

        return Contrato::where('personal_id', $employeeId)
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $today);
            })
            ->exists();
    }

    private function vacationDateForSelectedDays(Personal $employee, Collection $days): ?Carbon
    {
        if ($days->isEmpty()) {
            return null;
        }

        $dates = $this->upcomingDatesForDays($days);

        if ($dates->isEmpty()) {
            return null;
        }

        $start = $dates->min(fn (Carbon $date) => $date->toDateString());
        $end = $dates->max(fn (Carbon $date) => $date->toDateString());
        $vacations = $employee->vacationRequests()
            ->whereIn('status', [SolicitudVacacion::STATUS_PENDING, SolicitudVacacion::STATUS_APPROVED])
            ->whereDate('fecha_inicio', '<=', $end)
            ->whereDate('fecha_fin', '>=', $start)
            ->get();

        foreach ($dates as $date) {
            $overlaps = $vacations->contains(fn (SolicitudVacacion $vacation) => $date->betweenIncluded($vacation->fecha_inicio, $vacation->fecha_fin));

            if ($overlaps) {
                return $date;
            }
        }

        return null;
    }

    private function upcomingDatesForDays(Collection $days): Collection
    {
        $start = today();
        $end = $start->copy()->addDays(13);
        $dates = collect();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($days->contains($date->dayOfWeekIso)) {
                $dates->push($date->copy());
            }
        }

        return $dates;
    }

    private function groupsLabel(Collection $groups): string
    {
        return $groups
            ->map(fn (GrupoPersonal $group) => "{$group->name} ({$group->days_label})")
            ->implode(', ');
    }
}
