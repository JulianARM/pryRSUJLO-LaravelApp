<div class="modal fade" id="editPersonalModal{{ $employee->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            {!! Form::model($employee, ['route' => ['personal.update', $employee], 'method' => 'PUT', 'class' => 'js-ajax-form', 'files' => true]) !!}
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user text-warning mr-2"></i>Editar Personal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('personal.partials.form', ['employee' => $employee])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
