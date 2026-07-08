<?php

namespace App\Services;

use App\Models\Feriado;
use App\Models\GrupoPersonal;
use App\Models\Programacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProgramacionMasivaService
{
    public function __construct(
        private readonly DisponibilidadProgramacionService $availability
    ) {}

    public function groups(?int $shiftId = null): Collection
    {
        return GrupoPersonal::with(['shift', 'zone', 'vehicle', 'driver.staffType', 'helpers.staffType'])
            ->where('activo', true)
            ->when($shiftId, fn ($query) => $query->where('turno_id', $shiftId))
            ->orderBy('name')
            ->get();
    }

    public function feriados(?string $startDate, ?string $endDate): Collection
    {
        if (! $startDate || ! $endDate) {
            return collect();
        }

        return Feriado::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    public function rows(array $data, Collection $groups): array
    {
        $postedRows = collect($data['groups'] ?? []);
        $hasPostedRows = $postedRows->isNotEmpty();

        return $groups
            ->mapWithKeys(function (GrupoPersonal $group) use ($postedRows, $hasPostedRows) {
                $row = $postedRows->get((string) $group->id, []);

                return [
                    $group->id => [
                        'enabled' => array_key_exists('enabled', $row)
                            ? filter_var($row['enabled'], FILTER_VALIDATE_BOOLEAN)
                            : ! $hasPostedRows,
                        'conductor_id' => (int) ($row['conductor_id'] ?? $group->conductor_id),
                        'helper_ids' => array_values(array_filter((array) ($row['helper_ids'] ?? $group->helpers->pluck('id')->all()))),
                    ],
                ];
            })
            ->all();
    }

    public function validateRows(array $data, Collection $groups, array $rows): array
    {
        $results = $groups
            ->mapWithKeys(function (GrupoPersonal $group) use ($data, $rows) {
                $row = $rows[$group->id] ?? [];

                if (! ($row['enabled'] ?? false)) {
                    return [$group->id => [
                        'available' => true,
                        'skipped' => true,
                        'issues' => [],
                        'warnings' => [],
                        'suggestions' => [],
                        'dates' => [],
                        'count' => 0,
                    ]];
                }

                $payload = $this->payloadForGroup($data, $group, $row);
                $expectedHelpers = max(((int) $group->vehicle->capacidad_personas) - 1, 0);
                $helperIds = collect($payload['helper_ids'])->map(fn ($id) => (int) $id)->filter()->values();
                $issues = [];

                if ($helperIds->count() !== $expectedHelpers) {
                    $issues[] = "El grupo requiere exactamente {$expectedHelpers} ayudante(s) por la capacidad del vehículo.";
                }

                if ($helperIds->unique()->count() !== $helperIds->count()) {
                    $issues[] = 'Los ayudantes deben ser personas diferentes.';
                }

                if ($helperIds->contains((int) $payload['conductor_id'])) {
                    $issues[] = 'El conductor no puede figurar también como ayudante.';
                }

                $availabilityIssues = empty($issues)
                    ? $this->availability->issues($payload)
                    : [];

                $issues = array_values(array_unique([...$issues, ...$availabilityIssues]));
                $dates = $this->availability->dates($payload);

                return [$group->id => [
                    'available' => empty($issues),
                    'skipped' => false,
                    'issues' => $issues,
                    'warnings' => $this->availability->holidayWarnings($payload),
                    'suggestions' => empty($issues) ? [] : $this->availability->suggestions($payload),
                    'dates' => $dates->map->format('d/m/Y')->values()->all(),
                    'count' => $dates->count(),
                ]];
            })
            ->all();

        return $this->appendBatchConflicts($data, $groups, $rows, $results);
    }

    public function create(array $data, Collection $groups, array $rows, string $reason): int
    {
        $results = $this->validateRows($data, $groups, $rows);
        $issues = collect($results)->flatMap(fn ($result) => $result['issues'] ?? []);

        if ($issues->isNotEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($data, $groups, $rows, $reason) {
            $created = 0;

            foreach ($groups as $group) {
                $row = $rows[$group->id] ?? [];

                if (! ($row['enabled'] ?? false)) {
                    continue;
                }

                $payload = $this->payloadForGroup($data, $group, $row);

                foreach ($this->availability->dates($payload) as $date) {
                    $schedule = Programacion::create([
                        'grupo_personal_id' => $group->id,
                        'turno_id' => $group->turno_id,
                        'zona_id' => $group->zona_id,
                        'vehiculo_id' => $group->vehiculo_id,
                        'conductor_id' => $payload['conductor_id'],
                        'fecha_programada' => $date->format('Y-m-d'),
                        'status' => Programacion::STATUS_SCHEDULED,
                        'notes' => $data['notes'] ?? null,
                    ]);
                    $schedule->helpers()->sync($payload['helper_ids']);
                    $schedule->changes()->create([
                        'action' => 'mass_created',
                        'descripcion' => $reason,
                        'valores_nuevos' => $schedule->load(['helpers'])->toArray(),
                    ]);
                    $created++;
                }
            }

            return $created;
        });
    }

    public function hasBlockingIssues(array $results): bool
    {
        return collect($results)
            ->reject(fn ($result) => $result['skipped'] ?? false)
            ->contains(fn ($result) => ! ($result['available'] ?? false));
    }

    private function payloadForGroup(array $data, GrupoPersonal $group, array $row): array
    {
        return [
            'grupo_personal_id' => $group->id,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'turno_id' => $group->turno_id,
            'zona_id' => $group->zona_id,
            'vehiculo_id' => $group->vehiculo_id,
            'conductor_id' => $row['conductor_id'] ?? $group->conductor_id,
            'helper_ids' => array_values(array_filter((array) ($row['helper_ids'] ?? []))),
            'dias_semana' => $group->dias_semana ?? [],
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function appendBatchConflicts(array $data, Collection $groups, array $rows, array $results): array
    {
        $vehiculos = [];
        $people = [];
        $groupZonas = [];

        foreach ($groups as $group) {
            $row = $rows[$group->id] ?? [];

            if (! ($row['enabled'] ?? false)) {
                continue;
            }

            $payload = $this->payloadForGroup($data, $group, $row);
            $dates = $this->availability->dates($payload);
            $personIds = collect([$payload['conductor_id']])->merge($payload['helper_ids'])->map(fn ($id) => (int) $id)->filter()->unique();

            foreach ($dates as $date) {
                $dateLabel = $date->format('d/m/Y');
                $groupZonaKey = "{$payload['turno_id']}-{$date->format('Y-m-d')}-{$payload['zona_id']}-{$payload['grupo_personal_id']}";
                $vehicleKey = "{$payload['turno_id']}-{$date->format('Y-m-d')}-{$payload['vehiculo_id']}";

                if (isset($groupZonas[$groupZonaKey])) {
                    $message = "{$dateLabel}: este grupo ya está incluido para el mismo turno y zona dentro de esta programación masiva.";
                    $results = $this->addIssue($results, $group->id, $message);
                    $results = $this->addIssue($results, $groupZonas[$groupZonaKey]['id'], "{$dateLabel}: este grupo también está incluido para el mismo turno y zona dentro de esta programación masiva.");
                } else {
                    $groupZonas[$groupZonaKey] = ['id' => $group->id, 'name' => $group->name];
                }

                if (isset($vehiculos[$vehicleKey])) {
                    $message = "{$dateLabel}: el vehículo también está seleccionado en {$vehiculos[$vehicleKey]['name']} dentro de esta programación masiva.";
                    $results = $this->addIssue($results, $group->id, $message);
                    $results = $this->addIssue($results, $vehiculos[$vehicleKey]['id'], "{$dateLabel}: el vehículo también está seleccionado en {$group->name} dentro de esta programación masiva.");
                } else {
                    $vehiculos[$vehicleKey] = ['id' => $group->id, 'name' => $group->name];
                }

                foreach ($personIds as $personId) {
                    $personKey = "{$payload['turno_id']}-{$date->format('Y-m-d')}-{$personId}";

                    if (isset($people[$personKey])) {
                        $message = "{$dateLabel}: una persona del equipo también está seleccionada en {$people[$personKey]['name']} dentro de esta programación masiva.";
                        $results = $this->addIssue($results, $group->id, $message);
                        $results = $this->addIssue($results, $people[$personKey]['id'], "{$dateLabel}: una persona del equipo también está seleccionada en {$group->name} dentro de esta programación masiva.");
                    } else {
                        $people[$personKey] = ['id' => $group->id, 'name' => $group->name];
                    }
                }
            }
        }

        return $results;
    }

    private function addIssue(array $results, int $groupId, string $message): array
    {
        $results[$groupId]['issues'] = array_values(array_unique([
            ...($results[$groupId]['issues'] ?? []),
            $message,
        ]));
        $results[$groupId]['available'] = false;

        return $results;
    }
}
