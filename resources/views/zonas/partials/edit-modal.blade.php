<div class="modal fade" id="editZonaModal{{ $zone->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {!! Form::model($zone, ['route' => ['zonas.update', $zone], 'method' => 'PUT', 'class' => 'js-ajax-form']) !!}
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-map-marker-alt mr-1"></i> Editar Zona</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('zonas.partials.form', ['zone' => $zone])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
