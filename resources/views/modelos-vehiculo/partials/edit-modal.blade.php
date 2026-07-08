<div class="modal fade" id="editModeloVehiculoModal{{ $model->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::model($model, ['route' => ['modelos-vehiculo.update', $model], 'method' => 'PUT', 'class' => 'js-ajax-form']) !!}
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-wrench text-warning mr-2"></i>Editar Modelo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Nombre del Modelo *') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('code', 'Código del Modelo *') !!}
                        {!! Form::text('code', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('marca_id', 'Marca *') !!}
                        {!! Form::select('marca_id', $marcas, null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('descripcion', 'Descripción') !!}
                        {!! Form::textarea('descripcion', null, ['class' => 'form-control', 'rows' => 3]) !!}
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
