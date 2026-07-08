@php
    $shift = $shift ?? null;
@endphp

<div class="form-group">
    {!! Form::label('name', 'Nombre del Turno *') !!}
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-tag"></i></span>
        </div>
        {!! Form::text('name', old('name', $shift?->name), ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del turno', 'required' => true]) !!}
    </div>
    <small class="form-text text-muted">Ejemplo: Madrugada, Manana, Tarde, Noche.</small>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('start_time', 'Hora de Inicio *') !!}
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                </div>
                {!! Form::input('time', 'start_time', old('start_time', $shift?->start_time?->format('H:i')), ['class' => 'form-control', 'required' => true]) !!}
            </div>
            <small class="form-text text-muted">Formato de 24 horas.</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('end_time', 'Hora de Termino *') !!}
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                </div>
                {!! Form::input('time', 'end_time', old('end_time', $shift?->end_time?->format('H:i')), ['class' => 'form-control', 'required' => true]) !!}
            </div>
            <small class="form-text text-muted">Formato de 24 horas.</small>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('descripcion', 'Descripción') !!}
    {!! Form::textarea('descripcion', old('descripcion', $shift?->descripcion), ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Ingrese una descripción del turno (opcional)']) !!}
</div>

<div class="alert alert-info mb-0">
    <strong>Nota:</strong> Configure los horarios de entrada y salida para este turno.
</div>
