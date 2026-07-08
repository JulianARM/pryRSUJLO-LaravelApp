@php
    $zone = $zone ?? null;
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('name', 'Nombre de la Zona *') !!}
            {!! Form::text('name', old('name', $zone?->name), ['class' => 'form-control', 'placeholder' => 'Ej: Zona Centro', 'required' => true, 'maxlength' => 120]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('residuos_promedio_kg', 'Residuos Promedio Generados (Kg) *') !!}
            {!! Form::number('residuos_promedio_kg', old('residuos_promedio_kg', $zone?->residuos_promedio_kg ?? 0), ['class' => 'form-control', 'min' => 0, 'step' => '0.01', 'required' => true]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('departamento', 'Departamento *') !!}
            {!! Form::text('departamento', old('departamento', $zone?->departamento ?? 'Lambayeque'), ['class' => 'form-control', 'required' => true, 'maxlength' => 80]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('provincia', 'Provincia *') !!}
            {!! Form::text('provincia', old('provincia', $zone?->provincia ?? 'Chiclayo'), ['class' => 'form-control', 'required' => true, 'maxlength' => 80]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('distrito', 'Distrito *') !!}
            {!! Form::text('distrito', old('distrito', $zone?->distrito ?? 'Jose Leonardo Ortiz'), ['class' => 'form-control', 'required' => true, 'maxlength' => 80]) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('descripcion', 'Descripción') !!}
    {!! Form::textarea('descripcion', old('descripcion', $zone?->descripcion), ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Agregue una descripción de la zona...']) !!}
</div>

<div class="form-group mb-0">
    <label>Estado *</label>
    <div class="custom-control custom-switch">
        {!! Form::hidden('activo', 0) !!}
        {!! Form::checkbox('activo', 1, old('activo', $zone?->activo ?? true), ['class' => 'custom-control-input', 'id' => 'zoneStatus'.($zone?->id ?? 'Create')]) !!}
        {!! Form::label('zoneStatus'.($zone?->id ?? 'Create'), 'Activo', ['class' => 'custom-control-label text-success font-weight-bold']) !!}
    </div>
</div>
