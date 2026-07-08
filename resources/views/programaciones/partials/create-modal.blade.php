<div class="modal fade" id="createProgramacionModal" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            {!! Form::open([
                'route' => 'programaciones.store',
                'method' => 'POST',
                'class' => 'js-ajax-form js-schedule-form',
                'data-group-url-template' => route('grupos-personal.show', ['personnel_group' => '__GROUP__']),
                'data-validate-url' => route('programaciones.validate'),
            ]) !!}
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-calendar-check mr-1"></i> Nueva Programación</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('programaciones.partials.create-form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary mr-auto js-schedule-validate">
                        <i class="fas fa-search mr-1"></i> Validar Disponibilidad
                    </button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary js-schedule-submit" disabled>
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
