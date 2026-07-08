@php
    $holiday = $holiday ?? null;
@endphp

<div class="form-group">
    {!! Form::label('date', 'Fecha del Feriado *') !!}
    {!! Form::date('date', old('date', $holiday?->date?->format('Y-m-d')), ['class' => 'form-control', 'required' => true]) !!}
    <small class="form-text text-muted">Día: <strong>Seleccione una fecha</strong></small>
</div>

<div class="form-group">
    {!! Form::label('descripcion', 'Descripción *') !!}
    {!! Form::text('descripcion', old('descripcion', $holiday?->descripcion), ['class' => 'form-control', 'maxlength' => 160, 'required' => true, 'placeholder' => 'Descripción del día feriado']) !!}
</div>

<div class="form-group mb-0">
    <label>Estado *</label>
    <div class="custom-control custom-switch">
        {!! Form::hidden('activo', 0) !!}
        {!! Form::checkbox('activo', 1, old('activo', $holiday?->activo ?? true), ['class' => 'custom-control-input', 'id' => 'holidayStatus'.($holiday?->id ?? 'Create')]) !!}
        {!! Form::label('holidayStatus'.($holiday?->id ?? 'Create'), 'Activo', ['class' => 'custom-control-label text-success font-weight-bold']) !!}
    </div>
    <small class="form-text text-muted">Los feriados inactivos no se considerarán en las validaciones de programación.</small>
</div>

<div class="alert alert-info mt-3 mb-0">
    <strong><i class="fas fa-info-circle mr-1"></i> Información:</strong>
    <ul class="mb-0 mt-2">
        <li>Los días feriados afectan la programación de rutas.</li>
        <li>Puede cargar los feriados oficiales de Perú usando el botón "Cargar Feriados Perú".</li>
        <li>Los feriados inactivos no se consideran en las validaciones.</li>
    </ul>
</div>
