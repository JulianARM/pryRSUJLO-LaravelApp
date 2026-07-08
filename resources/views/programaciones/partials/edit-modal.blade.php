@php
    $selectedTurno = $schedule->turno_id;
    $selectedZona = $schedule->zona_id;
    $selectedVehiculo = $schedule->vehiculo_id;
    $selectedDriver = $schedule->conductor_id;
    $selectedHelpers = $schedule->helpers->pluck('id')->all();
    $selectedDays = [$schedule->fecha_programada->dayOfWeekIso];
    $fieldPrefix = 'edit'.$schedule->id;
@endphp

<div class="modal fade" id="editProgramacionModal{{ $schedule->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {!! Form::model($schedule, ['route' => ['programaciones.update', $schedule], 'method' => 'PUT', 'class' => 'js-ajax-form']) !!}
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-calendar-check mr-1"></i> Modificar Programación</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle mr-1"></i> Importante:</strong>
                        Al guardar cambios en turno, vehículo o personal, la programación pasará al estado <strong>Reprogramado</strong> y el motivo quedará registrado en el historial.
                    </div>

                    <div class="form-group">
                        {!! Form::label('fecha_programada', 'Fecha *') !!}
                        {!! Form::date('fecha_programada', $schedule->fecha_programada->format('Y-m-d'), ['class' => 'form-control', 'required' => true]) !!}
                    </div>

                    @include('programaciones.partials.fields', compact('selectedTurno', 'selectedZona', 'selectedVehiculo', 'selectedDriver', 'selectedHelpers', 'selectedDays', 'fieldPrefix'))

                    <div class="form-group">
                        {!! Form::label('change_reason', 'Motivo del cambio *') !!}
                        {!! Form::text('change_reason', null, ['class' => 'form-control', 'required' => true, 'maxlength' => 255, 'placeholder' => 'Ej: cambio de vehículo por mantenimiento']) !!}
                        <small class="form-text text-muted">Este motivo se mostrará en el historial de cambios.</small>
                    </div>

                    <div class="form-group">
                        {!! Form::label('notes', 'Notas') !!}
                        {!! Form::textarea('notes', $schedule->notes, ['class' => 'form-control', 'rows' => 2]) !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
