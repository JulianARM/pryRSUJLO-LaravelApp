@php
    use App\Models\Programacion;
    use Illuminate\Support\Carbon;

    $values = $values ?? [];

    $personLabel = function (?array $person, ?int $id = null): string {
        if (! $person) {
            return $id ? 'Personal #'.$id : '-';
        }

        $fullName = trim(($person['nombres'] ?? '').' '.($person['apellidos'] ?? ''));

        return trim($fullName.(! empty($person['dni']) ? ' - '.$person['dni'] : '')) ?: ($id ? 'Personal #'.$id : '-');
    };

    $helpers = collect($values['helpers'] ?? [])
        ->map(fn ($helper) => $personLabel($helper, $helper['id'] ?? null))
        ->filter()
        ->implode(', ');

    $scheduleDate = $values['fecha_programada'] ?? null;
    $formattedDate = $scheduleDate
        ? Carbon::parse($scheduleDate)->format('d/m/Y')
        : '-';

    $vehicle = $values['vehicle'] ?? null;
    $vehicleLabel = $vehicle
        ? trim(($vehicle['name'] ?? '').(! empty($vehicle['placa']) ? ' - '.$vehicle['placa'] : ''))
        : (! empty($values['vehiculo_id']) ? 'Vehículo #'.$values['vehiculo_id'] : '-');

    $rows = [
        ['icon' => 'fa-calendar-day', 'label' => 'Fecha', 'value' => $formattedDate],
        ['icon' => 'fa-clock', 'label' => 'Turno', 'value' => data_get($values, 'shift.name') ?: (! empty($values['turno_id']) ? 'Turno #'.$values['turno_id'] : '-')],
        ['icon' => 'fa-map-marker-alt', 'label' => 'Zona', 'value' => data_get($values, 'zone.name') ?: (! empty($values['zona_id']) ? 'Zona #'.$values['zona_id'] : '-')],
        ['icon' => 'fa-truck', 'label' => 'Vehículo', 'value' => $vehicleLabel ?: '-'],
        ['icon' => 'fa-user-tie', 'label' => 'Conductor', 'value' => $personLabel($values['driver'] ?? null, $values['conductor_id'] ?? null)],
        ['icon' => 'fa-users', 'label' => 'Ayudantes', 'value' => $helpers ?: '-'],
        ['icon' => 'fa-flag-checkered', 'label' => 'Estado', 'value' => Programacion::STATUSES[$values['status'] ?? ''] ?? ($values['status'] ?? '-')],
        ['icon' => 'fa-sticky-note', 'label' => 'Notas', 'value' => $values['notes'] ?? '-'],
    ];
@endphp

<div class="rsu-change-summary">
    @foreach ($rows as $row)
        <div class="rsu-change-summary-row">
            <span><i class="fas {{ $row['icon'] }}"></i> {{ $row['label'] }}</span>
            <strong>{{ $row['value'] }}</strong>
        </div>
    @endforeach
</div>
