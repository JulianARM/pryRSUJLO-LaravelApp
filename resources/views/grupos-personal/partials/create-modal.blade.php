<div class="modal fade" id="createGrupoPersonalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {!! Form::open([
                'route' => 'grupos-personal.store',
                'method' => 'POST',
                'class' => 'js-ajax-form js-personnel-group-form',
                'data-validate-url' => route('grupos-personal.validate'),
                'data-reset-on-open' => 'true',
                'autocomplete' => 'off',
            ]) !!}
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-users mr-1"></i> Nuevo Grupo de Personal</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('grupos-personal.partials.form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary js-group-submit" disabled><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
