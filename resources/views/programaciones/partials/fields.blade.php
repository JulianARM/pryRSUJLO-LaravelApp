@php
    $fieldPrefix = $fieldPrefix ?? 'create';
    $selectedDays = old('dias_semana', $selectedDays ?? []);
    $selectedHelpers = old('helper_ids', $selectedHelpers ?? []);
    $vehicleCapacities = $vehicleCapacities ?? [];
    $selectedVehiculoValue = old('vehiculo_id', $selectedVehiculo ?? null);
    $selectedVehiculoCapacity = $selectedVehiculoValue ? (int) ($vehicleCapacities[$selectedVehiculoValue] ?? 1) : 1;
    $helperOptionsCount = collect($vehicleCapacities)->map(fn ($capacity) => max(((int) $capacity) - 1, 0))->max() ?? 1;
    $maxHelpers = max(1, $helperOptionsCount, count($selectedHelpers));
    $visibleHelpers = $selectedVehiculoValue ? max($selectedVehiculoCapacity - 1, 0) : 0;
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('turno_id', 'Turno *') !!}
            {!! Form::select('turno_id', $turnos, old('turno_id', $selectedTurno ?? null), ['class' => 'form-control js-schedule-shift js-schedule-watch', 'placeholder' => 'Seleccione', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('zona_id', 'Zona *') !!}
            {!! Form::select('zona_id', $zonas, old('zona_id', $selectedZona ?? null), ['class' => 'form-control js-schedule-zone js-schedule-watch', 'placeholder' => 'Seleccione', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('vehiculo_id', 'Vehículo *') !!}
            {!! Form::select('vehiculo_id', $vehiculos, $selectedVehiculoValue, [
                'class' => 'form-control js-select2 js-schedule-vehicle js-schedule-watch',
                'placeholder' => 'Seleccione',
                'required' => true,
                'data-vehicle-capacities' => json_encode($vehicleCapacities),
            ]) !!}
            <small class="form-text text-muted js-schedule-vehicle-capacity"></small>
        </div>
    </div>
</div>

<div class="rsu-team-section">
    <div class="rsu-team-section-header">
        <div>
            <strong>Equipo operativo</strong>
            <span>Revise o cambie el conductor y los ayudantes propuestos por el grupo.</span>
        </div>
        <i class="fas fa-users"></i>
    </div>

    <div class="rsu-team-layout">
        <div class="rsu-team-driver">
            <div class="form-group mb-0">
                {!! Form::label('conductor_id', 'Conductor *') !!}
                {!! Form::select('conductor_id', $drivers, old('conductor_id', $selectedDriver ?? null), ['class' => 'form-control js-select2 js-schedule-driver js-schedule-watch', 'placeholder' => 'Seleccione', 'required' => true]) !!}
            </div>
        </div>

        <div class="rsu-team-helpers js-schedule-helper-container" data-max-helpers="{{ $maxHelpers }}">
            @for ($index = 0; $index < $maxHelpers; $index++)
                <div class="rsu-team-helper js-schedule-helper-wrapper {{ $index >= $visibleHelpers ? 'd-none' : '' }}" data-helper-index="{{ $index }}">
                    <div class="form-group mb-0">
                        {!! Form::label('scheduleHelper'.$fieldPrefix.$index, 'Ayudante '.($index + 1).' *') !!}
                        {!! Form::select('helper_ids[]', $helpers, $selectedHelpers[$index] ?? null, [
                            'id' => 'scheduleHelper'.$fieldPrefix.$index,
                            'class' => 'form-control js-select2 js-schedule-helper-select js-schedule-watch',
                            'placeholder' => 'Seleccione ayudante',
                            'required' => $index < $visibleHelpers,
                            'disabled' => $index >= $visibleHelpers,
                        ]) !!}
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <small class="form-text text-muted mt-2 js-schedule-helper-help">Seleccione un vehículo para calcular los ayudantes requeridos.</small>
</div>

<div class="form-group">
    <label>Días programables *</label>
    <div class="rsu-checkbox-grid">
        @foreach ($days as $value => $label)
            <div class="custom-control custom-checkbox">
                {!! Form::checkbox('dias_semana[]', $value, in_array((int) $value, array_map('intval', $selectedDays), true), ['class' => 'custom-control-input js-schedule-day js-schedule-watch', 'id' => 'scheduleDay'.$fieldPrefix.$value]) !!}
                {!! Form::label('scheduleDay'.$fieldPrefix.$value, $label, ['class' => 'custom-control-label']) !!}
            </div>
        @endforeach
    </div>
</div>
