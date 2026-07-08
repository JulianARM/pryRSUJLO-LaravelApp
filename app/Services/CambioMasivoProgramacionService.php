<?php

namespace App\Services;

use App\Models\MotivoCambio;
use App\Models\Programacion;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CambioMasivoProgramacionService
{
    public const TYPES = [
        'shift' => 'Cambio de turno',
        'driver' => 'Cambio de conductor',
        'helper' => 'Cambio de ocupante',
        'vehicle' => 'Cambio de vehículo',
    ];

    public function __construct(private readonly DisponibilidadProgramacionService $availability) {}

    public function apply(array $data, ?User $user): int
    {
        $schedules = $this->affectedSchedules($data);
        $issues = $this->issues($data, $schedules);

        if ($schedules->isEmpty()) {
            throw ValidationException::withMessages([
                'availability' => 'No existen programaciones modificables para el rango y zona seleccionados.',
            ]);
        }

        if (! empty($issues)) {
            throw ValidationException::withMessages(['availability' => $issues]);
        }

        $reason = MotivoCambio::findOrFail($data['motivo_cambio_id']);
        $batchUuid = (string) Str::uuid();

        return DB::transaction(function () use ($data, $schedules, $reason, $user, $batchUuid) {
            foreach ($schedules as $schedule) {
                $oldValues = $schedule->load(['shift', 'zone', 'vehicle', 'driver', 'helpers'])->toArray();
                $this->applyTarget($schedule, $data);
                $schedule->update(['status' => Programacion::STATUS_REPROGRAMMED]);
                $schedule->changes()->create([
                    'usuario_id' => $user?->id,
                    'motivo_cambio_id' => $reason->id,
                    'lote_uuid' => $batchUuid,
                    'action' => 'mass_change',
                    'tipo_cambio' => $data['tipo_cambio'],
                    'descripcion' => $reason->name,
                    'detail' => $data['detail'],
                    'valores_anteriores' => $oldValues,
                    'valores_nuevos' => $schedule->fresh(['shift', 'zone', 'vehicle', 'driver', 'helpers'])->toArray(),
                ]);
            }

            return $schedules->count();
        });
    }

    public function affectedSchedules(array $data): Collection
    {
        return Programacion::with(['shift', 'zone', 'vehicle', 'driver', 'helpers'])
            ->whereBetween('fecha_programada', [$data['fecha_inicio'], $data['fecha_fin']])
            ->where('zona_id', $data['zona_id'])
            ->whereNotIn('status', [Programacion::STATUS_FINALIZED, Programacion::STATUS_CANCELLED])
            ->orderBy('fecha_programada')
            ->get();
    }

    private function issues(array $data, Collection $schedules): array
    {
        return $schedules
            ->flatMap(function (Programacion $schedule) use ($data) {
                $issues = [];
                $payload = $this->payload($schedule, $data);

                if ($data['tipo_cambio'] === 'helper') {
                    $expected = max(((int) $schedule->vehicle->capacidad_personas) - 1, 0);
                    if (count($payload['helper_ids']) !== $expected) {
                        $issues[] = $schedule->fecha_programada->format('d/m/Y').": la programación {$schedule->id} requiere exactamente {$expected} ocupante(s).";
                    }
                }

                if ($data['tipo_cambio'] === 'vehicle') {
                    $currentExpected = max(((int) $schedule->vehicle->capacidad_personas) - 1, 0);
                    $newVehiculo = Vehiculo::find($data['vehiculo_id']);
                    $newExpected = max(((int) ($newVehiculo?->capacidad_personas ?? 1)) - 1, 0);
                    if ($currentExpected !== $newExpected) {
                        $issues[] = $schedule->fecha_programada->format('d/m/Y').": la programación {$schedule->id} requiere ajustar ocupantes antes de cambiar a un vehículo con capacidad distinta.";
                    }
                }

                return [...$issues, ...$this->availability->issues($payload, $schedule)];
            })
            ->unique()
            ->values()
            ->all();
    }

    private function payload(Programacion $schedule, array $data): array
    {
        return [
            'grupo_personal_id' => $schedule->grupo_personal_id,
            'fecha_inicio' => $schedule->fecha_programada->format('Y-m-d'),
            'fecha_fin' => $schedule->fecha_programada->format('Y-m-d'),
            'turno_id' => $data['tipo_cambio'] === 'shift' ? $data['turno_id'] : $schedule->turno_id,
            'zona_id' => $schedule->zona_id,
            'vehiculo_id' => $data['tipo_cambio'] === 'vehicle' ? $data['vehiculo_id'] : $schedule->vehiculo_id,
            'conductor_id' => $data['tipo_cambio'] === 'driver' ? $data['conductor_id'] : $schedule->conductor_id,
            'helper_ids' => $data['tipo_cambio'] === 'helper' ? $data['helper_ids'] : $schedule->helpers->pluck('id')->all(),
            'dias_semana' => [$schedule->fecha_programada->dayOfWeekIso],
        ];
    }

    private function applyTarget(Programacion $schedule, array $data): void
    {
        match ($data['tipo_cambio']) {
            'shift' => $schedule->update(['turno_id' => $data['turno_id']]),
            'driver' => $schedule->update(['conductor_id' => $data['conductor_id']]),
            'vehicle' => $schedule->update(['vehiculo_id' => $data['vehiculo_id']]),
            'helper' => $schedule->helpers()->sync($data['helper_ids']),
        };
    }
}
