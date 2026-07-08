<div class="modal fade" id="editContratoModal{{ $contract->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {!! Form::model($contract, ['route' => ['contratos.update', $contract], 'method' => 'PUT', 'class' => 'js-ajax-form']) !!}
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-contract text-warning mr-2"></i>Editar Contrato</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('contratos.partials.form', ['contract' => $contract])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
