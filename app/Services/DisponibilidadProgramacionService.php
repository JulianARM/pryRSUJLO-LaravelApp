<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Feriado;
use App\Models\Personal;
use App\Models\Programacion;
use App\Models\SolicitudVacacion;
use App\Models\TipoPersonal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DisponibilidadProgramacionService
{
    public function dates(array $data): Collection
    {
        return $this->candidateDates($data)
            ->reject(fn (Carbon $date) => $this->activeFeriado($date) !== null)
            ->values();
    }

    public function candidateDates(array $data): Collection
    {
        $start = Carbon::parse($data['fecha_inicio']);
        $end = Carbon::parse($data['fecha_fin']);
        $days = collect($data['dias_semana'] ?? [])->map(fn ($day) => (int) $day)->values();
        $dates = collect();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($days->contains($date->dayOfWeekIso)) {
                $dates->push($date->copy());
            }
        }

        return $dates;
    }

    public function holidayWarnings(array $data): array
    {
        return $this->candidateDates($data)
            ->map(function (Carbon $date) {
                $holiday = $this->activeFeriado($date);

                return $holiday
                    ? $date->format('d/m/Y').": feriado activo ({$holiday->descripcion}). No se generará programación para esta fecha."
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    public function issues(array $data, ?Programacion $ignoredSchedule = null): array
    {
        $issues = [];
        $candidateDates = $this->candidateDates($data);
        $dates = $this->dates($data);
        $employeeIds = collect([$data['conductor_id'] ?? null])
            ->merge($data['helper_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($candidateDates->isEmpty()) {
            return ['No se encontraron fechas programables con los días seleccionados.'];
        }

        if ($dates->isEmpty()) {
            return ['Todas las fechas programables coinciden con feriados activos. No hay registros por generar.'];
        }

        foreach ($dates as $date) {
            $dateLabel = $date->format('d/m/Y');

            if ($this->groupBusy(
                (int) ($data['grupo_personal_id'] ?? 0),
                (int) $data['turno_id'],
                (int) ($data['zona_id'] ?? 0),
                $date,
                $ignoredSchedule
            )) {
                $issues[] = "{$dateLabel}: el grupo seleccionado ya tiene una programación para este turno y zona.";
            }

            if ($this->vehicleBusy((int) $data['vehiculo_id'], (int) $data['turno_id'], $date, $ignoredSchedule)) {
                $issues[] = "{$dateLabel}: el vehículo seleccionado ya tiene una programación en este turno.";
            }

            foreach ($employeeIds as $employeeId) {
                $employee = Personal::find($employeeId);

                if (! $employee?->activo) {
                    $issues[] = "{$dateLabel}: {$employee?->full_name} no está activo.";

                    continue;
                }

                if (! $this->hasActiveContrato($employeeId, $date)) {
                    $issues[] = "{$dateLabel}: {$employee->full_name} no tiene contrato activo vigente.";
                }

                if ($this->employeeOnVacation($employeeId, $date)) {
                    $issues[] = "{$dateLabel}: {$employee->full_name} tiene vacaciones registradas.";
                }

                if ($this->employeeBusy($employeeId, (int) $data['turno_id'], $date, $ignoredSchedule)) {
                    $issues[] = "{$dateLabel}: {$employee->full_name} ya está programado en este turno.";
                }
            }
        }

        return array_values(array_unique($issues));
    }

    public function suggestions(array $data, ?Programacion $ignoredSchedule = null): array
    {
        $dates = $this->dates($data);

        if ($dates->isEmpty()) {
            return [];
        }

        $shiftId = (int) ($data['turno_id'] ?? 0);
        $driverId = (int) ($data['conductor_id'] ?? 0);
        $helperIds = collect($data['helper_ids'] ?? [])->map(fn ($id) => (int) $id)->values();
        $selectedIds = collect([$driverId])->merge($helperIds)->filter()->unique()->values();
        $suggestions = [];

        if ($driverId && $this->personIssues($driverId, $shiftId, $dates, $ignoredSchedule)) {
            $suggestions[] = [
                'role' => 'driver',
                'label' => 'Conductor',
                'current' => $this->personLabel($driverId),
                'replacements' => $this->availablePeople(TipoPersonal::DRIVER, $shiftId, $dates, $ignoredSchedule, $selectedIds),
            ];
        }

        foreach ($helperIds as $index => $helperId) {
            if (! $helperId || ! $this->personIssues($helperId, $shiftId, $dates, $ignoredSchedule)) {
                continue;
            }

            $suggestions[] = [
                'role' => 'helper',
                'index' => $index,
                'label' => 'Ayudante '.($index + 1),
                'current' => $this->personLabel($helperId),
                'replacements' => $this->availablePeople(null, $shiftId, $dates, $ignoredSchedule, $selectedIds),
            ];
        }

        return $suggestions;
    }

    private function activeFeriado(Carbon $date): ?Feriado
    {
        return Feriado::whereDate('date', $date)
            ->where('activo', true)
            ->first();
    }

    private function groupBusy(int $groupId, int $shiftId, int $zoneId, Carbon $date, ?Programacion $ignoredSchedule): bool
    {
        if (! $groupId || ! $zoneId) {
            return false;
        }

        return Programacion::where('grupo_personal_id', $groupId)
            ->where('turno_id', $shiftId)
            ->where('zona_id', $zoneId)
            ->whereDate('fecha_programada', $date)
            ->where('status', '!=', Programacion::STATUS_CANCELLED)
            ->when($ignoredSchedule, fn ($query) => $query->whereKeyNot($ignoredSchedule->id))
            ->exists();
    }

    private function vehicleBusy(int $vehicleId, int $shiftId, Carbon $date, ?Programacion $ignoredSchedule): bool
    {
        return Programacion::where('vehiculo_id', $vehicleId)
            ->where('turno_id', $shiftId)
            ->whereDate('fecha_programada', $date)
            ->where('status', '!=', Programacion::STATUS_CANCELLED)
            ->when($ignoredSchedule, fn ($query) => $query->whereKeyNot($ignoredSchedule->id))
            ->exists();
    }

    private function employeeBusy(int $employeeId, int $shiftId, Carbon $date, ?Programacion $ignoredSchedule): bool
    {
        return Programacion::where('turno_id', $shiftId)
            ->whereDate('fecha_programada', $date)
            ->where('status', '!=', Programacion::STATUS_CANCELLED)
            ->when($ignoredSchedule, fn ($query) => $query->whereKeyNot($ignoredSchedule->id))
            ->where(function ($query) use ($employeeId) {
                $query->where('conductor_id', $employeeId)
                    ->orWhereHas('helpers', fn ($helpers) => $helpers->whereKey($employeeId));
            })
            ->exists();
    }

    private function employeeOnVacation(int $employeeId, Carbon $date): bool
    {
        return Personal::find($employeeId)?->vacationRequests()
            ->whereIn('status', [SolicitudVacacion::STATUS_PENDING, SolicitudVacacion::STATUS_APPROVED])
            ->whereDate('fecha_inicio', '<=', $date)
            ->whereDate('fecha_fin', '>=', $date)
            ->exists() ?? false;
    }

    private function hasActiveContrato(int $employeeId, Carbon $date): bool
    {
        return Contrato::where('personal_id', $employeeId)
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $date);
            })
            ->exists();
    }

    public function personIssues(int $employeeId, int $shiftId, Collection $dates, ?Programacion $ignoredSchedule): array
    {
        $employee = Personal::find($employeeId);
        $issues = [];

        if (! $employee?->activo) {
            return ['No está activo.'];
        }

        foreach ($dates as $date) {
            if (! $this->hasActiveContrato($employeeId, $date)) {
                $issues[] = 'No tiene contrato activo vigente.';
            }

            if ($this->employeeOnVacation($employeeId, $date)) {
                $issues[] = 'Tiene vacaciones registradas.';
            }

            if ($this->employeeBusy($employeeId, $shiftId, $date, $ignoredSchedule)) {
                $issues[] = 'Ya está programado en este turno.';
            }
        }

        return array_values(array_unique($issues));
    }

    public function availablePeople(?string $staffTypeName, int $shiftId, Collection $dates, ?Programacion $ignoredSchedule, Collection $excludedIds): array
    {
        return Personal::with('staffType')
            ->where('activo', true)
            ->whereNotIn('id', $excludedIds->all())
            ->when(
                $staffTypeName === TipoPersonal::DRIVER,
                fn ($query) => $query->whereHas('staffType', fn ($type) => $type->where('name', TipoPersonal::DRIVER)),
                fn ($query) => $query->whereHas('staffType', fn ($type) => $type->where('name', '!=', TipoPersonal::DRIVER))
            )
            ->orderBy('nombres')
            ->get()
            ->filter(fn (Personal $employee) => empty($this->personIssues($employee->id, $shiftId, $dates, $ignoredSchedule)))
            ->take(3)
            ->map(fn (Personal $employee) => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'dni' => $employee->dni,
                'type' => $employee->staffType?->name,
                'label' => "{$employee->full_name} - {$employee->dni}",
            ])
            ->values()
            ->all();
    }

    private function personLabel(int $employeeId): string
    {
        $employee = Personal::find($employeeId);

        return $employee ? "{$employee->full_name} - {$employee->dni}" : 'Personal no encontrado';
    }
}
