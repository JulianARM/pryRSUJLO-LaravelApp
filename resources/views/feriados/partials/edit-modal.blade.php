<div class="modal fade" id="editFeriadoModal{{ $holiday->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::model($holiday, ['route' => ['feriados.update', $holiday], 'method' => 'PUT', 'class' => 'js-ajax-form']) !!}
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-flag mr-1"></i> Editar Feriado</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('feriados.partials.form', ['holiday' => $holiday])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
