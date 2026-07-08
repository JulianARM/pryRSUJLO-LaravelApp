<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('grupo_personal_id', 'Grupo de Personal *') !!}
            {!! Form::select('grupo_personal_id', $groups, null, ['class' => 'form-control js-select2 js-schedule-group-select', 'placeholder' => 'Seleccione un grupo', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('fecha_inicio', 'Fecha de Inicio *') !!}
            {!! Form::date('fecha_inicio', null, ['class' => 'form-control js-schedule-watch', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('fecha_fin', 'Fecha de Fin *') !!}
            {!! Form::date('fecha_fin', null, ['class' => 'form-control js-schedule-watch', 'required' => true]) !!}
        </div>
    </div>
</div>

<div class="alert alert-warning">
    <strong><i class="fas fa-info-circle mr-1"></i> Importante:</strong>
    Los datos del grupo se cargan como propuesta. Si un conductor o ayudante no está disponible, puede cambiarlo y volver a validar disponibilidad antes de guardar.
</div>

@include('programaciones.partials.fields')

<div class="form-group">
    {!! Form::label('notes', 'Notas') !!}
    {!! Form::textarea('notes', null, ['class' => 'form-control js-schedule-watch', 'rows' => 2, 'placeholder' => 'Observaciones para la programación...']) !!}
</div>

<div class="js-schedule-validation-result"></div>
