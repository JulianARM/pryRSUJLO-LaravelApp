@php
    $vehicle = $vehicle ?? null;
    $selectedMarca = old('marca_id', $vehicle?->marca_id);
    $selectedModel = old('modelo_vehiculo_id', $vehicle?->modelo_vehiculo_id);
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('code', 'Código *') !!}
            {!! Form::text('code', old('code', $vehicle?->code), ['class' => 'form-control', 'placeholder' => 'Ingrese el codigo (Ej: VEH-001)', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('tipo_vehiculo_id', 'Tipo de Vehículo *') !!}
            {!! Form::select('tipo_vehiculo_id', $vehicleTypes, old('tipo_vehiculo_id', $vehicle?->tipo_vehiculo_id), ['class' => 'form-control', 'placeholder' => 'Seleccione un tipo', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('name', 'Nombre del Vehículo *') !!}
            {!! Form::text('name', old('name', $vehicle?->name), ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre (Ej: Vehículo 01)', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('placa', 'Placa *') !!}
            {!! Form::text('placa', old('placa', $vehicle?->placa), ['class' => 'form-control', 'placeholder' => 'Ingrese la placa (Ej: CLO-008)', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('anio', 'Año *') !!}
            {!! Form::number('anio', old('anio', $vehicle?->anio), ['class' => 'form-control', 'placeholder' => 'Ingrese el anio (Ej: 2025)', 'required' => true, 'min' => 1990, 'max' => now()->addYear()->year]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('color_vehiculo_id', 'Color *') !!}
            {!! Form::select('color_vehiculo_id', $vehicleColors, old('color_vehiculo_id', $vehicle?->color_vehiculo_id), ['class' => 'form-control', 'placeholder' => 'Seleccione un color', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('marca_id', 'Marca *') !!}
            {!! Form::select('marca_id', $marcas, $selectedMarca, ['class' => 'form-control js-brand-select', 'placeholder' => 'Seleccione una marca', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('modelo_vehiculo_id', 'Modelo *') !!}
            <select name="modelo_vehiculo_id" class="form-control js-model-select" required>
                <option value="">Seleccione un modelo</option>
                @foreach ($brandModels as $model)
                    <option value="{{ $model->id }}" data-brand="{{ $model->marca_id }}" @selected((string) $selectedModel === (string) $model->id)>
                        {{ $model->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('capacidad_carga', 'Capacidad de Carga (Tn) *') !!}
            {!! Form::number('capacidad_carga', old('capacidad_carga', $vehicle?->capacidad_carga), ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'placeholder' => 'Ej: 9528', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('capacidad_combustible', 'Capacidad de Combustible (L) *') !!}
            {!! Form::number('capacidad_combustible', old('capacidad_combustible', $vehicle?->capacidad_combustible), ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'placeholder' => 'Ej: 60', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('capacidad_compactacion', 'Capacidad de Compactacion (Tn) *') !!}
            {!! Form::number('capacidad_compactacion', old('capacidad_compactacion', $vehicle?->capacidad_compactacion), ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'placeholder' => 'Ej: 180', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('capacidad_personas', 'Capacidad de Personas *') !!}
            {!! Form::number('capacidad_personas', old('capacidad_personas', $vehicle?->capacidad_personas), ['class' => 'form-control', 'min' => 1, 'placeholder' => 'Ej: 3', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('activo', 'Estado *') !!}
            {!! Form::select('activo', [1 => 'Activo', 0 => 'Inactivo'], old('activo', $vehicle?->activo ?? 1), ['class' => 'form-control', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('descripcion', 'Descripción') !!}
            {!! Form::textarea('descripcion', old('descripcion', $vehicle?->descripcion), ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Ingrese la descripcion']) !!}
        </div>
    </div>
</div>
