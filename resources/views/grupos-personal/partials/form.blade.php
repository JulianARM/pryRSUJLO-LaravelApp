@php
    $group = $group ?? null;
    $isEdit = filled($group?->id);
    $selectedDays = $isEdit ? old('dias_semana', $group?->dias_semana ?? []) : [];
    $selectedHelpers = $isEdit ? old('helper_ids', $group?->helpers?->pluck('id')->all() ?? []) : [];
    $fieldSuffix = $group?->id ?? 'Create';
    $vehicleCapacities = $vehicleCapacities ?? [];
    $selectedVehiculoId = $isEdit ? old('vehiculo_id', $group?->vehiculo_id) : null;
    $selectedVehiculoCapacity = $selectedVehiculoId ? (int) ($vehicleCapacities[$selectedVehiculoId] ?? 1) : 1;
    $helperOptionsCount = collect($vehicleCapacities)->map(fn ($capacity) => max(((int) $capacity) - 1, 0))->max() ?? 1;
    $maxHelpers = max(1, $helperOptionsCount, count($selectedHelpers));
    $visibleHelpers = $selectedVehiculoId ? max($selectedVehiculoCapacity - 1, 0) : 0;
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('name', 'Nombre del Grupo *') !!}
            {!! Form::text('name', $isEdit ? old('name', $group?->name) : null, ['class' => 'form-control', 'required' => true, 'maxlength' => 120, 'placeholder' => 'Ej: Grupo Centro - Mañana']) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('turno_id', 'Turno *') !!}
            {!! Form::select('turno_id', $turnos, $isEdit ? old('turno_id', $group?->turno_id) : null, ['class' => 'form-control js-group-watch', 'placeholder' => 'Seleccione un turno', 'required' => true]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('zona_id', 'Zona *') !!}
            {!! Form::select('zona_id', $zonas, $isEdit ? old('zona_id', $group?->zona_id) : null, ['class' => 'form-control', 'placeholder' => 'Seleccione una zona', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('vehiculo_id', 'Vehículo *') !!}
            {!! Form::select('vehiculo_id', $vehiculos, $selectedVehiculoId, [
                'class' => 'form-control js-select2 js-group-watch js-group-vehicle',
                'placeholder' => 'Seleccione un vehículo',
                'required' => true,
                'data-vehicle-capacities' => json_encode($vehicleCapacities),
            ]) !!}
            <small class="form-text text-muted js-group-vehicle-capacity">
                Seleccione un vehículo para calcular conductor y ayudantes requeridos.
            </small>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Días de la Semana *</label>
    <div class="rsu-checkbox-grid">
        @foreach ($days as $value => $label)
            <label class="rsu-day-option @if (in_array((int) $value, array_map('intval', $selectedDays), true)) is-selected @endif">
                {!! Form::checkbox('dias_semana[]', $value, in_array((int) $value, array_map('intval', $selectedDays), true), ['class' => 'rsu-day-checkbox js-group-watch', 'id' => 'groupDay'.$fieldSuffix.$value]) !!}
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <small class="form-text text-muted">Seleccione los días en los que este grupo podrá ser programado.</small>
</div>

<div class="rsu-team-section">
    <div class="rsu-team-section-header">
        <div>
            <strong>Equipo operativo</strong>
            <span>Seleccione el conductor y los ayudantes según la capacidad del vehículo.</span>
        </div>
        <i class="fas fa-users"></i>
    </div>

    <div class="rsu-team-layout">
        <div class="rsu-team-driver">
            <div class="form-group mb-0">
                {!! Form::label('conductor_id', 'Conductor *') !!}
                {!! Form::select('conductor_id', $drivers, $isEdit ? old('conductor_id', $group?->conductor_id) : null, ['class' => 'form-control js-select2 js-group-watch', 'placeholder' => 'Seleccione un conductor', 'required' => true]) !!}
                <div class="js-group-driver-availability mt-2"></div>
            </div>
        </div>

        <div class="rsu-team-helpers js-group-helper-container" data-max-helpers="{{ $maxHelpers }}">
            @for ($index = 0; $index < $maxHelpers; $index++)
                <div class="rsu-team-helper js-group-helper-wrapper {{ $index >= $visibleHelpers ? 'd-none' : '' }}" data-helper-index="{{ $index }}">
                    <div class="form-group mb-0">
                        {!! Form::label('helper'.$fieldSuffix.$index, 'Ayudante '.($index + 1).' *') !!}
                        {!! Form::select('helper_ids[]', $helpers, $selectedHelpers[$index] ?? null, [
                            'id' => 'helper'.$fieldSuffix.$index,
                            'class' => 'form-control js-select2 js-group-watch js-group-helper-select',
                            'placeholder' => 'Seleccione ayudante',
                            'required' => $index < $visibleHelpers,
                            'disabled' => $index >= $visibleHelpers,
                        ]) !!}
                        <div class="js-group-helper-availability mt-2" data-helper-index="{{ $index }}"></div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <small class="form-text text-muted mt-2 js-group-helper-help">
        Seleccione un vehículo para calcular los ayudantes requeridos.
    </small>
</div>

<div class="alert alert-info">
    <strong><i class="fas fa-info-circle mr-1"></i> Información:</strong>
    Estos datos funcionan como preconfiguración para crear programaciones más rápido. El sistema validará si conductor, ayudantes y vehículo están disponibles para los días y turno seleccionados.
</div>

<div class="js-group-availability-result mt-3"></div>

<div class="form-group mb-0">
    <label>Estado *</label>
    <div class="custom-control custom-switch">
        {!! Form::hidden('activo', 0) !!}
        {!! Form::checkbox('activo', 1, $isEdit ? old('activo', $group?->activo) : true, ['class' => 'custom-control-input', 'id' => 'groupStatus'.($group?->id ?? 'Create')]) !!}
        {!! Form::label('groupStatus'.($group?->id ?? 'Create'), 'Activo', ['class' => 'custom-control-label text-success font-weight-bold']) !!}
    </div>
</div>
