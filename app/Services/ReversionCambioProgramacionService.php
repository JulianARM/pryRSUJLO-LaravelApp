<?php

namespace App\Services;

use App\Models\CambioProgramacion;
use App\Models\Programacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReversionCambioProgramacionService
{
    public function revertirYEliminar(CambioProgramacion $change): void
    {
        DB::transaction(function () use ($change) {
            $change->load(['schedule.helpers']);
            $schedule = $change->schedule;

            if (! $schedule) {
                $change->delete();

                return;
            }

            $this->ensureCanRevert($change, $schedule);

            $oldValues = $change->valores_anteriores ?? [];

            if (empty($oldValues)) {
                throw ValidationException::withMessages([
                    'change' => 'Este registro no tiene valores anteriores para restaurar la programación afectada.',
                ]);
            }

            $schedule->update($this->schedulePayload($oldValues));

            if (array_key_exists('helpers', $oldValues)) {
                $schedule->helpers()->sync($this->helperIds($oldValues));
            }

            $change->delete();
        });
    }

    private function ensureCanRevert(CambioProgramacion $change, Programacion $schedule): void
    {
        $latestChangeId = $schedule->changes()->value('id');

        if ((int) $latestChangeId !== (int) $change->id) {
            throw ValidationException::withMessages([
                'change' => 'Solo se puede revertir el cambio más reciente de la programación para no sobrescribir cambios posteriores.',
            ]);
        }
    }

    private function schedulePayload(array $values): array
    {
        return [
            'grupo_personal_id' => $values['grupo_personal_id'] ?? null,
            'turno_id' => $values['turno_id'],
            'zona_id' => $values['zona_id'],
            'vehiculo_id' => $values['vehiculo_id'],
            'conductor_id' => $values['conductor_id'],
            'fecha_programada' => $this->formatDate($values['fecha_programada']),
            'status' => $values['status'] ?? Programacion::STATUS_SCHEDULED,
            'notes' => $values['notes'] ?? null,
        ];
    }

    private function helperIds(array $values): array
    {
        return collect($values['helpers'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function formatDate(string $date): string
    {
        return Carbon::parse($date)->format('Y-m-d');
    }
}
